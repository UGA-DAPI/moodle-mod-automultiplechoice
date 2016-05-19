<?php

/**
 * Shows details of a particular instance of automultiplechoice
 *
 * @package    mod_automultiplechoice
 * @copyright  2013 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* @var $DB moodle_database */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

use \mod\automultiplechoice as amc;

require_once __DIR__ . '/locallib.php';

global $OUTPUT, $PAGE, $CFG;

$controller = new amc\Controller();
$quizz = $controller->getQuizz();
$cm = $controller->getCm();
$course = $controller->getCourse();
$output = $controller->getRenderer('scoringsystem');

if (!count($quizz->questions)) {
    redirect(new moodle_url('questions.php', array('a' => $quizz->id)));
}
if (isset($_POST['score'])) {
    $quizz->score = (int) $_POST['score'];
    $quizz->amcparams->readFromForm($_POST['amc']);
    $pos = 0;
    foreach ($quizz->questions as $q) {
        if ($q->getType() === 'question') {
            /* @var $q amc\Question */
            $q->score = (float) $_POST['q']['score'][$pos];
        }
        $pos++;
    }
    if ($quizz->validate()) {
        if ($quizz->save()) {
            amc\FlashMessageManager::addMessage('success', "Les modification du barème ont été enregistrées.");
            redirect(new moodle_url('view.php', array('a' => $quizz->id)));
        } else {
            die("Could not save into automultiplechoice");
        }
    } else {
        $output->displayErrors($quizz->errors);
    }
}

require_capability('mod/automultiplechoice:update', $controller->getContext());

//add_to_log($course->id, 'automultiplechoice', 'view', "scoringsystem.php?id={$cm->id}", $quizz->name, $cm->id);

// Output starts here
$PAGE->set_url('/mod/automultiplechoice/scoringsystem.php', array('id' => $cm->id));
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('assets/scoring.js'));
$PAGE->requires->css(new moodle_url('assets/amc.css'));

echo $output->header();

if (!$quizz->validate()) {
    echo $OUTPUT->box_start('errorbox');
    echo '<p>' . get_string('someerrorswerefound') . '</p>';
    echo '<dl>';
    foreach ($quizz->errors as $field => $error) {
        $field = preg_replace('/^(.+)\[(.+)\]$/', '${1}_${2}', $field);
        echo "<dt>" . get_string($field, 'automultiplechoice') . "</dt>\n"
                . "<dd>" . get_string($error, 'automultiplechoice') . "</dd>\n";
    }
    echo "</dl>\n";
    echo $OUTPUT->box_end();
}

HtmlHelper::printFormFullQuestions($quizz);

echo $output->footer();
