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

class AmcProcessAssociate extends AmcProcess
{

    public $usersknown = 0;
    public $usersunknown = 0;
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
        if !($this->amcNote()){
            amc\FlashMessageManager::addMessage('error', "Erreur lors du calcul des notes");
            return $res;
        }else{
            return $this->amcAssociation();
        }
    }

    /**
     * @return boolean
     */
    public function deleteFailed($scan) {
        if (extension_loaded('sqlite3')){   
            $capture = new \SQLite3($this->workdir . '/data/scoring.sqlite',SQLITE3_OPEN_READ);
            if ($scan=='all'){
                $results = $capture->query('SELECT * FROM capture_failed');
                while ($row = $results->fetchArray()) {
                    $scan = substr($row[0],14);
                    array_map('unlink', glob($this->workdir . '/scans/'.$scan));
                }
                return  $capture->exec('DELETE FROM capture_failed ');
            }else{
                $result = $capture->querySingle('SELECT * FROM capture_failed WHERE filename LIKE "%'.$scan.'"');
                if (substr($result,14)==$scan){
                    unlink( glob($this->workdir . '/scans/'.$scan));
                    return  $capture->exec('DELETE FROM capture_failed WHERE filename LIKE "%'.$scan.'"');
                }
            }
        return false;
        }else{

        }
    }
}
