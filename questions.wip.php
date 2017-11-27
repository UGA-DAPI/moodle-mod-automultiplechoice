<?php

require_once(__DIR__ . '/locallib.php');

global $OUTPUT, $PAGE;
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$controller = new \mod_automultiplechoice\local\controllers\view_controller();
$quiz = $controller->getQuiz();
$cm = $controller->getCm();
$course = $controller->getCourse();
$output = $controller->getRenderer();
$thiscontext = $controller->getContext();

require_capability('mod/automultiplechoice:addinstance', $controller->getContext());

// Form submitted?.
$questions = \mod_automultiplechoice\local\models\question_list::fromForm('question');
if ($questions) {
    $quiz->questions = $questions;

    if ($quiz->save()) {
        \mod_automultiplechoice\local\helpers\log::build($quiz->id)->write('saving');
    } else {
        die(get_string('quiz_save_error', 'mod_automultiplechoice'));
    }
}

$PAGE->set_url('/mod/automultiplechoice/questions.php', array('a' => $quiz->id));
$PAGE->set_cacheable(false);
$PAGE->requires->js_call_amd('mod_automultiplechoice/questions', 'init');


// Remove deleted questions.
$quiz->validate();

$available_questions = automultiplechoice_list_questions($USER, $course);

echo $output->header('questions');


$contexts = new question_edit_contexts($thiscontext);
$questionbank = new \mod_automultiplechoice\question\bank\custom_view($contexts, $PAGE->url, $course, $cm, $quiz);
$questionbank->set_quiz_has_attempts(false);
$questionbank->process_actions($PAGE->url, $cm);

echo $output->question_bank_contents($questionbank, []);
$data = [
    'errors' => $quiz->questions->errors,
    'showerrors' => !empty($quiz->questions->errors),
    'courseid' => $course->id
    /*,
    'availablequestions' => automultiplechoice_list_questions($USER, $course),*/
];


// Page content.
$view = new \mod_automultiplechoice\output\view_questions($quiz, $data);
echo $output->render_questions_view($view);
echo $output->footer();
/*
echo $OUTPUT->box_start();
echo $OUTPUT->heading(get_string('questionoperations', 'automultiplechoice'));
$opt = array('class' => 'btn', 'target' => '_blank');
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
            if ($quiz->questions->contains($q->id)) {
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
    <strong><?php echo get_string('qexpected', 'automultiplechoice', $quiz->qnumber); ?></strong>
</p>
<form name="questions-form" action="questions.php" method="post">
<p>
    <input name="a" value="<?php echo $quiz->id; ?>" type="hidden" />
    <button class="btn btn-default" type="submit">
        <?php echo get_string('savesel', 'automultiplechoice'); ?>
    </button>
</p>
<ol id="questions-selected">
    <?php
    $emptyQuestion = new \mod_automultiplechoice\local\models\question();
    echo $emptyQuestion->toHtml();
    $emptySection = new \mod_automultiplechoice\local\models\question_section();
    echo $emptySection->toHtml();
    if (count($quiz->questions)) {
        foreach ($quiz->questions as $q) {
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
*/
