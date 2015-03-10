<?php

/**
 * Library of interface functions and constants for module automultiplechoice
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the automultiplechoice specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_automultiplechoice
 * @copyright  2013 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/models/Quizz.php';
require_once __DIR__ . '/models/ScoringSystem.php';
require_once __DIR__ . '/models/AmcProcess.php';
require_once __DIR__ . '/models/AmcProcessGrade.php';

/* @var $DB moodle_database */

/** example constant */
//define('NEWMODULE_ULTIMATE_ANSWER', 42);

////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function automultiplechoice_supports($feature) 
{
    switch($feature) {
    
    case FEATURE_GRADE_OUTCOMES:
    case FEATURE_MOD_INTRO:
        return false;
    
    case FEATURE_BACKUP_MOODLE2:
    case FEATURE_GRADE_HAS_GRADE:
        return true;

    default:
        return null;
            
    }
}

/**
 * Saves a new instance of the automultiplechoice into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $automultiplechoice An object from the form in mod_form.php
 * @param mod_automultiplechoice_mod_form $mform
 * @return int The id of the newly inserted automultiplechoice record
 */
function automultiplechoice_add_instance(stdClass $automultiplechoice, mod_automultiplechoice_mod_form $mform = null) {
    global $DB, $USER;

    $automultiplechoice->timecreated = $_SERVER['REQUEST_TIME'];
    $automultiplechoice->timemodified = $_SERVER['REQUEST_TIME'];
    $automultiplechoice->author = $USER->id;
    $automultiplechoice->questions = "";

    $params = \mod\automultiplechoice\AmcParams::fromForm($automultiplechoice->amc);
    unset($automultiplechoice->amc);
    $automultiplechoice->amcparams = $params->toJson();
    $quizz = new mod\automultiplechoice\Quizz();
    $quizz->readFromRecord($automultiplechoice);
    if ($quizz->save()) {
        return $quizz->id;
    } else {
        throw new Exception("ERROR");
    }
}

/**
 * Updates an instance of the automultiplechoice in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $automultiplechoice An object from the form in mod_form.php
 * @param mod_automultiplechoice_mod_form $mform
 * @return boolean Success/Fail
 */
function automultiplechoice_update_instance(stdClass $automultiplechoice, mod_automultiplechoice_mod_form $mform = null) {
    global $DB;

    $quizz = mod\automultiplechoice\Quizz::findById($automultiplechoice->instance);
    $quizz->readFromForm($automultiplechoice);
    $quizz->timemodified = $_SERVER['REQUEST_TIME'];
    return $quizz->save();

    $automultiplechoice->timemodified = $_SERVER['REQUEST_TIME'];
    $automultiplechoice->id = $automultiplechoice->instance;

    $params = \mod\automultiplechoice\AmcParams::fromForm($automultiplechoice->amc);
    unset($automultiplechoice->amc);
    $params->scoringset = $quizz->amcparams->scoringset;
    $automultiplechoice->amcparams = $params->toJson();
    if (isset($automultiplechoice->questions)) {
        unset($automultiplechoice->questions);
    }

    return $DB->update_record('automultiplechoice', $automultiplechoice);
}

/**
 * Removes an instance of the automultiplechoice from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function automultiplechoice_delete_instance($id) {
    global $DB;

    $automultiplechoice = $DB->get_record('automultiplechoice', array('id' => $id));
    if (! $automultiplechoice) {
        return false;
    }

    # Delete any dependent records here #
    $DB->delete_records('automultiplechoice', array('id' => $automultiplechoice->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function automultiplechoice_user_outline($course, $user, $mod, $automultiplechoice) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $automultiplechoice the module instance record
 * @return void, is supposed to echp directly
 */
function automultiplechoice_user_complete($course, $user, $mod, $automultiplechoice) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in automultiplechoice activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function automultiplechoice_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link automultiplechoice_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function automultiplechoice_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see automultiplechoice_get_recent_mod_activity()}

 * @return void
 */
