<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

require_once __DIR__ . '/AmcProcess.php';
require_once dirname(__DIR__) . '/locallib.php';
require_once __DIR__ . '/Log.php';
require_once __DIR__ . '/AmcFormat/Api.php';


class AmcProcessGrade extends AmcProcess
{
    const PATH_AMC_CSV = '/exports/grades.csv';
    const PATH_AMC_ODS = '/exports/grades.ods';
    const PATH_APOGEE_CSV = '/exports/grades_apogee.csv';
    const CSV_SEPARATOR = ';';

    protected $grades = array();
    public $usersknown = 0;
    public $usersunknown = 0;

    protected $format;
    private $actions;
    private $exportedFiles;

        /**
     * Constructor
     *
     * @param Quizz $quizz
     * @param string $formatName "txt" | "latex"
     */
    public function __construct(Quizz $quizz, $formatName = 'latex') {
        parent::__construct($quizz);
        $this->format = amcFormat\buildFormat($formatName, $quizz);
        if (!$this->format) {
            throw new \Exception("Erreur, pas de format de QCM pour AMC.");
        }
        $this->format->quizz = $this->quizz;
        $this->format->codelength = $this->codelength;
    
        $this->actions = new \stdClass();
        if ($this->isGraded()) {
            $this->exportedFiles = (object) array(
                'grades.ods' => $this->getFileUrl(AmcProcessGrade::PATH_AMC_ODS),
                'grades.csv' => $this->getFileUrl(AmcProcessGrade::PATH_AMC_CSV),
                'grades_apogee.csv' => $this->getFileUrl(AmcProcessGrade::PATH_APOGEE_CSV),
            );
        }
    }

    /**
     * @return boolean
     */
    public function grade() {
        $this->actions = array(
            'scoringset' => (boolean) $this->amcPrepareBareme(),
            'scoring' => (boolean) $this->amcNote(),
            'studentlist' =>(boolean) $this->writeFileStudentsList(),
            'export' => (boolean) $this->amcExport(),
        'csv' => (boolean) $this->writeFileApogeeCsv(),
        'gradebook' =>(boolean) $this->writeGrades()
        );
        $this->exportedFiles = (object) array(
            'grades.ods' => $this->getFileUrl(AmcProcessGrade::PATH_AMC_ODS),
            'grades.csv' => $this->getFileUrl(AmcProcessGrade::PATH_AMC_CSV),
            'grades_apogee.csv' => $this->getFileUrl(AmcProcessGrade::PATH_APOGEE_CSV),
        );
        return (array_sum($this->actions) === count($this->actions));
    }


    /**
     * @return StdClass
     */
    public function getResults() {
        return (object) array(
            'actions' => $this->actions,
            'csv' => $this->exportedFiles,
        );
    }

    /**
     * @global core_renderer $OUTPUT
     * @return string
     */
    public function getHtmlErrors() {
        global $OUTPUT;
        $html = '';

        // error messages
        $errorMsg = array(
            'scoringset' => "Erreur lors de l'extraction du barème",
            'scoring' => "Erreur lors du calcul des notes",
            'export' => "Erreur lors de l'export CSV des notes",
            'csv' => "Erreur lors de la création du fichier CSV des notes",
        );
        foreach ($this->actions as $k => $v) {
            if (!$v) {
                $html .= $OUTPUT->box($errorMsg[$k], 'errorbox');
            }
        }
        return $html;
    }

    /**
     * @return string
     */
    public function getHtmlCsvLinks() {
        $html = '<ul class="amc-files">';
        foreach ((array) $this->exportedFiles as $name => $url) {
            $html .= "<li>" . \html_writer::link($url, $name) . "</li>";
        }
        $html .= "</ul>\n";
        return $html;
    }

    /**
     * computes and display statistics indicators
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


    /**
     * Shell-executes 'amc prepare' for extracting grading scale (Bareme)
     * @return bool
     */
    protected function amcPrepareBareme() {
        $pre = $this->workdir;
        $parameters = array(
            '--n-copies', (string) $this->quizz->amcparams->copies,
            '--mode', 'b',
            '--data', $pre . '/data',
            '--filtered-source', $pre . '/prepare-source_filtered.tex', // for AMC-txt, the LaTeX will be written in this file
            '--progression-id', 'bareme',
            '--progression', '1',
            '--with', 'xelatex',
            '--filter', $this->format->getFilterName(),
            $pre . '/' . $this->format->getFilename()
            );
        $res = $this->shellExecAmc('prepare', $parameters);
        if ($res) {
            $this->log('prepare:bareme', 'OK.');
        }
        return $res;
    }

    /**
     * Shell-executes 'amc note'
     * @return bool
     */
    protected function amcNote() {
        $pre = $this->workdir;
        $parameters = array(
            '--data', $pre . '/data',
            '--progression-id', 'notation',
            '--progression', '1',
            '--seuil', '0.5', // black ratio threshold
            '--grain', $this->quizz->amcparams->gradegranularity,
            '--arrondi', $this->quizz->amcparams->graderounding,
            '--notemin', $this->quizz->amcparams->minscore,
            '--notemax', $this->quizz->amcparams->grademax,
            //'--plafond', // removed as grades ares scaled from min to max
            '--postcorrect-student', '', //FIXME inutile ?
            '--postcorrect-copy', '',    //FIXME inutile ?
            );
        $res = $this->shellExecAmc('note', $parameters);
        if ($res) {
            $this->log('note', 'OK.');
        }
        return $res;
    }

