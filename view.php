<?php

/* @var $DB moodle_database */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */


require_once(__DIR__ . '/locallib.php');

global $OUTPUT, $PAGE, $CFG;

$controller = new \mod_automultiplechoice\local\controllers\view_controller();
$quiz = $controller->getQuiz();
$cm = $controller->getCm();
$course = $controller->getCourse();
$output = $controller->getRenderer();
$process = new \mod_automultiplechoice\local\amc\process($quiz);

if (!count($quiz->questions)) {
    redirect(new moodle_url('questions.php', array('a' => $quiz->id)));
}

$PAGE->set_url('/mod/automultiplechoice/view.php', array('id' => $cm->id));

$viewcontext = $controller->getContext();

// Render header.
echo $output->header('dashboard');

require_capability('mod/automultiplechoice:view', $viewcontext);

if (!has_capability('mod/automultiplechoice:update', $viewcontext)) {
    $studentview = new \mod_automultiplechoice\output\studentview($quiz, $process, $USER);
    echo $output->render_student_view($studentview);
    echo $output->footer();
    return;
}

// Anyone with editing capability.

// Maybe this log should be recorded for the student to ?
add_to_log($course->id, 'automultiplechoice', 'view', "view.php?id={$cm->id}", $quiz->name, $cm->id);

if (!$quiz->validate()) {
    $output->display_errors($quiz->errors);
}

// Dashboard content.
$view = new \mod_automultiplechoice\output\view_dashboard($quiz);
echo $output->render_dashboard($view);
echo $OUTPUT->footer();
