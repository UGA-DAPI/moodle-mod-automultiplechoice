<?php

/**
 * @package    mod_automultiplechoice
 * @copyright  2014 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \mod\automultiplechoice as amc;

require_once(__DIR__ . '/locallib.php');
require_once __DIR__ . '/models/Grade.php';
require_once __DIR__ . '/models/AmcProcessPrepare.php';

global $DB, $OUTPUT, $PAGE;
/* @var $DB moodle_database */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$controller = new amc\Controller();
$quizz = $controller->getQuizz();
$cm = $controller->getCm();
$course = $controller->getCourse();
$output = $controller->getRenderer('associating');
$action = optional_param('action', '', PARAM_ALPHA);

require_capability('mod/automultiplechoice:update', $controller->getContext());

/// Print the page header

$PAGE->set_url('/mod/automultiplechoice/associating.php', array('id' => $cm->id));
$PAGE->requires->css(new moodle_url('assets/amc.css'));

$process = new amc\Grade($quizz);
if (!$process->isGraded() || $action === 'grade') {
    $prepare = new amc\AmcProcessPrepare($quizz);
    $prepare->saveFormat('latex');
    unset($prepare);
    if ($process->grade()) {
        redirect(new moodle_url('grading.php', array('a' => $quizz->id)));
    }
} else if ($action === 'anotate') {
    if ($process->anotate()) {
        redirect(new moodle_url('grading.php', array('a' => $quizz->id)));
    }
} else if ($action === 'setstudentaccess') {
    $quizz->studentaccess = optional_param('studentaccess', false, PARAM_BOOL);
    $quizz->corrigeaccess = optional_param('corrigeaccess', false, PARAM_BOOL);
    $quizz->save();
    redirect(new moodle_url('grading.php', array('a' => $quizz->id)));
} else if ($action === 'notification') {
    $studentsto = $process->getUsersIdsHavingAnotatedSheets();
    $okSends = $process->sendAnotationNotification($studentsto);
    amc\FlashMessageManager::addMessage(
        ($okSends == count($studentsto)) ? 'success' : 'error',
        $okSends . " messages envoyés pour " . count($studentsto) . " étudiants ayant une copie annotée."
    );
    redirect(new moodle_url('grading.php', array('a' => $quizz->id)));
}

// Has side effects, so must be called early.
$stats = $process->getHtmlStats();

// Output starts here
echo $output->header();


echo $output->footer();