function automultiplechoice_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function automultiplechoice_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function automultiplechoice_get_extra_capabilities() {
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Is a given scale used by the instance of automultiplechoice?
 *
 * This function returns if a scale is being used by one automultiplechoice
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $automultiplechoiceid ID of an instance of this module
 * @return bool true if the scale is used by the given automultiplechoice instance
 */
function automultiplechoice_scale_used($automultiplechoiceid, $scaleid) {
    return false;
}

/**
 * Checks if scale is being used by any instance of automultiplechoice.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any automultiplechoice instance
 */
function automultiplechoice_scale_used_anywhere($scaleid) {
    return false;
}

/**
 * Creates or updates grade item for the give automultiplechoice instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $automultiplechoice instance object with extra cmidnumber and modname property
 * @param mixed optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return void
 */
function automultiplechoice_grade_item_update(stdClass $automultiplechoice, $grades=null) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($automultiplechoice->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    $item['grademax']  = $automultiplechoice->score;
    $item['grademin']  = 0;

    grade_update('mod/automultiplechoice', $automultiplechoice->course, 'mod', 'automultiplechoice',
            $automultiplechoice->id, 0, $grades, $item);
}

/**
 * Update automultiplechoice grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $automultiplechoice instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function automultiplechoice_update_grades(stdClass $automultiplechoice, $userid = 0) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');
    require_once __DIR__ . '/models/AmcProcessGrade.php';

    $quizz = \mod\automultiplechoice\Quizz::buildFromRecord($automultiplechoice);
    $process = new \mod\automultiplechoice\AmcProcessGrade($quizz);
    $grades = $process->getMarks();
    if ($userid) {
        $grades = isset($grades[$userid]) ? $grades[$userid] : null;
    }

    automultiplechoice_grade_item_update($automultiplechoice, $grades);
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function automultiplechoice_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for automultiplechoice file areas
 *
 * @package mod_automultiplechoice
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function automultiplechoice_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the automultiplechoice file areas
 *
 * @package mod_automultiplechoice
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the automultiplechoice's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function automultiplechoice_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $USER;
    require_once __DIR__ . '/models/AmcProcessExport.php';
    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    $filename = array_pop($args);
    $quizz = \mod\automultiplechoice\Quizz::findById($cm->instance);
    $process = new \mod\automultiplechoice\AmcProcessExport($quizz);

    // First, the student use case: to download anotated answer sheet correction-0123456789-Surname.pdf
    // and corrigé
    if (preg_match('/^cr-[0-9]*\.pdf$/', $filename)) {
        $target = $process->workdir . '/cr/corrections/pdf/' . $filename;
        if (!file_exists($target)) {
            send_file_not_found();
        }
        if (has_capability('mod/automultiplechoice:update', $context)
            || (  $quizz->studentaccess && $USER->id.".pdf" === basename($filename))
            ) {
            send_file($target, $filename, 10, 0, false, false, 'application/pdf') ;
            return true;
        }
    }
    if (preg_match('/^page-[0-9]*-[0-9]*-[0-9]*\.jpg$/', $filename)) {
        $target = $process->workdir . '/cr/corrections/jpg/' . $filename;
        if (!file_exists($target)) {
            send_file_not_found();
        }
        if (has_capability('mod/automultiplechoice:update', $context)
            || (  $quizz->studentaccess && $USER->id.".jpg" === basename($filename))
            ) {
            send_file($target, $filename, 10, 0, false, false, 'application/jpg') ;
            return true;
        }
    }
    if (preg_match('/^corrige-.*\.pdf$/', $filename)) {
        if (   $quizz->corrigeaccess && file_exists("cr-".$USER->id.".pdf") )
            {
            send_file($process->workdir .'/'. $filename, $filename, 10, 0, false, false, 'application/pdf') ;
            return true;
         }
     }

    // Then teacher only use cases
    require_capability('mod/automultiplechoice:update', $context);

    // whitelist security
    if (preg_match('/^(sujet|corrige|catalog)-.*\.pdf$/', $filename)) {
        send_file($process->workdir .'/'. $filename, $filename, 10, 0, false, false, 'application/pdf') ;
        return true;
     } else if (preg_match('/^failed-.*\.pdf$/', $filename)) {
        $ret = $process->makeFailedPdf();     
        if ($ret){
            send_file($process->workdir . '/' . $filename, $filename, 10, 0, false, false, 'application/pdf') ;
        }
        return $ret;
     } else if (preg_match('/^sujets-.*\.zip$/', $filename)) {
        send_file($process->workdir . '/' . $filename, $filename, 10, 0, false, false, 'application/zip') ;
        return true;
     } else if (preg_match('/^corrections-.*\.pdf$/', $filename)) {
        send_file($process->workdir . '/' . $filename, $filename, 10, 0, false, false, 'application/pdf') ;
        return true;
     } else if (preg_match('/^cr-[0-9]*\.pdf$/', $filename)) {
        send_file($process->workdir . '/cr/corrections/pdf/' . $filename, $filename, 10, 0, false, false, 'application/pdf') ;
        return true;
    } else if (preg_match('/grades\.csv$/', $filename)) {
        $ret = $process->amcExport('csv');
        if ($ret){
            send_file($process->workdir . '/exports/' . $filename, $filename, 10, 0, false, false, 'text/csv') ;
        }
        return $ret;
     } else if (preg_match('/apogee\.csv$/', $filename)) {
        $ret = $process->writeFileApogeeCsv();     
        if ($ret){
            send_file($process->workdir . '/exports/' . $filename, $filename, 10, 0, false, false, 'text/csv') ;
        }
        return $ret;
    } else if (preg_match('/\.ods$/', $filename)) {
        $ret = $process->amcExport('ods');     
        if ($ret){
            send_file($process->workdir . '/exports/' . $filename, $filename, 10, 0, false, false, 'application/vnd.oasis.opendocument.spreadsheet') ;
        }
        return $ret;
    } else if (preg_match('/\.ppm$/', $filename)) {
        send_file($process->workdir . '/scans/' . $filename, $filename, 10, 0, false, false,'image/x-portable-pixmap') ;
        return true;
    } else if (preg_match('/\.pbm$/', $filename)) {
        send_file($process->workdir . '/scans/' . $filename, $filename, 10, 0, false, false,'image/x-portable-bitmap') ;
        return true;
    } else if (preg_match('/\.tif[f]*$/', $filename)) {
        send_file($process->workdir . '/scans/' . $filename, $filename, 10, 0, false, false,'image/tiff') ;
        return true;
    }else if (preg_match('/^name-[0-9]*:[0-9]*\.jpg$/', $filename)) {
        send_file($process->workdir . '/cr/' . $filename, $filename, 10, 0, false, false, 'application/jpg') ;
        return true;
        
    }
    send_file_not_found();
}

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding automultiplechoice nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the automultiplechoice module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function automultiplechoice_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the automultiplechoice settings
 *
 * This function is called when the context for the page is a automultiplechoice module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $automultiplechoicenode {@link navigation_node}
 */
function automultiplechoice_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $automultiplechoicenode=null) {
}

