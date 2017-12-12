<?php

/* @var $DB moodle_database */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */


require_once(__DIR__ . '/locallib.php');

global $OUTPUT, $PAGE, $CFG;

$sharedservice = new \mod_automultiplechoice\shared_service();
$sharedservice->parseRequest();
$quiz = $sharedservice->getQuiz();
$cm = $sharedservice->getCm();
$course = $sharedservice->getCourse();
\require_login($course, true, $cm);
$output = $sharedservice->getRenderer();
$process = new \mod_automultiplechoice\local\amc\process($quiz);


$apiurl = get_config('mod_automultiplechoice', 'amcapiurl');
$PAGE->requires->js_call_amd('mod_automultiplechoice/ajax-test', 'init', [$apiurl, $quiz->id]);

if ($sharedservice->should_redirect_to_questions()) {
    redirect(new moodle_url('questions.php', array('a' => $quiz->id)));
}

$PAGE->set_url('/mod/automultiplechoice/view.php', array('id' => $cm->id));

$viewcontext = $sharedservice->getContext();

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
