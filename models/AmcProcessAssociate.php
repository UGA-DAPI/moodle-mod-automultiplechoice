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
//require_once __DIR__ . '/Log.php';
//require_once __DIR__ . '/AmcFormat/Api.php';

class AmcProcessAssociate extends AmcProcess
{

    public $copyauto = array();
    public $copymanual = array();
    public $copyunknown =array();
    const CSV_SEPARATOR = ';';
  

    
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
        $res = $this->shellExecAmc('association-auto', $parameters);
        if ($res) {
            $this->log('association-auto', 'OK.');
            $amclog = Log::build($this->quizz->id);
            $amclog->write('associating');
        }
        return $res;
    }
    /**
     * Shell-executes 'amc association-auto'
     * @return bool
     */
    protected function amcAssociation_list() {
        $pre = $this->workdir;
        $parameters = array(
            '--data', $pre . '/data',
            '--list', 
        );
        $escapedCmd = escapeshellcmd('auto-multiple-choice '.'association' );
        $escapedParams = array_map('escapeshellarg', $parameters);
        $shellCmd = $escapedCmd . " " . join(" ", $escapedParams);
        $lines = array();
        $returnVal = 0;
        exec($shellCmd, $lines, $returnVal);
        foreach ($lines as $l){
            $split = get_list_row($l);
        if (isset($split['student'])){
            $id = $split['student'].'-'.$split['copy'];
            if ($split['status']=='manual'){
                $this->copymanual[$id] = $split['idnumber'];
            }else if ($split['status']=='auto'){
                $this->copyauto[$id] = $split['idnumber'];
            }
        }
        }
        return $returnVal;
    }
    /**
     * 
     *
     *
     *
     * @return boolean Success?
     */
    public function associate() {
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
        if (!$this->amcNote()){
            amc\FlashMessageManager::addMessage('error', "Erreur lors du calcul des notes");
            return $res;
        }else{
            return $this->amcAssociation();
        }
    }

    /**
     * @return boolean
     */
    public function get_association() {
        if (extension_loaded('sqlite3')){   
            $allcopy = array();
            $assoc = new \SQLite3($this->workdir . '/data/association.sqlite',SQLITE3_OPEN_READ);
            $score = new \SQLite3($this->workdir . '/data/scoring.sqlite',SQLITE3_OPEN_READ);
            $assoc_association= $cassoc->query('SELECT student, copy, manual, auto  FROM association_association');
            $score_code= $assoc->query('SELECT student, copy, value FROM scoring_code');
            while ($row = $assoc_association->fetchArray()) {
                $id = $row['student'].'-'.$row['copy'];
                    if ($row['manual']!=''){
                        $this->copymanual[$id] = $row['manual'];
                    }
                    if ($row['auto']!=''){
                        $this->copyauto[$id] = $row['auto'];
                    }
            }
            while ($row = $score_code->fetchArray()) {
                $id = $row['student'].'-'.$row['copy'];
                $allcopy[$id] = $row['value'];
            }
            $this->copyunknown = array_diff_key(array_merge($this->copymanual,$this->copyauto),$allcopy);
            
        }else{
            $allcopy = array_fill_keys(array_map('get_code',glob($this->workdir . '/cr/name-*.jpg')),'');
            if ($this->amcAssociation_list()==0){
                $this->copyunknown = array_diff_key($allcopy,array_merge($this->copymanual,$this->copyauto));
            }
        }
    }


}
