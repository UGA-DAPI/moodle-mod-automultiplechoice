<?php
/**
 * Internal library of functions for module automultiplechoice
 *
 * All the automultiplechoice specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_automultiplechoice
 * @copyright  2013-2014 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


//use \mod\automultiplechoice as amc;

global $DB,$CFG;
require_once dirname(dirname(__DIR__)) . '/config.php';
require_once($CFG->libdir . '/formslib.php');
require_once __DIR__ . '/lib.php';
//require_once __DIR__ . '/models/Quizz.php';
//require_once __DIR__ . '/models/AmcProcess.php';
//require_once __DIR__ . '/components/HtmlHelper.php';
//require_once __DIR__ . '/components/Controller.php';

defined('MOODLE_INTERNAL') || die();

global $DB;
/* @var $DB \moodle_database */

if (version_compare(phpversion(), '5.4.0') < 0) {
    error("This module requires PHP 5.4. It won't work with an older PHP.");
}

/**
 * Return the list of questions available for a given user and a context.
 *
 * This function ignore the permissions set in Moodle, except for 'moodle/question:useall'.
 * Then it lists all the questions defined in the current course.
 *
 * @todo Check if the current user has access to "system", then add its questions.
 *
 * @param object $user User record
 * @param object $course Course record
 * @return array List of objects with fields: id, categoryname, title, timemodified
 */
function automultiplechoice_list_questions($user, $course) {
    global $DB, $CFG;

    $course_context = context_course::instance($course->id);

    if (!has_capability('moodle/question:useall', $course_context, $user)) {
        return array();
    }

    if ($CFG->version >= 2013111800) {
        $qtable = 'qtype_multichoice_options';
        $qfield = 'questionid';
    } else {
        $qtable = 'question_multichoice';
        $qfield = 'question';
    }
    $sql = "SELECT q.id, qc.name AS categoryname, q.name AS title, q.timemodified "
            . "FROM {question} q JOIN {question_categories} qc ON q.category = qc.id "
            . " JOIN {" . $qtable . "} qm ON qm.{$qfield}=q.id "
            . "WHERE q.hidden = 0 AND qc.contextid = " . $course_context->id
            . " ORDER BY qc.sortorder, q.name";
    return $DB->get_records_sql($sql);
}

/**
 * Return the list of questions available for a given user and a context.
 *
 * This function ignore the permissions set in Moodle, except for 'moodle/question:useall'.
 * Then it lists all the questions defined in the current course.
 *
 * @todo Check if the current user has access to "system", then add its questions.
 *
 * @param object $user User record
 * @param object $course Course record
 * @return array List of objects with fields: id, categoryname, title, timemodified
 */
function automultiplechoice_list_user_questions($user) {
    global $DB, $CFG;

    $course_context = context_course::instance($course->id);

    if (!has_capability('moodle/question:useall', $course_context, $user)) {
        return array();
    }

    if ($CFG->version >= 2013111800) {
        $qtable = 'qtype_multichoice_options';
        $qfield = 'questionid';
    } else {
        $qtable = 'question_multichoice';
        $qfield = 'question';
    }
    $sql = "SELECT q.id, qc.name AS categoryname, q.name AS title, q.timemodified "
            . "FROM {question} q JOIN {question_categories} qc ON q.category = qc.id "
            . " JOIN {" . $qtable . "} qm ON qm.{$qfield}=q.id "
            . "WHERE q.hidden = 0"
            . " ORDER BY qc.sortorder, q.name";
    return $DB->get_records_sql($sql);
}

/**
 * Parses the config setting 'instructions' to convert it into an associative array (instruction => title).
 *
 * @return array
 */
function parse_default_instructions() {
    $raw = get_config('mod_automultiplechoice', 'instructions');
    if (!$raw) {
        return array();
    }
    $splitted = preg_split('/\n-{3,}\s*\n/s', $raw, -1, PREG_SPLIT_NO_EMPTY);
    $assoc = array();
    foreach ($splitted as $split) {
        $lines = explode("\n", $split, 2);
        $title = trim($lines[0]);
        if ($title) {
            $assoc[$lines[1]] = $title;
        }
    }
    $assoc[''] = 'vide';
    return $assoc;
}

/**
 * Return a user record.
 *
 * @todo Optimize? One query per user is doable, the difficulty is to sort results according to prefix order.
 *
 * @global \moodle_database $DB
 * @param string $idn
 * @return object Record from the user table.
 */
function getStudentByIdNumber($idn) {
    global $DB;
    $prefixestxt = get_config('mod_automultiplechoice', 'idnumberprefixes');
    $prefixes = array_filter(array_map('trim', preg_split('/\R/', $prefixestxt)));
    $prefixes[] = "";
    foreach ($prefixes as $p) {
        $user = $DB->get_record('user', array('idnumber' => $p . $idn, 'confirmed' => 1, 'deleted' => 0));
        if ($user) {
            return $user;
        }
    }
    return null;
}
/**
 * Return a user record.
 *
 *
 * @global \moodle_database $DB
 * @param context if
 * @return int count student user.
 */
