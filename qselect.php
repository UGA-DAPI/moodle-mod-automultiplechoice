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

/**
 * @todo l10n table header
 * @todo l10n jQuery datatables
 */

/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

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
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->js(new moodle_url('assets/dataTables/jquery.dataTables.min.js'));
$PAGE->requires->css(new moodle_url('assets/dataTables/css/jquery.dataTables.css'));

$PAGE->requires->js(new moodle_url('assets/qselect.js'));
$PAGE->requires->css(new moodle_url('assets/amc.css'));


$questions = automultiplechoice_list_questions($USER, $COURSE);

echo $OUTPUT->header();

echo $OUTPUT->box_start();
echo $OUTPUT->heading("Sélection des questions");
?>
<table id="questions-list">
    <thead>
        <tr>
            <th>Question Category</th>
            <th>Title</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($questions as $q) {
            echo '<tr id="q-' . $q->id . '">'
                . '<td>' . format_string($q->categoryname) . '</td>'
                . '<td class="qtitle">' . format_string($q->title) . '</td>'
                . '<td>' . date('Y-m-d', $q->timemodified) . '</td>'
                . '<td><button type="button" data-qid="' . $q->id . '">+</button></td>'
                . '</tr>';
        }
        ?>
    </tbody>
</table>
<div class="datatable-end"></div>
<?php
echo $OUTPUT->box_end();

echo $OUTPUT->box_start();
echo $OUTPUT->heading("Questions choisies");
?>
<p>
    Ces questions peuvent être triées en les déplaçant à la souris.
</p>
<form name="questions-form" action="validate.php" method="post">
<p>
    <button type="submit">Enregistrer la sélection</button>
</p>
<ul id="questions-selected">
    <li style="display: none;" class="ui-state-default">
        <span class="ui-icon ui-icon-arrowthick-2-n-s">XXX</span>
        <label></label>
        <input name="question[id][]" value="" type="hidden" disabled="disabled" />
        <button type="button">X</button>
        <label class="qscore">
            Score :
            <input name="question[score][]" value="" type="text" disabled="disabled" />
        </label>
    </li>
</ul>
</form>

<?php
echo $OUTPUT->box_end();

echo $OUTPUT->footer();
