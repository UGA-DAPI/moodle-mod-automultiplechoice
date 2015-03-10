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



class AmcProcessExport extends AmcProcess
{
    const PATH_AMC_CSV = '/exports/grades.csv';
    const PATH_AMC_ODS = '/exports/grades.ods';
    const PATH_APOGEE_CSV = '/exports/grades_apogee.csv';
    const CSV_SEPARATOR = ';';



/**
     *      * @return boolean
     *           */
    public function makeFailedPdf() {
        if (extension_loaded('sqlite3')){   
            $capture = new \SQLite3($this->workdir . '/data/capture.sqlite',SQLITE3_OPEN_READWRITE);
            $results = $capture->query('SELECT * FROM capture_failed');
            $scans = array();
            while ($row = $results->fetchArray()) {
                $scans[] = $this->workdir.substr($row[0],7);

            }
            $output = $this->normalizeFilename('failed');
            $scans[] = $this->workdir.'/'.$output;
            $res = $this->shellExec('convert ',$scans);
            return $res;
        }
        return false;
    }
    



    /**
     * Shell-executes 'amc export' to get a csv file
     * @return bool
     */
    protected function amcExport($type='csv') {
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
            '--fich-noms', $this->get_students_list(),
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
        if ($type =='csv'){
            $res = $this->shellExecAmc('export', $parametersCsv);
        }else{
             $this->shellExecAmc('export', $parametersOds);
        }
        chdir($oldcwd);
        if ($res) {
            $this->log('export', 'scoring.csv');
            Log::build($this->quizz->id)->write('grading');
        }
        if (!file_exists($csvfile) || !file_exists($odsfile)) {
            $this->errors[] = "Le fichier n'a pu être généré. Consultez l'administrateur.";
            return false;
        }
        return $res;
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
    
        while (($data = fgetcsv($input, 0, self::CSV_SEPARATOR)) !== FALSE) {
        $idnumber = $data[$getCol['student.number']];
   
        if ($data[$getCol['A:id']]!='NONE'){
            fputcsv($output, array($data[$getCol['A:id']],$data[$getCol['name']],$data[$getCol['surname']],$data[$getCol['groupslist']], $data[6]), self::CSV_SEPARATOR);
        }
        }
        fclose($input);
        fclose($output);
        

        return true;
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
