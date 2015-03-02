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

$process = new amc\AmcProcessAssociate($quizz);
 if ($action === 'associate') {
    if ($process->associate()) {
        redirect(new moodle_url('associating.php', array('a' => $quizz->id)));
    }
} 


// Output starts here
echo $output->header();

echo $OUTPUT->box_start('informationbox well');
echo $OUTPUT->heading("Association", 2)
    . "<p>" . $process->usersknown . " copies identifiées et " . $process->usersunknown . " non identifiées. </p>";
;
echo HtmlHelper::buttonWithAjaxCheck('Relancer l\'association', $quizz->id, 'associating', 'associate', 'process');
echo $OUTPUT->box_end();


echo $output->footer();