    /**
     * Shell-executes 'amc export' to get a csv file
     * @return bool
     */
    protected function amcExport() {
        $pre = $this->workdir;
        if (!is_writable($pre . '/exports')) {
            $this->errors[] = "Le répertoire /exports n'est pas accessible en écriture. Contactez l'administrateur.";
        }
        $oldcwd = getcwd();
        chdir($pre . '/exports');

        $csvfile = $pre . self::PATH_AMC_CSV;
        $odsfile = $pre . self::PATH_AMC_ODS;
        if (file_exists($csvfile)) {
            if (!unlink($csvfile)) {
                $this->errors[] = "Le fichier CSV n'a pas pu être recréé. Contactez l'administrateur pour un problème de permissions de fichiers.";
                return false;
            }
        }
        if (file_exists($odsfile)) {
            if (!unlink($odsfile)) {
                $this->errors[] = "Le fichier ODS n'a pas pu être recréé. Contactez l'administrateur pour un problème de permissions de fichiers.";
                return false;
            }
        }

        $parameters = array(
            '--data', $pre . '/data',
            '--useall', '0',
            '--sort', 'n',
            '--no-rtl',
            '--option-out', 'encodage=UTF-8',
            '--fich-noms', $pre . self::PATH_STUDENTLIST_CSV,
            '--noms-encodage', 'UTF-8',
        );
        $parametersCsv = array_merge($parameters, array(
            '--module', 'CSV',
            '--output', $csvfile,
            '--csv-build-name', '(nom|surname) (prenom|name)',
            '--option-out', 'columns=student.copy,student.key,name,surname,moodleid,groupslist',
            '--option-out', 'separateur=' . self::CSV_SEPARATOR,
            '--option-out', 'decimal=,',
            '--option-out', 'ticked=',
        ));
        $parametersOds = array_merge($parameters, array(
            '--module', 'ods',
            '--output', $odsfile,
            '--option-out', 'columns=student.copy,student.key,name,surname,groupslist',
            '--option-out', 'stats=1',
        ));
        $res = $this->shellExecAmc('export', $parametersCsv) && $this->shellExecAmc('export', $parametersOds);
        chdir($oldcwd);
        if ($res) {
            $this->log('export', 'scoring.csv');
            Log::build($this->quizz->id)->write('grading');
        }
        if (!file_exists($csvfile) || !file_exists($odsfile)) {
            $this->errors[] = "Les fichiers CSV et ODS n'ont pu être générés. Consultez l'administrateur.";
            return false;
        }
        return $res;
    }

