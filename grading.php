<?php

/**
 * @package    mod_automultiplechoice
 * @copyright  2014 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \mod\automultiplechoice as amc;

require_once(__DIR__ . '/locallib.php');
require_once __DIR__ . '/models/Grade.php';

global $DB, $OUTPUT, $PAGE;
/* @var $DB moodle_database */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$controller = new amc\Controller();
$quizz = $controller->getQuizz();
$cm = $controller->getCm();
$course = $controller->getCourse();
$output = $controller->getRenderer('grading');

require_capability('mod/automultiplechoice:view', $controller->getContext());

/// Print the page header

$PAGE->set_url('/mod/automultiplechoice/grading.php', array('id' => $cm->id));
$PAGE->requires->css(new moodle_url('assets/amc.css'));

// Output starts here
echo $output->header();

$process = new amc\Grade($quizz);
$process->grade();
$process->anotate();

echo $process->getHtml();

// Bouton imprimer
?>
<form action="note.php?a=<?php echo $quizz->id; ?>" method="post">
    <p>
        <label for="submit">Télécharger les copies annotées</label>
        <input type="submit" name="submit" value="Annotations">
    </p>
</form>
<?php
// <label for="compose">Copies composées</label>  <input type="checkbox" name="compose" id="compose">

echo $output->footer();