function has_students($context) {
    global $DB;
    list($relatedctxsql, $params) = $DB->get_in_or_equal($context->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'relatedctx');
    $countsql = "SELECT COUNT(DISTINCT(ra.userid))
        FROM {role_assignments} ra
        JOIN {user} u ON u.id = ra.userid
        WHERE ra.contextid  $relatedctxsql AND ra.roleid = 5";
    $totalcount = $DB->count_records_sql($countsql,$params);
    return $totalcount;

}


/**
 * Gets all the users assigned this role in this context or higher
 *
 * Note that moodle is based on capabilities and it is usually better
 * to check permissions than to check role ids as the capabilities
 * system is more flexible. If you really need, you can to use this
 * function but consider has_capability() as a possible substitute.
 *
 * The caller function is responsible for including all the
 * $sort fields in $fields param.
 *
 * If $roleid is an array or is empty (all roles) you need to set $fields
 * (and $sort by extension) params according to it, as the first field
 * returned by the database should be unique (ra.id is the best candidate).
 *
 * @param int $roleid (can also be an array of ints!)
 * @param context $context
 * @param bool $parent if true, get list of users assigned in higher context too
 * @param string $fields fields from user (u.) , role assignment (ra) or role (r.)
 * @param string $sort sort from user (u.) , role assignment (ra.) or role (r.).
 *      null => use default sort from users_order_by_sql.
 * @param bool $all true means all, false means limit to enrolled users
 * @param string $group defaults to ''
 * @param mixed $limitfrom defaults to ''
 * @param mixed $limitnum defaults to ''
 * @param string $extrawheretest defaults to ''
 * @param array $whereorsortparams any paramter values used by $sort or $extrawheretest.
 * @return array
 */
function amc_get_student_users($cm, $parent = false, $group = '', $exclude=NULL) {
    global $DB;
    $codelength = get_config('mod_automultiplechoice', 'amccodelength');
    $allnames = get_all_user_name_fields(true, 'u');
    $fields = 'u.id, u.confirmed, u.username, '. $allnames . ', ' .'RIGHT(u.idnumber,'.$codelength.') as idnumber';
    $context = context_module::instance($cm->id);
    $roleid =array_keys( get_archetype_roles('student'));
    $parentcontexts = '';
    if ($parent) {
        $parentcontexts = substr($context->path, 1); // kill leading slash
        $parentcontexts = str_replace('/', ',', $parentcontexts);
        if ($parentcontexts !== '') {
            $parentcontexts = ' OR ra.contextid IN ('.$parentcontexts.' )';
        }
    }


     if ($roleid) {
        list($rids, $params) = $DB->get_in_or_equal($roleid, SQL_PARAMS_NAMED, 'r');
        $roleselect = "AND ra.roleid $rids";
    } else {
        $params = array();
        $roleselect = '';
    }
    if ($exclude) {
        list($idnumbers, $excludeparams) = $DB->get_in_or_equal($exclude, SQL_PARAMS_NAMED, 'excl',false);
        $idnumberselect = " AND RIGHT(u.idnumber,".$codelength.") $idnumbers ";
        $params = array_merge($params, $excludeparams);
    } else {
        $excludeparams = array();
        $idnumberselect = '';
    }

    if ($coursecontext = $context->get_course_context(false)) {
        $params['coursecontext'] = $coursecontext->id;
    } else {
        $params['coursecontext'] = 0;
    }

    if ($group) {
        $groupjoin   = "JOIN {groups_members} gm ON gm.userid = u.id";
        $groupselect = " AND gm.groupid = :groupid ";
        $params['groupid'] = $group;
    } else {
        $groupjoin   = '';
        $groupselect = '';
    }

    $params['contextid'] = $context->id;
        list($sort, $sortparams) = users_order_by_sql('u');
        $params = array_merge($params, $sortparams);
        $ejoin = "JOIN {user_enrolments} ue ON ue.userid = u.id
                  JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :ecourseid)";
        $params['ecourseid'] = $coursecontext->instanceid;

    $sql = "SELECT DISTINCT $fields, ra.roleid
              FROM {role_assignments} ra
              JOIN {user} u ON u.id = ra.userid
               $idnumberselect
              JOIN {role} r ON ra.roleid = r.id
            $ejoin
         LEFT JOIN {role_names} rn ON (rn.contextid = :coursecontext AND rn.roleid = r.id)
        $groupjoin
             WHERE (ra.contextid = :contextid $parentcontexts)
                   $roleselect
                   $groupselect
          ORDER BY $sort";                  // join now so that we can just use fullname() later

    $availableusers = $DB->get_records_sql($sql, $params);
    $modinfo = get_fast_modinfo($cm->course);
    $info = new \core_availability\info_module($modinfo->get_cm($cm->id));
    $availableusers = $info->filter_user_list($availableusers);
    return $availableusers;
}

