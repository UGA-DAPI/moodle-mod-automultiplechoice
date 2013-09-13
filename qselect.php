<?php

/**
 * Selects questions.
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_automultiplechoice
 * @copyright  2013 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB, $OUTPUT, $PAGE;

require_once(dirname(dirname(__DIR__)) . '/config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/locallib.php');

$a  = required_param('a', PARAM_INT);  // instance ID

$automultiplechoice  = $DB->get_record('automultiplechoice', array('id' => $a), '*', MUST_EXIST);
$course     = $DB->get_record('course', array('id' => $automultiplechoice->course), '*', MUST_EXIST);
$cm         = get_coursemodule_from_instance('automultiplechoice', $automultiplechoice->id, $course->id, false, MUST_EXIST);

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

// add_to_log($course->id, 'automultiplechoice', 'view', "qselect.php?id={$cm->id}", $automultiplechoice->name, $cm->id);

$PAGE->set_url('/mod/automultiplechoice/qselect.php', array('a' => $automultiplechoice->id));
$PAGE->set_title(format_string($automultiplechoice->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_cacheable(false);

$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('js/dataTables/jquery.dataTables.min.js'));

$questions = autocomplete_list_questions($USER->id);

echo $OUTPUT->header();

?>
<table id="questionslist">
    <thead>
        <tr>
            <th>Course / Course Category</th>
            <th>Question Category</th>
            <th>Title</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($questions as $q) {
            echo '<tr>'
                . '<td></td>'
                . '<td></td>'
                . '<td></td>'
                . '</tr>';
        }
        ?>
    </tbody>
</table>

<?php

$PAGE->requires->js_init_code('
$(document).ready(function() {
    $("#questionslist").dataTable();
} );');

echo $OUTPUT->footer();
