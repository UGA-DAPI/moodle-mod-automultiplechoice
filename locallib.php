<?php

/**
 * Internal library of functions for module automultiplechoice
 *
 * All the automultiplechoice specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_automultiplechoice
 * @copyright  2013 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $DB;
/* @var $DB \moodle_database */

/**
 * Return the list of questions available to a given user.
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
    global $DB;

    $course_context = context_course::instance($course->id);

    if (!has_capability('moodle/question:useall', $course_context, $user)) {
        return array();
    }

    $sql = "SELECT q.id, qc.name AS categoryname, q.name AS title, q.timemodified "
            . "FROM {question} q JOIN {question_categories} qc ON q.category = qc.id "
            . " JOIN {question_multichoice} qm ON qm.question=q.id "
            . "WHERE qc.contextid = " . $course_context->id
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
    $assoc = array('' => '…');
    foreach ($splitted as $split) {
        $lines = explode("\n", $split, 2);
        $title = trim($lines[0]);
        if ($title) {
            $assoc[$lines[1]] = $title;
        }
    }
    return $assoc;
}

/**
 * Return a user record.
 *
 * @global \moodle_database $DB
 * @param string $idn
 * @return object Record from the user table.
 */
function getStudentByIdNumber($idn) {
    global $DB;
    return $DB->get_record('user', array('idnumber' => $idn, 'confirmed' => 1, 'deleted' => 0));
}