/**
 * Returns a select HTML element
 * @param  [type]  $url        [description]
 * @param  [type]  $cm         [description]
 * @param  [type]  $idnumber   [description]
 * @param  [type]  $groupid    [description]
 * @param  [type]  $exclude    [description]
 * @param  boolean $includeall [description]
 * @return [type]              [description]
 */
function amc_get_students_select($url, $cm, $idnumber, $groupid, $exclude = null, $includeall = false) {
    global $USER, $CFG;

    $codelength = get_config('mod_automultiplechoice', 'amccodelength');
    if (is_null($idnumber)) {
        $idnumber = $USER->idnumber;
    }
    if (count($idnumber)>$codelength){
        $idnumber = substr($idnumber,-1*$codelength);//by security
    }
    $menu = array(); // Will be a list of userid => user name
    if ($exclude and $idnumber) {
        $exclude= array_diff($exclude, array($idnumber));
    }
    $users = amc_get_student_users($cm, true, $groupid, $exclude);
    $label = get_string('selectuser', 'automultiplechoice');
    if ($includeall) {
        $menu[0] = get_string('allusers', 'automultiplechoice');
        $label = get_string('selectalloroneuser', 'automultiplechoice');
    }
    foreach ($users as $user) {
        //$user = $userdata->user;
        $userfullname = fullname($user);
        $menu[$user->idnumber] = $userfullname;

    }

    $select = new single_select($url, 'idnumber', $menu, $idnumber);
    $select->label = $label;
    $select->formid = 'choosestudent';
    return $select;
}


/**
 * Get course module users and return the result as an array usable in an HTML select element
 * @param  stdClass $cm       the course module (ie a automultiplechoice instance)
 * @param  string $idnumber a user id
 * @param  string $groupid  a group id
 * @param  Array $exclude  users to exclude
 * @return Array           an array usable in an HTML select element
 */
function amc_get_users_for_select_element($cm, $idnumber, $groupid, $exclude = null) {
    global $USER, $CFG;

    $codelength = get_config('mod_automultiplechoice', 'amccodelength');
    if (is_null($idnumber)) {
        $idnumber = $USER->idnumber;
    }
    if (count($idnumber)>$codelength) {
        $idnumber = substr($idnumber, -1*$codelength);//by security
    }

    if ($exclude && $idnumber) {
        $exclude = array_diff($exclude, array($idnumber));
    }
    $users = amc_get_student_users($cm, true, $groupid, $exclude);
    $label = get_string('selectuser', 'automultiplechoice');
    $menu = [];
    foreach ($users as $user) {
        $userfullname = fullname($user);
        // In case of prefixed student number.
        $usernumber = substr($user->idnumber, -1*$codelength);
        $menu[] = [
          'value' => $user->idnumber,
          'label' => $userfullname,
          'selected' => intval($usernumber) === intval($idnumber)
        ];
    }

    return $menu;
}

/**
 * Returns a HTML button.
 *
 * @global type $OUTPUT
 * @param integer $id
 * @return string
 */
function button_back_to_activity($id) {
    global $OUTPUT;
    $url = new moodle_url('/mod/automultiplechoice/view.php', array('a' => $id));
    return '<div class="back-to-activity">'
            . $OUTPUT->single_button($url, 'Retour au questionnaire', 'get')
            . '</div>';
}

/*
function displayLockButton(\mod_automultiplechoice\local\models\quiz $quiz) {
    global $OUTPUT;
    if (empty($quiz->errors)) {
        if ($quiz->isLocked()) {
            echo $OUTPUT->single_button(
                    new moodle_url('/mod/automultiplechoice/documents.php', array('a' => $quiz->id, 'unlock' => 1)),
                    'Déverrouiller (permettre les modifications du questionnaire)', 'post'
            );
        } else {
            echo $OUTPUT->single_button(
                        new moodle_url('/mod/automultiplechoice/documents.php', array('a' => $quiz->id, 'lock' => 1)),
                        'Préparer les documents à imprimer et verrouiller le questionnaire', 'post'
                );
        }
    } else {
        echo 'Préparer et verrouiller. ' . get_string('functiondisabled');
    }
}

function displayGradeInfo(\mod_automultiplechoice\local\amc\process $process) {
    $gradetime = $process->lastlog('note');
    if ($gradetime) {
        echo "<div>Correction des copies déjà effectuée le " . $process::isoDate($gradetime) . "</div>\n";
    }
}
*/


function backup_source($file) {
     copy ($file,$file.'.orig');
}

function restore_source($file) {
    copy ($file,substr($file, -5));
}

function get_code($name) {
    preg_match('/name-(?P<student>[0-9]+)[:-](?P<copy>[0-9]+).jpg$/', $name, $res);
    return $res['student'].'_'.$res['copy'];
}


function get_list_row($list) {
    preg_match('/(?P<student>[0-9]+):(?P<copy>[0-9]+)\s*(?P<idnumber>[0-9]+)\s*\((?P<status>.*)\)/', $list, $res);
    return $res;
}