function automultiplechoice_questions_in_use($questionids) {
    global $DB;
    $records = $DB->get_recordset('automultiplechoice');
    foreach ($records as $record) {
        $quizz = \mod\automultiplechoice\Quizz::buildFromRecord($record);
        if ($quizz->questions->contains($questionids)) {
            return true;
        }
    }
    return false;
}

class mod_automultiplechoice_users_iterator {

    /**
     * The couse whose users we are interested in
     */
    protected $course;

    

    /**
     * The group ID we are interested in. 0 means all groups.
     */
    protected $groupid;

    /**
     * A recordset of graded users
     */
    protected $users_rs;

    
    /**
     * Should users whose enrolment has been suspended be ignored?
     */
    protected $onlyactive = false;

    

    /**
     * List of suspended users in course. This includes users whose enrolment status is suspended
     * or enrolment has expired or not started.
     */
    protected $suspendedusers = array();

    /**
     * Constructor
     *
     * @param object $course A course object
     * @param int    $groupid iterate only group users if present
    
     */
    public function __construct($course, $groupid=0) {
        $this->course      = $course;
        $this->grade_items = $grade_items;
        $this->groupid     = $groupid;
        $this->sortfield1  = $sortfield1;
        $this->sortorder1  = $sortorder1;
        $this->sortfield2  = $sortfield2;
        $this->sortorder2  = $sortorder2;

        $this->gradestack  = array();
    }

