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
global $DB;

/**
 * Return the list of questions available to a given user.
 *
 * @param integer $userid
 * @return array assoc array with fields: id, coursename, categoryname, title
 */
function autocomplete_list_questions($userid) {
    global $DB;

    /**
     * TODO
     *
     * - which capabilities :
     *     * moodle/question:viewall?
     *     * moodle/question:viewmine?
     *     * moodle/question:usemine?
     *     * moodle/question:useall?
     *
     * - find the contexts where the user has one of these capabilities
     *
     * - find the questions that are in a q_category under one of these contexts.
     */

    return array();
}