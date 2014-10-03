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
require_once __DIR__ . '/models/Grade.php';

global $OUTPUT, $PAGE, $CFG;

$controller = new amc\Controller();
$quizz = $controller->getQuizz();
$cm = $controller->getCm();
$course = $controller->getCourse();
$output = $controller->getRenderer('dashboard');
$process = new amc\Grade($quizz);


$PAGE->set_url('/mod/automultiplechoice/notification.php', array('id' => $cm->id));
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('assets/scoring.js'));
$PAGE->requires->css(new moodle_url('assets/amc.css'));

$viewContext = $controller->getContext();
require_capability('mod/automultiplechoice:view', $viewContext);

$studentsto = $process->getUsersWithAnotatedSheets();
var_dump($studentsto);
$count = $process->sendAnotationNotification($studentsto);
var_dump($count);