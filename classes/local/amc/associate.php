<?php

namespace mod_automultiplechoice\local\amc;

require_once(__DIR__ . './../../../locallib.php');

class associate extends \mod_automultiplechoice\local\amc\process
{
    public $copyauto = array();
    public $copymanual = array();
    public $copyunknown = array();

    public function __construct(\mod_automultiplechoice\local\models\quiz $quiz, $formatName = 'latex')
    {
        parent::__construct($quiz, $formatName);
    }
    /**
     *
     *
     * @return boolean Success?
     */
    public function associate()
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

    public function manual_association($student, $copy, $id) {
        $pre = $this->workdir;
        $parameters = [
            '--data', $pre . '/data',
            '--set',
            '--student', $student,
            '--copy', $copy
        ];

        if (!empty($id)) {
            array_push($parameters, '--id', $id);
        }
        $res = $this->shellExecAmc('association', $parameters);
        if ($res) {
            $this->log('association', 'OK.');
            $amclog = \mod_automultiplechoice\local\helpers\log::build($this->quiz->id);
            $amclog->write('manualassoc');
        }
        return $res;
    }

    public function create_student_csv() {
        global $DB;
        $studentList = fopen($this->workdir . self::PATH_STUDENTLIST_CSV, 'w');
        if (!$studentList) {
            return false;
        }
        fputcsv($studentList, array('surname', 'name', 'patronomic', 'id', 'email', 'moodleid', 'groupslist'), self::CSV_SEPARATOR);
        $codelength = get_config('mod_automultiplechoice', 'amccodelength');
        $sql = 'SELECT u.idnumber, u.firstname, u.lastname, u.username, u.email, u.id as id , GROUP_CONCAT(DISTINCT g.name ORDER BY g.name) as groups_list ';
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
                            $user->username,
                            substr($num, -1*$codelength),
                            $user->email,
                            $user->id,
                            $user->groups_list ? $user->groups_list : ''
                        ),
                        self::CSV_SEPARATOR,
                        '"'
                    );
                }
            }
        }
        fclose($studentList);
    }

    /**
     * @return boolean
     */
    public function get_association()
    {

        if ((extension_loaded('sqlite3')) && (file_exists($this->workdir . '/data/association.sqlite'))) {

            $assoc = new \SQLite3($this->workdir . '/data/association.sqlite', SQLITE3_OPEN_READONLY);
            $assoc_association = $assoc->query('SELECT student || "_" || copy as id, auto, manual  FROM association_association');

            while ($row = $assoc_association->fetchArray()) {
                $id = $row['id'];
                if ($row['manual'] != '') {
                    $this->copymanual[$id] = $row['manual'];
                }
                if ($row['auto'] != '') {
                    $this->copyauto[$id] = $row['auto'];
                }
            }
            $allcopy = array_fill_keys(
                array_map(
                    'get_code',
                    glob($this->workdir . '/cr/name-*.jpg')
                ),
                ''
            );
            $this->copyunknown = array_diff_key($allcopy, $this->copymanual, $this->copyauto);
        } else {
            $this->amcAssociation_list();
        }
    }
    /**
     * Shell-executes 'amc association-auto'
     * @return bool
     */
    protected function amcAssociation_list()
    {
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
                if ($split['status'] === 'manual') {
                    $this->copymanual[$id] = $split['idnumber'];
                } elseif ($split['status'] === 'auto') {
                    $this->copyauto[$id] = $split['idnumber'];
                } else if ($split['status'] === 'none') {
                    $this->copyunknown[$id] = '';
                }
            }
        }
        return $returnVal;
    }
     /**
     * Shell-executes 'amc association-auto'
     * @return bool
     */
    public function amcAssociation()
    {
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

    /**
     * Handle form submission for manual association
     *
     *
     * @param array $data data from request ($_POST)
     * @return array $errors empty if no errors
     */
    public function handle_manual_association($data)
    {
        $errors = [];
        if (isset($data['student']) && !empty($data['file'])) {
            $studentnumber = $data['student'];
            $filename =  $data['file'];
            $amcinfos = explode('_', $filename);
            if (!$amcinfos || empty($amcinfos[0]) || empty($amcinfos[1])) {
                // $filename should be formated with x_y where x / y are numbers
                $errors[] = 'filename not formated as expected.';
            } else {
                // call appropriate amc cmd depending on student number
                // if studentnumber is empty then launch the cmd without id param
                if (!$this->manual_association($amcinfos[0], $amcinfos[1], $studentnumber)) {
                    $errors[] = 'amc manual association cmd failed';
                }
            }

        } else {
            $errors[] = 'missing parameter(s) in query.';
        }

        return $errors;
    }

    public function get_association_modes()
    {
        return [
            'unknown'  => get_string('unknown', 'automultiplechoice'),
            'manual' => get_string('manual', 'automultiplechoice'),
            'auto' => get_string('auto', 'automultiplechoice'),
            'all' => get_string('all')
        ];
    }

    public function get_user_modes()
    {
        return [
          'without' => get_string('without', 'automultiplechoice'),
          'all' => get_string('all')
        ];
    }

    /**
     * Get the users to display depending on filter value.
     */
    public function get_all_users_data($mode) {
        if ($mode === 'unknown') {
            return $this->copyunknown;
        } elseif ($mode === 'manual') {
            return $this->copymanual;
        } elseif ($mode === 'auto') {
            return $this->copyauto;
        } elseif ($mode === 'all') {
            return array_merge($this->copyunknown, $this->copymanual, $this->copyauto);
        }
    }
}