    /**
     * Initialise the iterator
     *
     * @return boolean success
     */
    public function init() {
        global $CFG, $DB;

        $this->close();

        export_verify_grades($this->course->id);
        $course_item = grade_item::fetch_course_item($this->course->id);
        if ($course_item->needsupdate) {
            // Can not calculate all final grades - sorry.
            return false;
        }

        $coursecontext = context_course::instance($this->course->id);

        list($relatedctxsql, $relatedctxparams) = $DB->get_in_or_equal($coursecontext->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'relatedctx');
        list($gradebookroles_sql, $params) = $DB->get_in_or_equal(explode(',', $CFG->gradebookroles), SQL_PARAMS_NAMED, 'grbr');
        list($enrolledsql, $enrolledparams) = get_enrolled_sql($coursecontext, '', 0, $this->onlyactive);

        $params = array_merge($params, $enrolledparams, $relatedctxparams);

        if ($this->groupid) {
            $groupsql = "INNER JOIN {groups_members} gm ON gm.userid = u.id";
            $groupwheresql = "AND gm.groupid = :groupid";
            // $params contents: gradebookroles
            $params['groupid'] = $this->groupid;
        } else {
            $groupsql = "";
            $groupwheresql = "";
        }

        if (empty($this->sortfield1)) {
            // We must do some sorting even if not specified.
            $ofields = ", u.id AS usrt";
            $order   = "usrt ASC";

        } else {
            $ofields = ", u.$this->sortfield1 AS usrt1";
            $order   = "usrt1 $this->sortorder1";
            if (!empty($this->sortfield2)) {
                $ofields .= ", u.$this->sortfield2 AS usrt2";
                $order   .= ", usrt2 $this->sortorder2";
            }
            if ($this->sortfield1 != 'id' and $this->sortfield2 != 'id') {
                // User order MUST be the same in both queries,
                // must include the only unique user->id if not already present.
                $ofields .= ", u.id AS usrt";
                $order   .= ", usrt ASC";
            }
        }

        $userfields = 'u.*';
        $customfieldssql = '';
        if ($this->allowusercustomfields && !empty($CFG->grade_export_customprofilefields)) {
            $customfieldscount = 0;
            $customfieldsarray = grade_helper::get_user_profile_fields($this->course->id, $this->allowusercustomfields);
            foreach ($customfieldsarray as $field) {
                if (!empty($field->customid)) {
                    $customfieldssql .= "
                            LEFT JOIN (SELECT * FROM {user_info_data}
                                WHERE fieldid = :cf$customfieldscount) cf$customfieldscount
                            ON u.id = cf$customfieldscount.userid";
                    $userfields .= ", cf$customfieldscount.data AS customfield_{$field->shortname}";
                    $params['cf'.$customfieldscount] = $field->customid;
                    $customfieldscount++;
                }
            }
        }

        $users_sql = "SELECT $userfields $ofields
                        FROM {user} u
                        JOIN ($enrolledsql) je ON je.id = u.id
                             $groupsql $customfieldssql
                        JOIN (
                                  SELECT DISTINCT ra.userid
                                    FROM {role_assignments} ra
                                   WHERE ra.roleid $gradebookroles_sql
                                     AND ra.contextid $relatedctxsql
                             ) rainner ON rainner.userid = u.id
                         WHERE u.deleted = 0
                             $groupwheresql
                    ORDER BY $order";
        $this->users_rs = $DB->get_recordset_sql($users_sql, $params);

        if (!$this->onlyactive) {
            $context = context_course::instance($this->course->id);
            $this->suspendedusers = get_suspended_userids($context);
        } else {
            $this->suspendedusers = array();
        }

        if (!empty($this->grade_items)) {
            $itemids = array_keys($this->grade_items);
            list($itemidsql, $grades_params) = $DB->get_in_or_equal($itemids, SQL_PARAMS_NAMED, 'items');
            $params = array_merge($params, $grades_params);

            $grades_sql = "SELECT g.* $ofields
                             FROM {grade_grades} g
                             JOIN {user} u ON g.userid = u.id
                             JOIN ($enrolledsql) je ON je.id = u.id
                                  $groupsql
                             JOIN (
                                      SELECT DISTINCT ra.userid
                                        FROM {role_assignments} ra
                                       WHERE ra.roleid $gradebookroles_sql
                                         AND ra.contextid $relatedctxsql
                                  ) rainner ON rainner.userid = u.id
                              WHERE u.deleted = 0
                              AND g.itemid $itemidsql
                              $groupwheresql
                         ORDER BY $order, g.itemid ASC";
            $this->grades_rs = $DB->get_recordset_sql($grades_sql, $params);
        } else {
            $this->grades_rs = false;
        }

        return true;
    }

