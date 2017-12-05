<?php

global $OUTPUT, $PAGE, $CFG;

require_once(__DIR__ . '/locallib.php');

/* @var $DB moodle_database */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$sharedservice = new \mod_automultiplechoice\shared_service();
$quiz = $sharedservice->getQuiz();
$cm = $sharedservice->getCm();
$course = $sharedservice->getCourse();

$output = $sharedservice->getRenderer();

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
            $res = $process->saveFormat('latex') && $process->amcPrepareBareme();
            if (!$res) {
                 \mod_automultiplechoice\local\helpers\flash_message_manager::addMessage('error', get_string('scoring_scale_extract_error', 'mod_automultiplechoice'));
            } else {
                \mod_automultiplechoice\local\helpers\log::build($quiz->id)->write('scoring');
                \mod_automultiplechoice\local\helpers\flash_message_manager::addMessage('success', get_string('scoring_scale_save_success', 'mod_automultiplechoice'));
            }

        } else {
            die(get_string('quiz_save_error', 'mod_automultiplechoice'));
        }
    }
}

require_capability('mod/automultiplechoice:update', $sharedservice->getContext());

// Output starts here.
$PAGE->set_url('/mod/automultiplechoice/scoringsystem.php', array('id' => $cm->id));

$PAGE->requires->js_call_amd('mod_automultiplechoice/scoringsystem', 'init');

echo $output->header('scoringsystem');

if (!$quiz->validate()) {
    $output->display_errors($quiz->errors);
}

// Scoring system form.
$view = new \mod_automultiplechoice\output\view_scoringform($quiz);
echo $output->render_scoring_form($view);

echo $OUTPUT->footer();
