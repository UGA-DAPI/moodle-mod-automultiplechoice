<?php

/**
 * Selects questions.
 *
 * @package    mod_automultiplechoice
 * @copyright  2013-2014 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \mod\automultiplechoice as amc;

require_once __DIR__ . '/locallib.php';

global $OUTPUT, $PAGE;
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$controller = new amc\Controller();
$quizz = $controller->getQuizz();
$cm = $controller->getCm();
$course = $controller->getCourse();
$output = $controller->getRenderer('questions');

require_capability('mod/automultiplechoice:addinstance', $controller->getContext());

// form submitted?
$questions = \mod\automultiplechoice\QuestionList::fromForm('question');
if ($questions) {
    if ($quizz->isLocked()) { // no modification allowed
        /**
         * @todo warn that modification is not allowed on a locked quizz.
         */
        redirect(new moodle_url('view.php', array('a' => $quizz->id)));
    }
    $quizz->questions = $questions;
    if ($quizz->save()) {
        if ($quizz->score > 0) {
            redirect(new moodle_url('view.php', array('a' => $quizz->id)));
        } else {
            redirect(new moodle_url('scoringsystem.php', array('a' => $quizz->id)));
        }
    } else {
        die("Could not save into automultiplechoice");
    }
}

// add_to_log($course->id, 'automultiplechoice', 'view', "questions.php?id={$cm->id}", $quizz->name, $cm->id);

$PAGE->set_url('/mod/automultiplechoice/questions.php', array('a' => $quizz->id));
$PAGE->set_cacheable(false);

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('dataTables','mod_automultiplechoice');
//$PAGE->requires->js(new \moodle_url('/mod/automultiplechoice/assets/dataTables/jquery.dataTables.min.js'));
//$PAGE->requires->css(new \moodle_url('/mod/automultiplechoice/assets/dataTables/css/jquery.dataTables.css'));


$PAGE->requires->js(new moodle_url('assets/questions.js'));
$PAGE->requires->css(new moodle_url('assets/amc.css'));

// remove deleted questions
$quizz->validate();

$available_questions = automultiplechoice_list_questions($USER, $course);

echo $output->header();

echo $OUTPUT->box_start();
echo $OUTPUT->heading(get_string('questionoperations', 'automultiplechoice'));
echo '<p>' . $OUTPUT->action_link(
        new moodle_url('/question/import.php', array('courseid' => $course->id)),
        get_string('importfilequestions', 'automultiplechoice'),
        null,
        array('target' => '_blank')
    ) . '</p>';
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
echo '<p>' . $OUTPUT->action_link(
        new moodle_url('/question/edit.php', array('courseid' => $course->id)),
        get_string('questionbank', 'question'),
        null,
        array('target' => '_blank')
    ) . '</p>';
echo "<p>Si vos questions récentes n'apparaissent pas, "
        . "pensez à rafraichir la page de votre navigateur (F5) et à trier par date descendante.</p>";
echo $OUTPUT->box_end();

echo $OUTPUT->box_start('generalbox', 'questions-part-selecting');
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
        $stringAdd = format_string(get_string('add'));
        $stringRemove = format_string(get_string('remove'));
        foreach ($available_questions as $q) {
            $editurl = new moodle_url(
                    '/local/questionssimplified/edit_standard.php',
                    array('questions' => $q->id, 'courseid' => $course->id)
            );
            if ($quizz->questions->contains($q->id)) {
                $button = '<button type="button" data-qid="' . $q->id . '" data-selected="true" title="' . $stringRemove .'">&#x2A2F;</button>'
                        . ' <a href="' . $editurl->out() . '" target="_blank">' . $editicon . '</a>';
            } else {
                $button = '<button type="button" data-qid="' . $q->id . '" title="' . $stringAdd .'">&gt;&gt;</button>'
                        . ' <a href="' . $editurl->out() . '" target="_blank">' . $editicon . '</a>';
            }
            echo '<tr id="q-' . $q->id . '">'
                . '<td>' . format_string($q->categoryname) . '</td>'
                . '<td class="qtitle">' . format_string($q->title) . '</td>'
                . '<td>' . date('Y-m-d h:i', $q->timemodified) . '</td>'
                . '<td>' . $button .'</td>'
                . '</tr>';
        }
        ?>
    </tbody>
</table>
<div class="datatable-end"></div>
<?php
echo $OUTPUT->box_end();

echo $OUTPUT->box_start('generalbox', 'questions-part-selected');
echo $OUTPUT->heading(get_string('questionselected', 'automultiplechoice'));
?>
<p>
    <?php echo get_string('sortmsg', 'automultiplechoice'); ?>
    <strong><?php echo get_string('qexpected', 'automultiplechoice', $quizz->qnumber); ?></strong>
</p>
<form name="questions-form" action="questions.php" method="post">
<p>
    <input name="a" value="<?php echo $quizz->id; ?>" type="hidden" />
    <button type="submit"><?php echo get_string('savesel', 'automultiplechoice'); ?></button>
</p>
<ol id="questions-selected">
    <?php
    $emptyQuestion = new amc\Question();
    echo $emptyQuestion->toHtml();
    $emptySection = new amc\QuestionSection();
    echo $emptySection->toHtml();
    if (count($quizz->questions)) {
        foreach ($quizz->questions as $q) {
            echo $q->toHtml();
        }
    }
    ?>
</ol>
<p>
    <button type="button" id="insert-section"><?php echo get_string('insertsection', 'automultiplechoice'); ?></button>
</p>
</form>

<?php
echo $OUTPUT->box_end();

echo $output->footer();
