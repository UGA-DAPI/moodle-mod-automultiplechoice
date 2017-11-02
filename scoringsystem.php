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
                 \mod_automultiplechoice\local\helpers\flash_message_manager::addMessage('error', "Erreur lors de l'extraction du barème");
            } else {
                \mod_automultiplechoice\local\helpers\log::build($quiz->id)->write('scoring');
                \mod_automultiplechoice\local\helpers\flash_message_manager::addMessage('success', "Les modification du barème ont été enregistrées.");
            }

        } else {
            die("Could not save into automultiplechoice");
        }
    } else {
        $output->displayErrors($quiz->errors);
    }
}

require_capability('mod/automultiplechoice:update', $controller->getContext());


// Output starts here
$PAGE->set_url('/mod/automultiplechoice/scoringsystem.php', array('id' => $cm->id));
//$PAGE->requires->js(new moodle_url('assets/scoringsystem.js'));
$PAGE->requires->js_call_amd('mod_automultiplechoice/scoringsystem', 'init');

echo $output->header();

if (!$quiz->validate()) {
    echo $OUTPUT->box_start('errorbox');
    echo '<p>' . get_string('someerrorswerefound') . '</p>';
    echo '<dl>';
    foreach ($quiz->errors as $field => $error) {
        $field = preg_replace('/^(.+)\[(.+)\]$/', '${1}_${2}', $field);
        echo "<dt>" . get_string($field, 'automultiplechoice') . "</dt>\n"
                . "<dd>" . get_string($error, 'automultiplechoice') . "</dd>\n";
    }
    echo "</dl>\n";
    echo $OUTPUT->box_end();
}

\mod_automultiplechoice\local\helpers\html::printFormFullQuestions($quiz);
echo $output->footer();
