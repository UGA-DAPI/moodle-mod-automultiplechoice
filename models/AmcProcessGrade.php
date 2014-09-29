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
    const PATH_FULL_CSV = '/exports/grades_with_names.csv';
    const PATH_STUDENTLIST_CSV = '/exports/student_list.csv';
    const CSV_SEPARATOR = ';';

    protected $grades = array();
    public $usersknown = 0;
    public $usersunknown = 0;

    protected $format;

    /**
     * Constructor
     *
     * @param Quizz $quizz
     * @param string $formatName "txt" | "latex"
     */
    public function __construct(Quizz $quizz, $formatName) {
        parent::__construct($quizz);
        $this->format = amcFormat\buildFormat($formatName);
        if (!$this->format) {
            throw new \Exception("Erreur, pas de format de QCM pour AMC.");
        }
        $this->format->quizz = $this->quizz;
        $this->format->codelength = $this->codelength;
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
            '--filtered-source', $pre . '/prepare-source_filtered.tex', // the LaTeX will be written in this file
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
            '--grain', '0.25',
            '--arrondi', 'inf',
            '--notemin', $this->quizz->amcparams->minscore,
            '--notemax', $this->quizz->score,
            '--plafond',
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
        $parameters = array(
            '--module', 'CSV',
            '--data', $pre . '/data',
            '--useall', '0',
            '--sort', 'n',
            '--no-rtl',
            '--output', $pre . self::PATH_AMC_CSV,
            '--option-out', 'encodage=UTF-8',
            '--option-out', 'columns=student.copy,student.key,student.name',
            '--option-out', 'decimal=,',
            '--option-out', 'ticked=',
            '--option-out', 'separateur=' . self::CSV_SEPARATOR,
            '--fich-noms', $pre . self::PATH_STUDENTLIST_CSV,
            '--noms-encodage', 'UTF-8',
            '--csv-build-name', '(nom|surname) (prenom|name)',
        );
        $res = $this->shellExecAmc('export', $parameters);
        if ($res) {
            $this->log('export', 'scoring.csv');
            $amclog = Log::build($this->quizz->id);
            $amclog->write('grading');
        }
        return $res;
    }

    /**
     * low-level Shell-executes 'amc annote'
     * fills the cr/corrections/jpg directory with individual annotated copies
     * @return bool
     */
    private function amcAnnote() {
        if (!is_dir($this->workdir . '/cr/corrections/jpg')) { // amc-annote will silently fail if the dir does not exist
            mkdir($this->workdir . '/cr/corrections/jpg', 0777, true);
        }
        if (!is_dir($this->workdir . '/cr/corrections/pdf')) {
            mkdir($this->workdir . '/cr/corrections/pdf', 0777, true);
        }
        $pre = $this->workdir;
        $parameters = array(
            '--projet', $pre,
            '--ch-sign', '4',
            '--cr', $pre . '/cr',
            '--data', $pre.'/data',
            //'--id-file',  '', // undocumented option: only work with students whose ID is in this file
            '--taille-max', '1000x1500',
            '--qualite', '90',
            '--line-width', '2',
            '--indicatives', '1',
            '--symbols', '0-0:none/#000000,0-1:circle/#ff0000,1-0:mark/#ff0000,1-1:mark/#00ff00',
            '--position', 'case',
            '--ecart', '10',
            '--pointsize-nl', '80',
            '--verdict', '%(ID) Note: %s/%m (score total : %S/%M)',
            '--verdict-question', '"%s / %m"',
            '--no-rtl',
            '--no-changes-only',
            '--fich-noms', $pre . self::PATH_STUDENTLIST_CSV,
            //'--noms-encodage', 'UTF-8',
            //'--csv-build-name', 'surname name',
        );
        $res = $this->shellExecAmc('annote', $parameters);
        if ($res) {
            $this->log('annote', '');
        }
        return $res;
    }

     /**
     * lowl-level Shell-executes 'amc regroupe'
     * fills the cr/corrections/pdf directory with a global pdf file for all copies
     * @return bool
     */
    protected function amcRegroupe() {
        $pre = $this->workdir;
        $parameters = array(
            //'--id-file',  '', // undocumented option: only work with students whose ID is in this file
            '--no-compose',
            '--projet',  $pre,
            '--sujet', $pre. '/' . $this->normalizeFilename('sujet'),
            '--data', $pre.'/data',
            '--tex-src', $pre . '/' . $this->format->getFilename(),
            '--filter', $this->format->getFilterName(),
            '--with', 'xelatex',
            '--filtered-source', $pre.'/prepare-source_filtered.tex', // the LaTeX will be written in this file
            '--n-copies', (string) $this->quizz->amcparams->copies,
            '--progression-id', 'regroupe',
            '--progression', '1',
            //'--modele', '',
            '--fich-noms', $pre . self::PATH_STUDENTLIST_CSV,
            '--noms-encodage', 'UTF-8',
            '--csv-build-name', '(nom|surname) (prenom|name)',
            '--single-output', $this->normalizeFilename('corrections'),
            '--sort', 'n',
            '--register',
            '--no-force-ascii'
        );
        $res = $this->shellExecAmc('regroupe', $parameters);
        if ($res) {
            $this->log('regroup', '');
            $amclog = Log::build($this->quizz->id);
            $amclog->write('correction');
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
     * (high-level) executes "amc annote" then "amc regroupe" to get one or several pdf files
     * for the moment, only one variant is possible : ONE global file, NO compose
     * @todo (maybe) manages all variants
     * @return bool
     */
    protected function amcAnnotePdf() {
        array_map('unlink', glob($this->workdir .  "/cr/corrections/jpg/*.jpg"));
        array_map('unlink', glob($this->workdir .  "/cr/corrections/pdf/*.pdf"));

        if (!$this->amcAnnote()) {
            return false;
        }
        return $this->amcRegroupe();
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
        $header = fgetcsv($input, 1024, self::CSV_SEPARATOR);
        if (!$header) {
            return false;
        }
        $getCol = array_flip($header);

        $this->grades = array();
        while (($data = fgetcsv($input, 1024, self::CSV_SEPARATOR)) !== FALSE) {
            $idnumber = $data[$getCol['student.number']];
            $user = null;
            if ($idnumber) {
                $user = getStudentByIdNumber($idnumber);
            }
            if ($user) {
                $userid = $user->id;
                $this->usersknown++;
            } else {
                $userid = null;
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
     * Initialize $this->grades.
     * Sets $this->usersknown and $this->usersunknown.
     *
     *
     * @return boolean Success?
     */
    protected function writeFileWithIdentifiedStudents() {
        $input = $this->fopenRead($this->workdir . self::PATH_AMC_CSV);
        if (!$input) {
            return false;
        }
        $output = fopen($this->workdir . self::PATH_FULL_CSV, 'w');
        if (!$output) {
            return false;
        }
        $studentList = fopen($this->workdir . self::PATH_STUDENTLIST_CSV, 'w');
        if (!$studentList) {
            return false;
        }
        fputcsv($studentList, array('surname', 'name', 'id', 'email'), self::CSV_SEPARATOR);

        $header = fgetcsv($input, 1024, self::CSV_SEPARATOR);
        if (!$header) {
            return false;
        }
        $getCol = array_flip($header);
        $header[] = 'firstname';
        $header[] = 'lastname';
        $header[] = 'idnumber';
        fputcsv($output, $header, self::CSV_SEPARATOR);

        $this->grades = array();
        while (($data = fgetcsv($input, 1024, self::CSV_SEPARATOR)) !== FALSE) {
            $idnumber = $data[$getCol['student.number']];
            $user = null;
            if ($idnumber) {
                $user = getStudentByIdNumber($idnumber);
            }
            if ($user) {
                $userid = $user->id;
                $data[$getCol['Name']] = fullname($user);
                $data[] = $user->firstname;
                $data[] = $user->lastname;
                $data[] = $user->idnumber;
                $this->usersknown++;
                fputcsv($studentList, array($user->lastname, $user->firstname, $idnumber, $user->email), self::CSV_SEPARATOR);
            } else {
                $userid = null;
                $data[] = '';
                $data[] = '';
                $data[] = '';
                $this->usersunknown++;
            }
            $this->grades[] = (object) array(
                'userid' => $userid,
                'rawgrade' => str_replace(',', '.', $data[$getCol['Mark']])
            );
            fputcsv($output, $data, self::CSV_SEPARATOR);
        }
        fclose($input);
        fclose($output);
        fclose($studentList);

        return $this->amcAssociation();
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
    public function hasAnotatedFiles() {
        return (file_exists($this->workdir . '/cr/corrections/pdf/' . $this->normalizeFilename('corrections')));
    }

    /**
     * @return boolean
     */
    public function isGraded() {
        return (file_exists($this->workdir . AmcProcessGrade::PATH_AMC_CSV));
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
}