     /**
     * Shell-executes 'amc association-auto'
     * @return bool
     */
    protected function amcAssociation() {
        $pre = $this->workdir;
        $parameters = array(
            '--data', $pre . '/data',
            '--no-pre-association',
            '--liste', $pre . self::PATH_STUDENTLIST_CSV,
            '--encodage-liste', 'UTF-8',
            '--liste-key', 'id',
            '--csv-build-name', '(nom|surname) (prenom|name)',
            '--notes-id', 'student.number',
        );
        return $this->shellExecAmc('association-auto', $parameters);
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
        while (($data = fgetcsv($input, 0, self::CSV_SEPARATOR)) !== FALSE) {
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
                'rawgrade' => str_replace(',', '.', $data[$getCol['Mark']])
    );
        }
    fclose($input);
        return true;
    }

    /**
     * Return an array of students with added fields for identified users.
     *
     *
     *
     * @return boolean Success?
     */
    protected function writeFileStudentsList() {
    global $DB;
        
        $studentList = fopen($this->workdir . self::PATH_STUDENTLIST_CSV, 'w');
        if (!$studentList) {
            return false;
        }
        fputcsv($studentList, array('surname', 'name', 'id', 'email','moodleid','groupslist'), self::CSV_SEPARATOR);
    $codelength = get_config('mod_automultiplechoice', 'amccodelength');
    $sql = "SELECT RIGHT(u.idnumber,".$codelength.") as idnumber ,u.firstname, u.lastname,u.email, u.id as id , GROUP_CONCAT(DISTINCT g.name ORDER BY g.name) as groups_list FROM {user} u "
                ."JOIN {user_enrolments} ue ON (ue.userid = u.id) "
        ."JOIN {enrol} e ON (e.id = ue.enrolid) "
        ."JOIN  groups_members gm ON u.id=gm.userid "
        ."JOIN groups g ON g.id=gm.groupid "
        ."WHERE u.idnumber != '' AND e.courseid = ? AND g.courseid=e.courseid "
        ."GROUP BY u.id";
        $users=  $DB->get_records_sql($sql, array($this->quizz->course));

        if (!empty($users)) {
        foreach ($users as $user) {
                fputcsv($studentList, array($user->lastname, $user->firstname, $user->idnumber, $user->email, $user->id, $user->groups_list), self::CSV_SEPARATOR,'"');
            }
        }
        fclose($studentList);

        return $this->amcAssociation();
    }

    /**
     * Return an array of students with added fields for identified users.
     *
     * Initialize $this->grades.
     * Sets $this->usersknown and $this->usersunknown.
     *
     *
     * @return boolean Success?
     */
    protected function writeFileApogeeCsv() {
        $input = $this->fopenRead($this->workdir . self::PATH_AMC_CSV);
        if (!$input) {
            return false;
        }
        $output = fopen($this->workdir . self::PATH_APOGEE_CSV, 'w');
        if (!$output) {
            return false;
        }
        
        $header = fgetcsv($input, 0, self::CSV_SEPARATOR);
        if (!$header) {
            return false;
        }
        $getCol = array_flip($header);
    fputcsv($output, array('id','name','surname','groups', 'mark'), self::CSV_SEPARATOR);
    $this->grades = array();
        while (($data = fgetcsv($input, 0, self::CSV_SEPARATOR)) !== FALSE) {
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
            'rawgrade' => str_replace(',', '.', $data[$getCol['Mark']])
                                                    );
        if ($data[$getCol['A:id']]!='NONE'){
            fputcsv($output, array($data[$getCol['A:id']],$data[$getCol['name']],$data[$getCol['surname']],$data[$getCol['groupslist']], $data[$getCol['Mark']]), self::CSV_SEPARATOR);
        }
        }
        fclose($input);
        fclose($output);
        

        return true;
    }
    
    protected function writeGrades(){

    global $DB;
    $grades = $this->getMarks();
    $record = $DB->get_record('automultiplechoice', array('id' => $this->quizz->id), '*');
    \automultiplechoice_grade_item_update($record, $grades);
    return true;
    } 
    
    
    
    /**
     * returns an array to fill the Moodle grade system from the raw marks .
     *
     * @return array grades
     */
    public function getMarks() {
        $this->readGrades();
        $namedGrades = array();
        foreach ($this->grades as $grade) {
            if ($grade->userid) {
                $namedGrades[$grade->userid] = (object) array(
                    'id' => $grade->userid,
                    'userid' => $grade->userid,
                    'rawgrade' => $grade->rawgrade,
                );
            }
        }
        return $namedGrades;
    }




    /**
     * @return boolean
     */
    public function isGraded() {
        return (file_exists($this->workdir . AmcProcessGrade::PATH_AMC_CSV));
    }


    /**
     * Remove the prefixes configured at the module level.
     *
     * @param string $idnumber
     * @return int
     */
    static protected function removePrefixFromIdnumber($idnumber) {
        $prefixestxt = get_config('mod_automultiplechoice', 'idnumberprefixes');
        $prefixes = array_filter(array_map('trim', preg_split('/\R/', $prefixestxt)));
        foreach ($prefixes as $p) {
            if (strncmp($idnumber, $p, strlen($p)) === 0) {
                return (int) substr($idnumber, strlen($p));
            }
        }
        return (int) $idnumber;
    }

    /**
     * returns a list of students with anotated answer sheets
     * @return array of (int) user.id
     */
    public function getUsersIdsHavingAnotatedSheets() {
        global $DB;

        $files = glob($this->workdir . '/cr/corrections/pdf/cr-*.pdf');
        $userids = array();
        foreach ($files as $file) {
        $userids[] = (int) substr($file,3,-4);
        }

        return $userids;
    }

    /**
     * Computes several statistics indicators from an array
     *
     * @param array $array
     * @param string $output
     * @return float
     */
    protected function mmmr($array, $output = 'mean'){
        if (empty($array) || !is_array($array)) {
            return FALSE;
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

    private static function fopenRead($filename) {
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
    * Sends a Moodle message to all students having an anotated sheet
    * @param $usersIds array(user.id => user.username)
    * @return integer # messages sent
    */
    public function sendAnotationNotification($usersIds) {
        global $USER;
        $url = new \moodle_url('/mod/automultiplechoice.php', array('a' => $this->quizz->id));
        
        $eventdata = new \object();
        $eventdata->component         = 'mod_automultiplechoice';
        $eventdata->name              = 'anotatedsheet';
        $eventdata->userfrom          = $USER;
        $eventdata->subject           = "Correction disponible";
        $eventdata->fullmessageformat = FORMAT_PLAIN;   // text format
        $eventdata->fullmessage       = "Votre copie corrigée est disponible pour le QCM ". $this->quizz->name;
        $eventdata->fullmessagehtml   = "Votre copie corrigée est disponible pour le QCM ". $this->quizz->name
                                      . " à l'adresse " . \html_writer::link($url, $url) ;
        $eventdata->smallmessage      = "Votre copie corrigée est disponible pour le QCM ". $this->quizz->name;

        // documentation : http://docs.moodle.org/dev/Messaging_2.0#Message_dispatching
        $count = 0;
        foreach ($usersIds as $userid) {
            $eventdata->userto = $userid;
            $res = message_send($eventdata);
            if ($res) {
                $count++;
            }
        }
        return $count;
    }

}
