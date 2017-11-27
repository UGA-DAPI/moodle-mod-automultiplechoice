<?php

namespace mod_automultiplechoice\local\amc;

require_once(__DIR__ . './../../../locallib.php');

class associate extends \mod_automultiplechoice\local\amc\process
{
    public $copyauto = array();
    public $copymanual = array();
    public $copyunknown = array();

    public function __construct(\mod_automultiplechoice\local\models\quiz $quiz ,$formatName = 'latex') {
        parent::__construct($quiz, $formatName);
    }
    /**
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
        fputcsv($studentList, array('surname', 'name', 'patronomic', 'id', 'email', 'moodleid', 'groupslist'), self::CSV_SEPARATOR);
        $codelength = get_config('mod_automultiplechoice', 'amccodelength');
        $sql = 'SELECT u.idnumber ,u.firstname, u.lastname,u.alternatename,u.email, u.id as id , GROUP_CONCAT(DISTINCT g.name ORDER BY g.name) as groups_list ';
        $sql .= 'FROM {user} u ';
        $sql .= 'JOIN {user_enrolments} ue ON (ue.userid = u.id) ';
        $sql .= 'JOIN {enrol} e ON (e.id = ue.enrolid) ';
        $sql .= 'LEFT JOIN  {groups_members} gm ON u.id=gm.userid ';
        $sql .= 'LEFT JOIN {groups} g ON g.id=gm.groupid  AND g.courseid=e.courseid ';
        $sql .= 'WHERE u.idnumber != "" AND e.courseid = ? ';
        $sql .= 'GROUP BY u.id';
        $users = $DB->get_records_sql($sql, array($this->quiz->course));
        if (!empty($users)) {
            foreach ($users as $user) {
                $nums = explode(";", $user->idnumber);
                foreach ($nums as $num) {
                    fputcsv(
                        $studentList,
                        array(
                            $user->lastname,
                            $user->firstname,
                            $user->alternatename,
                            substr($num, -1*$codelength),
                            $user->email,
                            $user->id,
                            $user->groups_list
                        ),
                        self::CSV_SEPARATOR,
                        '"'
                    );
                }
            }
        }
        fclose($studentList);
        if (!$this->amcNote()) {
            \mod_automultiplechoice\local\helpers\flash_message_manager::addMessage('error', "Erreur lors du calcul des notes");
            return $res;
        } else {
            return $this->amcAssociation();
        }
    }
    /**
     * @return boolean
     */
    public function get_association() {
        if ((extension_loaded('sqlite3')) && (file_exists($this->workdir . '/data/association.sqlite'))) {
            $allcopy = array();

            $assoc = new \SQLite3($this->workdir . '/data/association.sqlite', SQLITE3_OPEN_READONLY);
            $assoc_association = $assoc->query('SELECT student, copy, manual, auto  FROM association_association');

            while ($row = $assoc_association->fetchArray()) {
                $id = $row['student'].'_'.$row['copy'];
                if ($row['manual'] != '') {
                    $this->copymanual[$id] = $row['manual'];
                }
                if ($row['auto'] != '') {
                    $this->copyauto[$id] = $row['auto'];
                }
            }
            $this->copyunknown = array_diff_key($allcopy, $this->copymanual, $this->copyauto);
        } else {
            $allcopy = array_fill_keys(
                array_map(
                    'get_code',
                    glob($this->workdir . '/cr/name-*.jpg')
                ),
                ''
            );

            if ($this->amcAssociation_list() == 0) {
                $this->copyunknown = array_diff_key($allcopy, $this->copymanual, $this->copyauto);
            }
        }
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
        foreach ($lines as $l) {
            $split = get_list_row($l);
            if (isset($split['student'])) {
                $id = $split['student'].'_'.$split['copy'];
                if ($split['status']=='manual') {
                    $this->copymanual[$id] = $split['idnumber'];
                } else if ($split['status']=='auto') {
                    $this->copyauto[$id] = $split['idnumber'];
                }
            }
        }
        return $returnVal;
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
        $res = $this->shellExecAmc('association-auto', $parameters);
        if ($res) {
            $this->log('association-auto', 'OK.');
            $amclog = \mod_automultiplechoice\local\helpers\log::build($this->quiz->id);
            $amclog->write('associating');
        }
        return $res;
    }
}
