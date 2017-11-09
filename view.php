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
$output = $controller->getRenderer('dashboard');
$process = new \mod_automultiplechoice\local\amc\process($quiz);

if (!count($quiz->questions)) {
    redirect(new moodle_url('questions.php', array('a' => $quiz->id)));
}

$PAGE->set_url('/mod/automultiplechoice/view.php', array('id' => $cm->id));

// Is it used ?
//$PAGE->requires->js_call_amd('mod_automultiplechoice/scoringsystem', 'init');

$viewcontext = $controller->getContext();

// Render header.
echo $output->header();

require_capability('mod/automultiplechoice:view', $viewcontext);

if (!has_capability('mod/automultiplechoice:update', $viewcontext)) {
    $studentview = new \mod_automultiplechoice\output\student_view($quiz, $process, $USER);
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

if ($quiz->isLocked()) {
    // Cannot put a button if we use $OUTPUT->notification.
    $unlockurl = new \moodle_url('documents.php', array('a' => $quiz->id, 'action' => 'unlock'));
    $unlockbutton = new \single_button($unlockurl, get_string('unlock_quiz', 'mod_automultiplechoice'));
    $message = \mod_automultiplechoice\local\helpers\log::build($quiz->id)->check('unlock');
    if ($message) {
        $unlockbutton->add_confirm_action(implode('\n', $message));
    }
    echo '<div class="informationbox notifyproblem alert alert-info">'
        . get_string('quiz_is_locked', 'mod_automultiplechoice')
        . get_string('access_documents', 'mod_automultiplechoice')
        . '<em> '.get_string('documents', 'mod_automultiplechoice').'</em>'
        . $OUTPUT->render($unlockbutton)
        . "</div>";
}

// Dashboard content.
$dashboard = new \mod_automultiplechoice\output\dashboard($quiz);
echo $output->render_dashboard($dashboard);
echo $OUTPUT->footer();
