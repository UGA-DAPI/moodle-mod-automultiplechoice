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

/* @var $DB \moodle_database */

global $COURSE, $DB;

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
    global $COURSE, $DB;

    $course_context = context_course::instance($course->id);

    if (!has_capability('moodle/question:useall', $course_context, $user)) {
        return array();
    }

    $sql = "SELECT q.id, qc.name AS categoryname, q.name AS title, q.timemodified "
            . "FROM {question} q JOIN {question_categories} qc ON q.category = qc.id "
            . "WHERE qc.contextid = " . $course_context->id
            . " ORDER BY qc.sortorder, q.name";
    $records = $DB->get_records_sql($sql);
    return $records;
}