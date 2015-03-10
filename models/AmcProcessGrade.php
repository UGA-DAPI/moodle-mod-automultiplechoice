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
    

    public $usersknown = 0;
    public $usersunknown = 0;

    protected $format;
    private $actions;
    

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
     * Shell-executes 'amc prepare' for extracting grading scale (Bareme)
     * @return bool
     */
    protected function amcPrepareBareme() 
    {
        $pre = $this->workdir;
        $path = get_config('mod_automultiplechoice', 'xelatexpath');
        if ($path === '') {
            $path = '/usr/bin/xelatex';
        }
        $parameters = array(
            '--n-copies', (string) $this->quizz->amcparams->copies,
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
        }
        return $res;
    }

<<<<<<< 1988cadf1219cf4b9e3a282c33b5bdf430613585
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
            '--seuil', '0.86', // black ratio threshold
            '--grain', $this->quizz->amcparams->gradegranularity,
            '--arrondi', $this->quizz->amcparams->graderounding,
            '--notemin', $this->quizz->amcparams->minscore,
            '--notemax', $this->quizz->amcparams->grademax,
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
            '--fich-noms', $this->get_students_list(),
            '--noms-encodage', 'UTF-8',
        );
        $parametersCsv = array_merge($parameters, array(
            '--module', 'CSV',
            '--output', $csvfile,
            '--csv-build-name', '(nom|surname) (prenom|name)',
            '--option-out', 'columns=student.copy,student.key,patronomic,name,surname,moodleid,groupslist',
            '--option-out', 'separateur=' . self::CSV_SEPARATOR,
            '--option-out', 'decimal=,',
            '--option-out', 'ticked=',
        ));
        $parametersOds = array_merge($parameters, array(
            '--module', 'ods',
            '--output', $odsfile,
            '--option-out', 'columns=student.copy,student.key,patronomic,name,surname,groupslist',
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
     * low-level Shell-executes 'amc annote'
     * fills the cr/corrections/jpg directory with individual annotated copies
     * @return bool
     */
    private function amcAnnote() {
        $pre = $this->workdir;
        if (!is_dir($pre. '/cr/corrections/jpg')) { // amc-annote will silently fail if the dir does not exist
            mkdir($pre. '/cr/corrections/jpg', 0777, true);
        }
        if (!is_dir($pre. '/cr/corrections/pdf')) {
            mkdir($pre. '/cr/corrections/pdf', 0777, true);
        }
        $parameters = array(
            '--projet', $pre,
            '--ch-sign', '4',
            '--cr', $pre . '/cr',
            '--data', $pre.'/data',
            '--id-file', $pre. '/student.txt'  , // undocumented option: only work with students whose ID is in this file
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
        $res = $this->shellExecAmc('annote', $parameters,true);
        if ($res) {
            $this->log('annote', '');
        }
        return $res;
    }
     /**
	     *      * lowl-level Shell-executes 'amc regroupe'
	     *           * fills the cr/corrections/pdf directory with a global pdf file (parameter single==true) for all copies
	     *                * or one pdf per student (single==false)
	     *                     * @single bool
	     *                          * @return bool
	     *                               */
    protected function amcRegroupe($single=true) {
	    $pre = $this->workdir;
	    if ($single) {
		    $addon = array(
			    '--single-output', $this->normalizeFilename('corrections'), // merge all sheets into one file that rules them all
		    );
	    } else {
		    $addon = array(
			    '--modele', 'cr-(N).pdf', // "(ID)" is replaced by the complete name
			    '--id-file', $pre. '/student.txt'  , // undocumented option: only work with students whose ID is in this file
			   // '--csv-build-name', '(nom|id)-(prenom|surname)', // defines the complete name as the columns "id-surname" of the CSV
		    );
	    }
	    $parameters = array_merge(
		    array(
			    '--no-compose',
			    '--projet',  $pre,
			    '--sujet', $pre. '/' . $this->normalizeFilename('sujet'),
			    '--data', $pre.'/data',
			    '--progression-id', 'regroupe',
			    '--progression', '1',
			    '--fich-noms', $pre . self::PATH_STUDENTLIST_CSV,
			    '--noms-encodage', 'UTF-8',
			    '--sort', 'n',
			    '--register',
			    '--no-force-ascii'
			    /* // useless with no-compose
			  '--tex-src', $pre . '/' . $this->format->getFilename(),
			'--filter', $this->format->getFilterName(),
			      '--with', 'xelatex',
			    '--filtered-source', $pre.'/prepare-source_filtered.tex',
			 '--n-copies', (string) $this->quizz->amcparams->copies,
		     */
		    ),
		    $addon
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
     * (high-level) executes "amc annote" then "amc regroupe" to get one or several pdf files
     * for the moment, only one variant is possible : ONE global file, NO compose
     * @todo (maybe) manages all variants
     * @return bool
     */
    protected function amcAnnotePdf() {
	$pre = $this->workdir;    
	//array_map('unlink', glob($pre.  "/cr/corrections/jpg/*.jpg"));
        array_map('unlink', glob($pre.  "/cr/corrections/pdf/*.pdf"));
        $allcopy = array_map('get_code',glob($pre . '/cr/name-*.jpg'));
	foreach($allcopy as $copy){
		$fp = fopen($pre . '/student.txt', 'w');
		fwrite($fp,str_replace('_',':',$copy));
		fclose($fp);	
		if (!$this->amcAnnote()) {
			return false;
		}
		if (!$this->amcRegroupe(false)) {
			return false;
		}
	}
	return $this->amcRegroupe(true);
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
     * @return boolean Success?
     */
    protected function writeFileStudentsList() 
    {
        global $DB;
        
        $studentList = fopen($this->workdir . self::PATH_STUDENTLIST_CSV, 'w');
        if (!$studentList) {
            return false;
        }
        
        fputcsv($studentList, array('surname', 'name', 'patronomic', 'id', 'email', 'moodleid', 'groupslist'), self::CSV_SEPARATOR);

        $codelength = get_config('mod_automultiplechoice', 'amccodelength');
        $sql = 'SELECT u.idnumber ,u.firstname, u.lastname,u.alternatename,u.email, u.id as id , GROUP_CONCAT(DISTINCT g.name ORDER BY g.name) as groups_list ';
        $sql .= 'FROM {user} u ';
        $sql .= 'JOIN {user_enrolments} ue ON (ue.userid = u.id) ';
        $sql .= 'JOIN {enrol} e ON (e.id = ue.enrolid) ';
        $sql .= 'LEFT JOIN  {groups_members} gm ON u.id=gm.userid ';
        $sql .= 'LEFT JOIN {groups} g ON g.id=gm.groupid  AND g.courseid=e.courseid ';
        $sql .= 'WHERE u.idnumber != "" AND e.courseid = ? ';
	$sql .= 'AND g.courseid = e.courseid ';
        $sql .= 'GROUP BY u.id';    
        $users =  $DB->get_records_sql($sql, array($this->quizz->course));
    
        if (!empty($users)) {
            foreach ($users as $user) {
                fputcsv(
                        $studentList, 
                        array(
                            $user->lastname, 
                            $user->firstname,
                            $user->alternatename, 
                            substr($user->idnumber, -1*$codelength), 
                            $user->email, 
                            $user->id, 
                            $user->groups_list
                        ), 
                        self::CSV_SEPARATOR,
                        '"'
                    );
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
	fputcsv($output, array('id','patronomic','name','surname','groups', 'mark'), self::CSV_SEPARATOR);
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
			fputcsv($output, array($data[$getCol['A:id']],$data[$getCol['patronomic']],$data[$getCol['name']],$data[$getCol['surname']],$data[$getCol['groupslist']], $data[$getCol['Mark']]), self::CSV_SEPARATOR);
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





}
