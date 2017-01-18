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
        //redirect(new moodle_url('view.php', array('a' => $quizz->id)));
    }
    $quizz->questions = $questions;
 
    if ($quizz->save()) {
        amc\Log::build($quizz->id)->write('saving');
        /*if ($quizz->score > 0) {
            redirect(new moodle_url('view.php', array('a' => $quizz->id)));
        } else {
            redirect(new moodle_url('scoringsystem.php', array('a' => $quizz->id)));
        }*/
    } else {
        die("Could not save into automultiplechoice");
    }
}

$PAGE->set_url('/mod/automultiplechoice/questions.php', array('a' => $quizz->id));
$PAGE->set_cacheable(false);
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('dataTables', 'mod_automultiplechoice');
$PAGE->requires->js(new moodle_url('assets/questions.js'));
$PAGE->requires->css(new moodle_url('assets/datatable-override.css'));

// remove deleted questions
$quizz->validate();

$available_questions = automultiplechoice_list_questions($USER, $course);

echo $output->header();

echo $OUTPUT->box_start();
echo $OUTPUT->heading(get_string('questionoperations', 'automultiplechoice'));
$opt = array('class' => 'btn', 'target' =>'_blank');
echo  \html_writer::start_div('btn-group');
echo  $OUTPUT->action_link(
        new moodle_url('/question/import.php', array('courseid' => $course->id)),
        get_string('importfilequestions', 'automultiplechoice'),
        null,
        $opt
    ) ;
echo  $OUTPUT->action_link(
        new moodle_url('/local/questionssimplified/edit_wysiwyg.php', array('courseid' => $course->id)),
        get_string('importquestions', 'automultiplechoice'),
        null,
        $opt
    ) ;
echo  $OUTPUT->action_link(
        new moodle_url('/local/questionssimplified/edit_standard.php', array('courseid' => $course->id)),
        get_string('createquestions', 'automultiplechoice'),
        null,
        $opt
    ) ;
echo   $OUTPUT->action_link(
        new moodle_url('/question/edit.php', array('courseid' => $course->id)),
        get_string('questionbank', 'question'),
        null,
        $opt
    )  ;
echo  \html_writer::end_div();
echo  \html_writer::div("<p>Si vos questions récentes n'apparaissent pas, "
        . "pensez à <strong>rafraichir la page</strong> de votre navigateur (<strong>F5</strong>) et à trier par date descendante.</p>",
        'informationbox notifyproblem alert alert-info');

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
        
        $stringAdd = format_string(get_string('add'));
        $stringRemove = format_string(get_string('remove'));
        foreach ($available_questions as $q) {
            $editurl = new moodle_url(
                '/local/questionssimplified/edit_standard.php',
                array('questions' => $q->id, 'courseid' => $course->id)
            );

            // add a trash / delete button idf question belongs to the selected ones 
            if ($quizz->questions->contains($q->id)) {
                $actions = '<div class="btn-group" role="group">';
                $actions .= '<button class="btn btn-sm btn-danger" role="button" type="button" data-qid="' . $q->id . '" data-selected="true" title="' . $stringRemove .'">';
                $actions .= ' <span class="fa fa-trash"></span>';
                $actions .= '</button>';
                $actions .= ' <a href="' . $editurl->out() . '" class="btn btn-sm btn-default" target="_blank"><span class="fa fa-pencil"></span></a>';
                $actions .= '</div>';
            } else {
                // add a plus button if the question does not belong to the quiz
                $actions = '<div class="btn-group" role="group">';
                $actions .= '<button class="btn btn-sm btn-default" role="button" type="button" data-qid="' . $q->id . '" title="' . $stringAdd .'">';
                $actions .= ' <span class="fa fa-plus"></span>';
                $actions .= '</button>';
                $actions .= ' <a href="' . $editurl->out() . '" class="btn btn-sm btn-default" target="_blank"><span class="fa fa-pencil"></span></a>';
                $actions .= '</div>';
            }
            echo '<tr id="q-' . $q->id . '">'
                . '<td>' . format_string($q->categoryname) . '</td>'
                . '<td class="qtitle">' . format_string($q->title) . '</td>'
                . '<td>' . date('Y-m-d h:i', $q->timemodified) . '</td>'
                . '<td class="qactions">' . $actions .'</td>'
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
    <button class="btn btn-default" type="submit">
        <?php echo get_string('savesel', 'automultiplechoice'); ?>
    </button>
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
    <button type="button" role="button" class="btn btn-default" id="insert-section">
        <?php echo get_string('insertsection', 'automultiplechoice'); ?>
    </button>
</p>
</form>

<?php
echo $OUTPUT->box_end();

echo $output->footer();
