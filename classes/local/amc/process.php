<?php
namespace mod_automultiplechoice\local\amc;
defined('MOODLE_INTERNAL') || die();
class process
{
    /**
     * @var \mod_automultiplechoice\local\models\quiz Contains notably an 'amcparams' attribute.
     */
    protected $quiz;
    protected $codelength = 0;
    public $workdir;
    protected $relworkdir;
    protected $grades = array();
    /**
     * @var array
     */
    public $errors = array();
    // Write in moodledata log file.
    private $_logger;
    const PATH_STUDENTLIST_CSV = '/exports/student_list.csv';
    const PATH_AMC_CSV = '/exports/grades.csv';
    const PATH_AMC_ODS = '/exports/grades.ods';
    const PATH_APOGEE_CSV = '/exports/grades_apogee.csv';
    const CSV_SEPARATOR = ';';
    /**
     * Constructor
     *
     * @param \mod_automultiplechoice\local\models\quiz $quiz
     */
    public function __construct(\mod_automultiplechoice\local\models\quiz $quiz, $formatName = 'latex') {
        if (empty($quiz->id)) {
            throw new Exception("No quizz ID");
        }
        $this->quiz = $quiz;
        $this->workdir = $quiz->getDirName(true);
        $this->relworkdir = $quiz->getDirName(false);
        $this->initWorkdir();
        $this->codelength = (int) get_config('mod_automultiplechoice', 'amccodelength');
        $this->format = \mod_automultiplechoice\local\format\api::buildFormat($formatName, $quiz);
        if (!$this->format) {
            throw new \Exception("Erreur, pas de format de QCM pour AMC.");
        }
        $this->format->quiz = $this->quiz;
        $this->format->codelength = $this->codelength;
    }
    /**
     * Save the AmcTXT source file.
     *
     * @param string $formatName "txt" | "latex"
     *
     * @return \mod_automultiplechoice\local\format\api
     */
    public function saveFormat($formatName) {
        try {
            $format = amcFormat\buildFormat($formatName, $this->quiz);
            $format->quiz = $this->quiz;
            $format->codelength = $this->codelength;
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            return null;
        }
        $filename = $this->workdir . "/" . $format->getFilename();
        if (file_put_contents($filename, $format->getContent())) {
            return $format;
        } else {
            $this->errors[] = "Could not write the file for AMC. Disk full?";
            return null;
        }
    }
    /**
     * Write in a .log file (Moodledata/AMC_commands.log)
     *
     * @return AmcLogfile
     */
    public function getLogger() {
        if (!isset($this->_logger)) {
            $this->_logger = new \mod_automultiplechoice\local\amc\logger($this->workdir);
        }
        return $this->_logger;
    }
    /**
     * Return the errors of the last command.
     *
     * @return array
     */
    public function getLastErrors() {
        return $this->errors;
    }
    /**
     * Return the single error of the last command.
     *
     * @return string
     */
    public function getLastError() {
        return end($this->errors);
    }
    /**
     * Shell-executes 'amc meptex'
     *
     * @return bool
     */
    public function amcMeptex() {
        $pre = $this->workdir;
        $amclog = Log::build($this->quiz->id);
        $res = $this->shellExecAmc(
            'meptex',
            array(
                '--data', $pre . '/data',
                '--progression-id', 'MEP',
                '--progression', '1',
                '--src', $pre . '/prepare-calage.xy',
            )
        );
        if ($res) {
            $this->log('meptex', '');
            $amclog = Log::build($this->quiz->id);
            $amclog->write('meptex');
        }
        return $res;
    }
    /**
     * Shell-executes 'amc prepare' for extracting grading scale (Bareme)
     *
     * @return bool
     */
    public function amcPrepareBareme() {
        $path = get_config('mod_automultiplechoice', 'xelatexpath');
        if ($path === '') {
            $path = '/usr/bin/xelatex';
        }
        $pre = $this->workdir;
        $parameters = array(
            '--n-copies', (string) $this->quiz->amcparams->copies,
            '--mode', 'b',
            '--data', $pre . '/data',
            '--filtered-source', $pre . '/prepare-source_filtered.tex', // for AMC-txt, the LaTeX will be written in this file
            '--progression-id', 'bareme',
            '--progression', '1',
            '--with', $path,
            '--filter', $this->format->getFilterName(),
            $pre . '/' . $this->format->getFilename()
        );
        $res = $this->shellExecAmc('prepare', $parameters);
        if ($res) {
            $this->log('prepare:bareme', 'OK.');
            $amclog = Log::build($this->quiz->id);
            $amclog->write('scoring');
        }
        return $res;
    }
    /**
     * Shell-executes 'amc note'
     *
     * @return bool
     */
    public function amcNote() {
        $pre = $this->workdir;
        $parameters = array(
            '--data', $pre . '/data',
            '--progression-id', 'notation',
            '--progression', '1',
            '--seuil', '0.85', // black ratio threshold
            '--grain', $this->quiz->amcparams->gradegranularity,
            '--arrondi', $this->quiz->amcparams->graderounding,
            '--notemin', $this->quiz->amcparams->minscore,
            '--notemax', $this->quiz->amcparams->grademax,
            '--postcorrect-student', '', //FIXME inutile ?
            '--postcorrect-copy', '',    //FIXME inutile ?
            );
        $res = $this->shellExecAmc('note', $parameters);
        if ($res) {
            $this->log('note', 'OK.');
            $amclog = Log::build($this->quiz->id);
            $amclog->write('grading');
        }
        return $res;
    }
    /**
     * Creates scan stat information (number and dates)
     * On scanned (ppm) files already stored
     *
     * @return array with keys: count, time, timefr ; null if nothing was uploaded
     */
    public function statScans() {
        $ppmfiles = $this->findScannedFiles();
        $tsmax = 0;
        $tsmin = PHP_INT_MAX;
        foreach ($ppmfiles as $file) {
            $filedata = stat($file);
            if ($filedata['mtime'] > $tsmax) {
                $tsmax = $filedata['mtime'];
            }
            if ($filedata['mtime'] < $tsmin) {
                $tsmin = $filedata['mtime'];
            }
        }
        if ($ppmfiles) {
            return array(
                'nbidentified' => count(glob($this->workdir . '/cr/page-*.jpg')),
                'count' => count($ppmfiles),
                'time' => $tsmax,
                'timefr' => self::isoDate($tsmax)
            );
        } else {
            return null;
        }
    }
    /**
     * Returns the name of pdf anotated file matching user (upon $idnumber)
     *
     * @param string $idnumber the student id
     *
     * @return string (matching user file) OR FALSE if no matching file
     */
    public function getUserAnotatedSheet($idnumber) {
        $numid = substr($idnumber, -1*$this->codelength);
        $files = glob($this->workdir . '/cr/corrections/jpg/cr-*.jpg');
        foreach ($files as $file) {
            if (preg_match('@/(cr-([0-9]+)-[^/]+\.pdf)$@', $file, $matches)) {
                if ($numid === (int) $matches[2]) {
                    return $matches[1];
                }
            }
        }
        return false;
    }
    /**
     * @return boolean
     */
    public function isGraded() {
        if (\mod_automultiplechoice\local\helpers\log::build($this->quiz->id)->read('grading')) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Computes and display statistics indicators
     *
     * @return string html table with statistics indicators
     */
    public function getHtmlStats() {
        $this->readGrades();
        $mark = array();
        foreach ($this->grades as $rawmark) {
            $mark[] = $rawmark->rawgrade;
        }
        $indics = array('size' => 'effectif', 'mean' => 'moyenne', 'median' => 'médiane', 'mode' => 'mode', 'range' => 'intervalle');
        $out = "<table class=\"generaltable\"><tbody>\n";
        foreach ($indics as $indicen => $indicfr) {
            $out .= '<tr><td>' . $indicfr. '</td><td>' . $this->mmmr($mark, $indicen) . '</td></tr>' . "\n";
        }
        $out .= "</tbody></table>\n";
        return $out;
    }
    public function getStats() {
        $this->readGrades();
        $mark = array();
        foreach ($this->grades as $rawmark) {
            $mark[] = $rawmark->rawgrade;
        }
        $indics = array('size' => 'effectif', 'mean' => 'moyenne', 'median' => 'médiane', 'mode' => 'mode', 'range' => 'intervalle');
        $stats = [];
        foreach ($indics as $indicen => $indicfr) {
            $stats[$indicfr] = $this->mmmr($mark, $indicen);
        }
        return $stats;
    }
    public function getStats2() {
        $this->readGrades();
        $mark = array();
        foreach ($this->grades as $rawmark) {
            $mark[] = $rawmark->rawgrade;
        }
        $indics = array('size' => 'effectif', 'mean' => 'moyenne', 'median' => 'médiane', 'mode' => 'mode', 'range' => 'intervalle');
        $stats = [];
        foreach ($indics as $indicen => $indicfr) {
            $stats[$indicen] = $this->mmmr($mark, $indicen);
        }
        return $stats;
    }
    /**
     * Fills the "grades" property from the CSV.
     *
     * @return boolean
     */
    protected function readGrades() {
        if (count($this->grades) > 0) {
            return true;
        }
        $input = $this->fopenRead($this->workdir . self::PATH_AMC_CSV);
        if (!$input) {
            return false;
        }
        $header = fgetcsv($input, 0, self::CSV_SEPARATOR);
        if (!$header) {
            return false;
        }
        $getCol = array_flip($header);
        $this->grades = array();
        while (($data = fgetcsv($input, 0, self::CSV_SEPARATOR)) !== false) {
            $idnumber = $data[$getCol['student.number']];
            $userid=null;
            $userid = $data[$getCol['moodleid']];
            if ($userid) {
                $this->usersknown++;
            } else {
                $this->usersunknown++;
            }
            $this->grades[] = (object) array(
                'userid' => $userid,
                'rawgrade' => str_replace(',', '.', $data[6])
            );
        }
        fclose($input);
        return true;
    }
    protected static function fopenRead($filename) {
        if (!is_readable($filename)) {
            return false;
        }
        $handle = fopen($filename, 'r');
        if (!$handle) {
            return false;
        }
        return $handle;
    }
    /**
     * Computes several statistics indicators from an array
     *
     * @param array $array
     * @param string $output
     * @return float
     */
    protected function mmmr($array, $output = 'mean') {
        if (empty($array) || !is_array($array)) {
            return false;
        } else {
            switch($output){
                case 'size':
                    $res = count($array);
                    break;
                case 'mean':
                    $count = count($array);
                    $sum = array_sum($array);
                    $res = $sum / $count;
                    break;
                case 'median':
                    rsort($array);
                    $middle = round(count($array) / 2);
                    $res = $array[$middle-1];
                    break;
                case 'mode':
                    $v = array_count_values($array);
                    arsort($v);
                    list ($res) = each($v); // read the first key
                    break;
                case 'range':
                    sort($array, SORT_NUMERIC);
                    $res = $array[0] . " - " . $array[count($array) - 1];
                    break;
            }
            return $res;
        }
    }
    /**
     * Log processed action
     *
     * @param string $action ('prepare'...)
     * @param string $msg the message to put in the log
     */
    public function log($action, $msg) {
        $url = '/mod/automultiplechoice/view.php?a='. $this->quiz->id;
        $cm = get_coursemodule_from_instance('automultiplechoice', $this->quiz->id, $this->quiz->course, false, MUST_EXIST);
        add_to_log($this->quiz->course, 'automultiplechoice', $action, $url, $msg, $cm->id, 0);
        return true;
    }
    /**
     * Return the timestamp of the action.
     *
     * @param string $action the action to search
     *
     * @return integer
     */
    public function lastlog($action) {
        global $DB;
        $cm = get_coursemodule_from_instance('automultiplechoice', $this->quiz->id, $this->quiz->course, false, MUST_EXIST);
        $sql = 'SELECT time FROM {log} WHERE action=? AND cmid=? ORDER BY time DESC LIMIT 1';
        $res = $DB->get_field_sql($sql, array($action, $cm->id), IGNORE_MISSING);
        return $res;
    }
    /**
     * Gets the moodle_url that points to a file produced by this instance.
     *
     * @global moodle_page $PAGE
     * @param string $filename Local path and file name.
     * @param boolean $forcedld (opt, false)
     * @param integer $contextid (opt)
     * @return \moodle_url
     */
    public function getFileUrl($filename, $forcedld=false, $contextid=null) {
        global $PAGE;
        if (!$contextid) {
            $contextid = $PAGE->context->id;
        }
        return \moodle_url::make_pluginfile_url(
                $contextid,
                'mod_automultiplechoice',
                'local',
                $this->quiz->id,
                '/',
                ltrim($filename, '/'),
                $forcedld
        );
    }
    protected function get_students_list(){
        if (file_exists($this->workdir . self::PATH_STUDENTLIST_CSV)){
        return $this->workdir . self::PATH_STUDENTLIST_CSV;
    }else{
        return ' ';
    }
    }
    /**
     * Format a timestamp into a fr datetime.
     *
     * @param integer $timestamp
     * @return string
     */
    public static function isoDate($timestamp) {
        return date('Y-m-d à H:i', $timestamp);
    }
    /**
     * Returns a normalized text (no accents, spaces...) for use in file names
     * @param $text string input text
     * @return (guess what ?)
     */
    public function normalizeText($text) {
        setlocale(LC_CTYPE, 'fr_FR.utf8');
        if (extension_loaded("iconv")) {
            $text = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        }
        $text = strtr(
                $text,
                array(' '=>'_', "'"=>'-', '.'=>'-', ','=>'-', ';'=>'-', ':'=>'-', '?'=>'-', '!'=>'-')
        );
        $text = strtolower($text);
        $text = preg_replace('/-+/', '-', $text);
        $text = trim ($text, '-');
        $text = preg_replace('/[^\w\d-]/si', '', $text); //remove all illegal chars
        $text = substr($text, 0, 50);
        setlocale(LC_CTYPE, 'C');
        return $text;
    }
    /**
     * Returns a normalized filename for teacher downloads
     * @param string $filetype keyword amongst ('sujet', 'catalog', 'sujets')
     * @return string normalized filename
     */
    public function normalizeFilename($filetype) {
        switch ($filetype) {
            case 'sujet':
                return 'sujet-' . $this->normalizeText($this->quiz->name) . '.pdf';
            case 'corrige':
                return 'corrige-' . $this->normalizeText($this->quiz->name) . '.pdf';
            case 'corriges':
                return 'corriges-' . $this->normalizeText($this->quiz->name) . '.pdf';
            case 'catalog':
                return 'catalog-' . $this->normalizeText($this->quiz->name) . '.pdf';
            case 'sujets': // !!! plural
                return 'sujets-' . $this->normalizeText($this->quiz->name) . '.zip';
            case 'corrections':
                return 'corrections-' . $this->normalizeText($this->quiz->name) . '.pdf';
            case 'failed':
                return 'failed-' . $this->normalizeText($this->quiz->name) . '.pdf';
        }
    }
    /**
     * Displays a block containing the shell output
     *
     * @param string $cmd
     * @param array $lines output lines to be displayed
     * @param integer $returnVal shell return value
     * @return string
     */
    protected function formatShellOutput($cmd, $lines, $returnVal) {
        $txt = $cmd . " \n---------OUTPUT---------\n";
        $i=0;
        foreach ($lines as $line) {
            $i++;
            $txt .= sprintf("%03d.", $i) . " " . $line . "\n";
        }
        $txt .= "------RETURN VALUE------\n" . $returnVal. "\n";
        return $txt;
    }
    /**
     * Displays a block containing the shell output
     *
     * @param string $cmd
     * @param array $lines output lines to be displayed
     * @param integer $returnVal shell return value
     * @param int $debuglevel
     */
    protected function displayShellOutput($cmd, $lines, $returnVal, $debuglevel) {
        if (get_config('core', 'debugdisplay') == 0) {
            return false;
        }
        $html = '<pre style="margin:2px; padding:2px; border:1px solid grey;">' . " \n"
            . $this->formatShellOutput($cmd, $lines, $returnVal)
            . "</pre>"
            . "-------CALL TRACE-------\n";
        debugging($html, $debuglevel);
    }
    /**
     *
     * @param string $cmd
     * @param array $params List of strings.
     * @return boolean Success?
     */
    protected function shellExec($cmd, $params, $output=false) {
        $escapedCmd = escapeshellcmd($cmd);
        $escapedParams = array_map('escapeshellarg', $params);
        $shellCmd = $escapedCmd . " " . join(" ", $escapedParams);
        $lines = array();
        $returnVal = 0;
        exec($shellCmd, $lines, $returnVal);
        $this->getLogger()->write($this->formatShellOutput($shellCmd, $lines, $returnVal));
        if ($output) {
            $this->displayShellOutput($shellCmd, $lines, $returnVal, DEBUG_DEVELOPER);
        }
        if ($returnVal === 0) {
            return true;
        } else {
            /**
             * @todo Fill $this->errors instead of outputing HTML on the fly
             */
            $this->displayShellOutput($shellCmd, $lines, $returnVal, DEBUG_NORMAL);
            return false;
        }
    }
    /**
     * Wrapper around shellExec() including lock write
     * @param string $cmd auto-multiple-choice subcommand
     * @param array $params List of strings.
     * @param boolean (opt, false) Write a log as a side-effect (ugly, will probably be written before the HTML starts).
     * @return boolean Success?
     */
    protected function shellExecAmc($cmd, $params, $output=false) {
        $amclog = Log::build($this->quiz->id);
        $amclog->write('process');
        $res = $this->shellExec('auto-multiple-choice ' . $cmd,
            $params,
            $output
        );
        $amclog->write('process', 0);
        return $res;
    }
    /**
     * Find all the pictures in the scan dir.
     *
     * @return array
     */
    protected function findScannedFiles() {
        return $this->quiz->findScannedFiles();
    }
    public function getPdfLinks() {
        return [
          'sujet' => $this->getFileUrl($this->normalizeFilename('sujet')),
          'catalog' => $this->getFileUrl($this->normalizeFilename('catalog')),
          'correction' => $this->getFileUrl($this->normalizeFilename('corriges'))
        ];
    }
    /**
     * Return the HTML that lists links to the PDF files.
     *
     * @return string
     */
    public function getHtmlPdfLinks() {
        $opts = array('class' => 'btn','target' =>'_blank');
        $links = array(
            \html_writer::link($this->getFileUrl($this->normalizeFilename('sujet')), 'Sujet', $opts),
            \html_writer::link($this->getFileUrl($this->normalizeFilename('catalog')), 'Catalogue', $opts),
            \html_writer::link($this->getFileUrl($this->normalizeFilename('corriges')), 'Corrig&eacute;s', $opts),
        );
        return <<<EOL
        <ul class="amc-files">
            <li>
                $links[0]
                <div>Ce fichier contient tous les énoncés regroupés. <span class="warning">Ne pas utiliser ce fichier pour distribuer aux étudiants.</span></div>
            </li>
            <li>
                $links[1]
                <div>Le catalogue de questions.</div>
            </li>
            <li>
                $links[2]
                <div>Les  corrigés des différentes versions.</div>
            </li>
        </ul>
EOL;
    }
    /**
     * Return the HTML that for the link to the ZIP file.
     *
     * @return string
     */
    public function getHtmlZipLink() {
        $links = array(
            \html_writer::link($this->getFileUrl($this->normalizeFilename('sujets')), 'sujets',array('class'=>'btn')),
        );
        return <<<EOL
        <ul class="amc-files">
            <li>
                $links[0]
                <div>Cette archive contient un PDF par variante de l'énoncé.</div>
            </li>
        </ul>
EOL;
    }
    public function getZipLink() {
        return $this->getFileUrl($this->normalizeFilename('sujets'));
    }
    /**
     * Initialize the data directory $this->workdir with the template structure.
     */
    protected function initWorkdir() {
        if ( ! file_exists($this->workdir) || ! is_dir($this->workdir)) {
            $parent = dirname($this->workdir);
            if (!is_dir($parent)) {
                if (!mkdir($parent, 0777, true)) {
                    error("Could not create directory. Please contact the administrator.");
                }
            }
            if (!is_writeable($parent)) {
                error("Could not write in directory. Please contact the administrator.");
            } else {
                $templatedir = get_config('mod_automultiplechoice', 'amctemplate');
                $this->shellExec('cp', array('-r', $templatedir, $this->workdir));
            }
        }
    }
}
