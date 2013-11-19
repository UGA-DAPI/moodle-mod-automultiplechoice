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

/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

global $DB, $OUTPUT, $PAGE;

require_once(dirname(dirname(__DIR__)) . '/config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/locallib.php');
require_once __DIR__ . '/models/Quizz.php';

$a  = required_param('a', PARAM_INT);  // instance ID

//$automultiplechoice  = $DB->get_record('automultiplechoice', array('id' => $a), '*', MUST_EXIST);
$quizz = \mod\automultiplechoice\Quizz::findById($a);
$course     = $DB->get_record('course', array('id' => $quizz->course), '*', MUST_EXIST);
$cm         = get_coursemodule_from_instance('automultiplechoice', $quizz->id, $course->id, false, MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/automultiplechoice:addinstance', $context);

// form submitted?
$questions = \mod\automultiplechoice\QuestionList::fromForm('question');
if ($questions) {
    $quizz->questions = $questions;
    if ($quizz->save()) {
        redirect(new moodle_url('view.php', array('a' => $quizz->id)));
    } else {
        die("Could not save into automultiplechoice");
    }
}

// add_to_log($course->id, 'automultiplechoice', 'view', "qselect.php?id={$cm->id}", $quizz->name, $cm->id);

$PAGE->set_url('/mod/automultiplechoice/qselect.php', array('a' => $quizz->id));
$PAGE->set_title(format_string($quizz->name));
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

// remove deleted questions
$quizz->validate();

$available_questions = automultiplechoice_list_questions($USER, $COURSE);

echo $OUTPUT->header();


echo $OUTPUT->box_start();
echo $OUTPUT->heading(get_string('questionoperations', 'automultiplechoice'));
echo '<p>' . $OUTPUT->action_link(
        new moodle_url('/local/questionssimplified/edit_wysiwyg.php', array('courseid' => $course->id)),
        get_string('importquestions', 'automultiplechoice'),
        null,
        array('target' => '_blank')
    ) . '</p>';
echo '<p>' . $OUTPUT->action_link(
        new moodle_url('/local/questionssimplified/edit_standard.php', array('courseid' => $course->id)),
        get_string('createquestions', 'automultiplechoice'),
        null,
        array('target' => '_blank')
    ) . '</p>';
echo $OUTPUT->box_end();

echo $OUTPUT->box_start();
echo $OUTPUT->heading(get_string('questionselect', 'automultiplechoice'));
if ($questions && $questions->errors) {
    echo $OUTPUT->box_start('errorbox');
    echo $OUTPUT->heading(get_string('errors', 'automultiplechoice'), 3);
    echo '<ul class="errors">';
    foreach ($questions->errors as $e) {
        echo '<li>ERROR (to localize, etc): ' . $e . '</li>';
    }
    echo '</ul>';
    echo $OUTPUT->box_end();
}
?>
<table id="questions-list">
    <thead>
        <tr>
            <th><?php echo get_string('qcategory', 'automultiplechoice'); ?></th>
            <th><?php echo get_string('qtitle', 'automultiplechoice'); ?></th>
            <th><?php echo get_string('date'); ?></th>
            <th><?php echo get_string('actions'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $editicon = $OUTPUT->pix_icon('i/edit', get_string('edit'));
        foreach ($available_questions as $q) {
            $editurl = new moodle_url(
                    '/local/questionssimplified/edit_standard.php',
                    array('questions' => $q->id, 'courseid' => $course->id)
            );
            if ($quizz->questions->contains($q->id)) {
                $button = '<button type="button" data-qid="' . $q->id . '" data-selected="true">-</button>'
                        . ' <a href="' . $editurl->out() . '" target="_blank">' . $editicon . '</a>';
            } else {
                $button = '<button type="button" data-qid="' . $q->id . '">+</button>'
                        . ' <a href="' . $editurl->out() . '" target="_blank">' . $editicon . '</a>';
            }
            echo '<tr id="q-' . $q->id . '">'
                . '<td>' . format_string($q->categoryname) . '</td>'
                . '<td class="qtitle">' . format_string($q->title) . '</td>'
                . '<td>' . date('Y-m-d', $q->timemodified) . '</td>'
                . '<td>' . $button .'</td>'
                . '</tr>';
        }
        ?>
    </tbody>
</table>
<div class="datatable-end"></div>
<?php
echo $OUTPUT->box_end();

echo $OUTPUT->box_start();
echo $OUTPUT->heading(get_string('questionselected', 'automultiplechoice'));
?>
<p>
    <?php echo get_string('sortmsg', 'automultiplechoice'); ?>
    <strong><?php echo get_string('qexpected', 'automultiplechoice', $quizz->qnumber); ?></strong>
</p>
<form name="questions-form" action="qselect.php" method="post">
<p>
    <input name="a" value="<?php echo $quizz->id; ?>" type="hidden" />
    <button type="submit"><?php echo get_string('savesel', 'automultiplechoice'); ?></button>
</p>
<ol id="questions-selected">
    <li style="display: none;" class="ui-state-default">
        <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
        <label></label>
        <input name="question[id][]" value="" type="hidden" disabled="disabled" />
        <button type="button">X</button>
        <label class="qscore">
            <?php echo get_string('qscore', 'automultiplechoice'); ?> :
            <input name="question[score][]" value="1" type="text" disabled="disabled" />
        </label>
    </li>
    <?php
    if (count($quizz->questions)) {
        foreach ($quizz->questions->getRecords() as $q) {
            echo '
    <li class="ui-state-default" id="qsel-' . $q->id . '">
        <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
        <label>' . format_string($q->name) . '</label>
        <input name="question[id][]" value="' . $q->id . '" type="hidden" />
        <button type="button">X</button>
        <label class="qscore">
            ' . get_string('qscore', 'automultiplechoice') . ' :
            <input name="question[score][]" value="' . ($q->score ? $q->score : sprintf('%.2f', $q->defaultmark)) . '" type="text" />
        </label>
    </li>
                ';
        }
    }
    ?>
</ol>
</form>

<?php
echo $OUTPUT->box_end();

echo $OUTPUT->footer();
