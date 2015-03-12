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
     * Shell-executes 'amc prepare' for creating pdf files
     *
     * @param string $formatName "txt" | "latex"
     * @return bool
     */
    public function amcCreateCorrection($formatName) {
        $this->errors = array();

        

        $pre = $this->workdir;
        $res = $this->shellExecAmc('prepare',
            array(
                '--n-copies', (string) $this->quizz->amcparams->copies,
                '--with', 'xelatex',
                '--filter', $format->getFiltername(),
                '--mode', 'k',
                '--prefix', $pre,
                '--out-corrige', $pre . '/' . $this->normalizeFilename('corrige'),
                '--latex-stdout',
                $pre . '/' . $format->getFilename()
            )
        );
        if ($res) {
            $amclog = Log::build($this->quizz->id);
            $this->log('prepare:pdf', 'catalog corrige sujet');
            $amclog->write('pdf');
        } else {
            $this->errors[] = "Exec of `auto-multiple-choice prepare` failed. Is AMC installed?";
        }
        return $res;
    }


    /**
     * Shell-executes 'amc export' to get a csv file
     * @return bool
     */
    public function amcExport($type='csv') {
    $file =($type=='csv')? $pre . self::PATH_AMC_CSV : $pre . self::PATH_AMC_ODS;
        $warnings = Log::build($this->quizz->id)->check('exporting');
    if (!$warnings and file_exists($file)) {
        return true;
    }
        if (file_exists($file)) {
            if (!unlink($file)) {
                $this->errors[] = "Le fichier ".strtoupper($type)." n'a pas pu être recréé. Contactez l'administrateur pour un problème de permissions de fichiers.";
                return false;
            }
        }
        $pre = $this->workdir;
        if (!is_writable($pre . '/exports')) {
            $this->errors[] = "Le répertoire /exports n'est pas accessible en écriture. Contactez l'administrateur.";
        }
        $oldcwd = getcwd();
        chdir($pre . '/exports');


        $parameters = array(
            '--data', $pre . '/data',
            '--useall', '0',
            '--sort', 'n',
            '--no-rtl',
            '--output', $file,
            '--option-out', 'encodage=UTF-8',
            '--fich-noms', $this->get_students_list(),
            '--noms-encodage', 'UTF-8',
        );
        $parametersCsv = array_merge($parameters, array(
            '--module', 'CSV',
            '--csv-build-name', '(nom|surname) (prenom|name)',
            '--option-out', 'columns=student.copy,student.key,name,surname,moodleid,groupslist',
            '--option-out', 'separateur=' . self::CSV_SEPARATOR,
            '--option-out', 'decimal=,',
            '--option-out', 'ticked=',
        ));
        $parametersOds = array_merge($parameters, array(
            '--module', 'ods',
            '--option-out', 'columns=student.copy,student.key,name,surname,groupslist',
            '--option-out', 'stats=1',
        ));
        if ($type =='csv'){
            $res = $this->shellExecAmc('export', $parametersCsv);
        }else{
            $res = $this->shellExecAmc('export', $parametersOds);
        }
        chdir($oldcwd);
        if ($res) {
            $this->log('export', 'scoring.csv');
        Log::build($this->quizz->id)->write('exporting');
        return true;
        }
        if (!file_exists($csvfile) || !file_exists($odsfile)) {
            $this->errors[] = "Le fichier n'a pu être généré. Consultez l'administrateur.";
            return false;
        }
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
    public function writeFileApogeeCsv() {
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

}