    /**
     * Returns information about the next user
     * @return mixed array of user info, all grades and feedback or null when no more users found
     */
    public function next_user() {
        if (!$this->users_rs) {
            return false; // no users present
        }

        if (!$this->users_rs->valid()) {
            if ($current = $this->_pop()) {
                // this is not good - user or grades updated between the two reads above :-(
            }

            return false; // no more users
        } else {
            $user = $this->users_rs->current();
            $this->users_rs->next();
        }

        // find grades of this user
        $grade_records = array();
        while (true) {
            if (!$current = $this->_pop()) {
                break; // no more grades
            }

            if (empty($current->userid)) {
                break;
            }

            if ($current->userid != $user->id) {
                // grade of the next user, we have all for this user
                $this->_push($current);
                break;
            }

            $grade_records[$current->itemid] = $current;
        }

        $grades = array();
        $feedbacks = array();

        if (!empty($this->grade_items)) {
            foreach ($this->grade_items as $grade_item) {
                if (!isset($feedbacks[$grade_item->id])) {
                    $feedbacks[$grade_item->id] = new stdClass();
                }
                if (array_key_exists($grade_item->id, $grade_records)) {
                    $feedbacks[$grade_item->id]->feedback       = $grade_records[$grade_item->id]->feedback;
                    $feedbacks[$grade_item->id]->feedbackformat = $grade_records[$grade_item->id]->feedbackformat;
                    unset($grade_records[$grade_item->id]->feedback);
                    unset($grade_records[$grade_item->id]->feedbackformat);
                    $grades[$grade_item->id] = new grade_grade($grade_records[$grade_item->id], false);
                } else {
                    $feedbacks[$grade_item->id]->feedback       = '';
                    $feedbacks[$grade_item->id]->feedbackformat = FORMAT_MOODLE;
                    $grades[$grade_item->id] =
                        new grade_grade(array('userid'=>$user->id, 'itemid'=>$grade_item->id), false);
                }
            }
        }

        // Set user suspended status.
        $user->suspendedenrolment = isset($this->suspendedusers[$user->id]);
        $result = new stdClass();
        $result->user      = $user;
        $result->grades    = $grades;
        $result->feedbacks = $feedbacks;
        return $result;
    }

    /**
     * Close the iterator, do not forget to call this function
     */
    public function close() {
        if ($this->users_rs) {
            $this->users_rs->close();
            $this->users_rs = null;
        }
        if ($this->grades_rs) {
            $this->grades_rs->close();
            $this->grades_rs = null;
        }
        $this->gradestack = array();
    }

    /**
     * Should all enrolled users be exported or just those with an active enrolment?
     *
     * @param bool $onlyactive True to limit the export to users with an active enrolment
     */
    public function require_active_enrolment($onlyactive = true) {
        if (!empty($this->users_rs)) {
            debugging('Calling require_active_enrolment() has no effect unless you call init() again', DEBUG_DEVELOPER);
        }
        $this->onlyactive  = $onlyactive;
    }

    /**
     * Allow custom fields to be included
     *
     * @param bool $allow Whether to allow custom fields or not
     * @return void
     */
    public function allow_user_custom_fields($allow = true) {
        if ($allow) {
            $this->allowusercustomfields = true;
        } else {
            $this->allowusercustomfields = false;
        }
    }

    /**
     * Add a grade_grade instance to the grade stack
     *
     * @param grade_grade $grade Grade object
     *
     * @return void
     */
    private function _push($grade) {
        array_push($this->gradestack, $grade);
    }


    /**
     * Remove a grade_grade instance from the grade stack
     *
     * @return grade_grade current grade object
     */
    private function _pop() {
        global $DB;
        if (empty($this->gradestack)) {
            if (empty($this->grades_rs) || !$this->grades_rs->valid()) {
                return null; // no grades present
            }

            $current = $this->grades_rs->current();

            $this->grades_rs->next();

            return $current;
        } else {
            return array_pop($this->gradestack);
        }
    }
}
