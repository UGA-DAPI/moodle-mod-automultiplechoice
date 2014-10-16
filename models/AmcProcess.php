<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

require_once __DIR__ . '/Log.php';

class AmcProcess
{
    /**
     * @var Quizz Contains notably an 'amcparams' attribute.
     */
    protected $quizz;

    protected $codelength = 0;

    public $workdir;

    protected $relworkdir;

    /**
     * @var array
     */
    protected $errors = array();

    /**
     * Constructor
     *
     * @param Quizz $quizz
     */
    public function __construct(Quizz $quizz) {
        $this->quizz = $quizz;

        $this->workdir = $quizz->getDirName(true);
        $this->relworkdir = $quizz->getDirName(false);

        $this->codelength = (int) get_config('mod_automultiplechoice', 'amccodelength');
        /**
         * @todo error if codelength == 0
         */
    }

    /**
     * Return the errors of the last command.
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
     * @return bool
     */
    public function amcMeptex() {
        $pre = $this->workdir;
        $res = $this->shellExecAmc('meptex',
                array(
                    '--data', $pre . '/data',
                    '--progression-id', 'MEP',
                    '--progression', '1',
                    '--src', $pre . '/prepare-calage.xy',
                )
        );
        if ($res) {
            $this->log('meptex', '');
        }
        return $res;
    }

    /**
     * returns stat() information (number and dates) on scanned (ppm) files already stored
     * @return array with keys: count, time, timefr ; null if nothing was uploaded
     */
    public function statScans() {
        $ppmfiles = $this->findScannedFiles();
        $tsmax = 0;
        $tsmin = PHP_INT_MAX;
        foreach ($ppmfiles as $file) {
            $filedata = stat($file);
            if ( $filedata['mtime'] > $tsmax) {
                $tsmax = $filedata['mtime'];
            }
            if ( $filedata['mtime'] < $tsmin) {
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
     * log processed action
     * @param string $action ('prepare'...)
     * @param string $msg
     */
    public function log($action, $msg) {
        $url = '/mod/automultiplechoice/view.php?a='. $this->quizz->id;
        $cm = get_coursemodule_from_instance('automultiplechoice', $this->quizz->id, $this->quizz->course, false, MUST_EXIST);
        add_to_log($this->quizz->course, 'automultiplechoice', $action, $url, $msg, $cm->id, 0);
        return true;
    }

    /**
     * Return the timestamp of the action.
     *
     * @param string $action
     * @return integer
     */
    public function lastlog($action) {
        global $DB;

        $cm = get_coursemodule_from_instance('automultiplechoice', $this->quizz->id, $this->quizz->course, false, MUST_EXIST);
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
                '',
                NULL,
                '',
                $this->relworkdir . '/' . ltrim($filename, '/'),
                $forcedld
        );
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
                return 'sujet-' . $this->normalizeText($this->quizz->name) . '.pdf';
            case 'corrige':
                return 'corrige-' . $this->normalizeText($this->quizz->name) . '.pdf';
            case 'catalog':
                return 'catalog-' . $this->normalizeText($this->quizz->name) . '.pdf';
            case 'sujets': // !!! plural 
                return 'sujets-' . $this->normalizeText($this->quizz->name) . '.zip';
            case 'corrections':
                return 'corrections-' . $this->normalizeText($this->quizz->name) . '.pdf';
        }
    }

    /**
     * Displays a block containing the shell output
     *
     * @param string $cmd
     * @param integer $returnVal shell return value
     * @param array $lines output lines to be displayed
     */
    protected function shellOutput($cmd, $returnVal, $lines, $debuglevel) {
        if (get_config('core', 'debugdisplay') == 0) {
            return false;
        }
        $html = '<pre style="margin:2px; padding:2px; border:1px solid grey;">' . " \n";
        $html .= $cmd . " \n---------OUTPUT---------\n";
        $i=0;
        foreach ($lines as $line) {
            $i++;
            $html .= sprintf("%03d.", $i) . " " . $line . "\n";
        }
        $html .= "------RETURN VALUE------\n<b>" . $returnVal. "</b>\n";
        $html .= "-------CALL TRACE-------\n";
        debugging($html, $debuglevel);
        echo "</pre>";
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
            if ($output) {
                $this->shellOutput($shellCmd, $returnVal, $lines, DEBUG_DEVELOPER);
            }
        if ($returnVal === 0) {
            return true;
        } else {
            /**
             * @todo Fill $this->errors instead of outputing HTML on the fly
             */
            $this->shellOutput($shellCmd, $returnVal, $lines, DEBUG_NORMAL);
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
        $amclog = Log::build($this->quizz->id);
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
        return $this->quizz->findScannedFiles();
    }

    /**
     * Return the HTML that lists links to the PDF files.
     *
     * @return string
     */
    public function getHtmlPdfLinks() {
        $opts = array('target' => '_blank');
        $links = array(
            \html_writer::link($this->getFileUrl($this->normalizeFilename('sujet')), $this->normalizeFilename('sujet'), $opts),
            \html_writer::link($this->getFileUrl($this->normalizeFilename('catalog')), $this->normalizeFilename('catalog'), $opts),
        );
        return <<<EOL
        <ul class="amc-files">
            <li>
                $links[0]
                <div>Ce fichier contient tous les énoncés regroupés. <span class="warning">Ne pas utiliser ce fichier pour distribuer aux étudiants.</span></div>
            </li>
            <li>
                $links[1]
                <div>Le corrigé de référence.</div>
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
            \html_writer::link($this->getFileUrl($this->normalizeFilename('sujets')), $this->normalizeFilename('sujets')),
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
}
