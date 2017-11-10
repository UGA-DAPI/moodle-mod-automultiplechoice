<?php

global $OUTPUT, $PAGE, $CFG;

require_once(__DIR__ . '/locallib.php');

/* @var $DB moodle_database */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$controller = new \mod_automultiplechoice\local\controllers\view_controller();
$quiz = $controller->getQuiz();
$cm = $controller->getCm();
$course = $controller->getCourse();

$output = $controller->getRenderer('scoringsystem');

if (!count($quiz->questions)) {
    redirect(new moodle_url('questions.php', array('a' => $quiz->id)));
}

// Handle form submission.
if (isset($_POST['score'])) {
    $quiz->score = (int) $_POST['score'];
    $quiz->amcparams->readFromForm($_POST['amc']);
    $pos = 0;
    foreach ($quiz->questions as $q) {
        if ($q->getType() === 'question') {
            /* @var $q amc\Question */
            $q->score = (float) $_POST['q']['score'][$pos];
        }
        $pos++;
    }
    if ($quiz->validate()) {
        if ($quiz->save()) {
            $process = new \mod_automultiplechoice\local\amc\process($quiz);
            $export = new \mod_automultiplechoice\local\amc\export($quiz);
            $res = $export->saveFormat('latex') && $process->amcPrepareBareme();
            if (!$res) {
                 \mod_automultiplechoice\local\helpers\flash_message_manager::addMessage('error', get_string('scoring_scale_extract_error', 'mod_automultiplechoice'));
            } else {
                \mod_automultiplechoice\local\helpers\log::build($quiz->id)->write('scoring');
                \mod_automultiplechoice\local\helpers\flash_message_manager::addMessage('success', get_string('scoring_scale_save_success', 'mod_automultiplechoice'));
            }

        } else {
            die(get_string('quiz_save_error', 'mod_automultiplechoice'));
        }
    } else {
        $output->display_errors($quiz->errors);
    }
}

require_capability('mod/automultiplechoice:update', $controller->getContext());

// Output starts here.
$PAGE->set_url('/mod/automultiplechoice/scoringsystem.php', array('id' => $cm->id));

$PAGE->requires->js_call_amd('mod_automultiplechoice/scoringsystem', 'init');

echo $output->header();

if (!$quiz->validate()) {
    $output->display_errors($quiz->errors);
}

// Scoring system form.
$view = new \mod_automultiplechoice\output\scoringform($quiz);
echo $output->render_scoring_form($view);

echo $OUTPUT->footer();
