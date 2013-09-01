<?php

/**
 * Prints a particular instance of automultiplechoice
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_automultiplechoice
 * @copyright  2013 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace automultiplechoice with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // automultiplechoice instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('automultiplechoice', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $automultiplechoice  = $DB->get_record('automultiplechoice', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $automultiplechoice  = $DB->get_record('automultiplechoice', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $automultiplechoice->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('automultiplechoice', $automultiplechoice->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'automultiplechoice', 'view', "view.php?id={$cm->id}", $automultiplechoice->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/automultiplechoice/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($automultiplechoice->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('automultiplechoice-'.$somevar);

// Output starts here
echo $OUTPUT->header();

if ($automultiplechoice->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('automultiplechoice', $automultiplechoice, $cm->id), 'generalbox mod_introbox', 'automultiplechoiceintro');
}

// Replace the following lines with you own code
echo $OUTPUT->heading('Yay! It works!');

// Finish the page
echo $OUTPUT->footer();
