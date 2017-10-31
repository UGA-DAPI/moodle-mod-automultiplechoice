<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This script lists all the instances of questionnaire in a particular course
 *
 * @package    mod
 * @subpackage questionnaire
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


 require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
 require_once(dirname(__FILE__).'/lib.php');

 $output = $PAGE->get_renderer('mod_automultiplechoice');


 $id = required_param('id', PARAM_INT);
 $course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
 require_course_login($course);

 add_to_log($course->id, 'automultiplechoice', 'view all', 'index.php?id='.$course->id, '');

 $coursecontext = context_course::instance($course->id);

 $PAGE->set_url('/mod/automultiplechoice/index.php', array('id' => $id));
 $PAGE->set_title(format_string($course->fullname));
 $PAGE->set_heading(format_string($course->fullname));
 $PAGE->set_context($coursecontext);

 echo $OUTPUT->header();



// Configure table for displaying the list of instances.
$headings = array(get_string('name'));
$align = array('left');





$content = array();

$indexpage = new \mod_automultiplechoice\output\indexpage($headings, $content);
echo $OUTPUT->render_indexpage($indexpage);

//echo $output->render_index($headings, $align, $content);

// Finish the page.
echo $output->footer();
