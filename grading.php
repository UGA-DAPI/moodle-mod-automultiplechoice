<?php

/**
 * @package    mod_automultiplechoice
 * @copyright  2014 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \mod\automultiplechoice as amc;

require_once __DIR__ . '/locallib.php';
require_once __DIR__ . '/models/AmcProcessGrade.php';

global $DB, $OUTPUT, $PAGE;
/* @var $DB moodle_database */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$controller = new amc\Controller();
$quizz = $controller->getQuizz();
$cm = $controller->getCm();
$course = $controller->getCourse();
$output = $controller->getRenderer('grading');
$action = optional_param('action', '', PARAM_ALPHA);

require_capability('mod/automultiplechoice:update', $controller->getContext());
$PAGE->set_url('/mod/automultiplechoice/grading.php', array('id' => $cm->id));

$process = new amc\AmcProcessGrade($quizz);
if (!$process->isGraded() || $action === 'grade') {
    
    if ($process->amcNote()) {
        redirect($PAGE->url);
    }
} 

// Has side effects, so must be called early.
$stats = $process->getHtmlStats();

// Output starts here
echo $output->header();

$checklock = json_encode(array('a' => $quizz->id, 'actions' => 'process'));
$button = '<form action="' . htmlspecialchars(new moodle_url('/mod/automultiplechoice/grading.php', array('a' => $quizz->id)))
    . '" method="post" class="checklock" data-checklock="' . htmlspecialchars($checklock) . '">
<p>
<input type="hidden" name="action" value="%s" />
<button type="submit">%s</button>
</p>
</form>';

//echo $process->getHtmlErrors();
$warnings = amc\Log::build($quizz->id)->check('grading');
if ($warnings) {
    echo '<div class="informationbox notifyproblem alert alert-error">';
    foreach ($warnings as $warning) {
        echo $warning;
    }

    echo "<br /><br />";
    echo $OUTPUT->single_button( new moodle_url('/mod/automultiplechoice/grading.php'),
                                array( 'a'=>$quizz->id, 'action'=> 'grade')
                                , 'Relancer la correction');
    echo "</div>";
}
echo $OUTPUT->box_start('informationbox well');
echo $OUTPUT->heading("Notes", 2);
echo $OUTPUT->heading("Fichiers tableaux des notes", 3);
echo "<p>" . $process->usersknown . " copies identifiées et " . $process->usersunknown . " non identifiées. </p>";
$opt = array('class'=>'btn','target'=>'_blank');
echo  \html_writer::start_div('btn-group');
echo  \html_writer::link($process->getFileUrl(amc\AmcProcess::PATH_AMC_CSV), 'csv',$opt);
echo  \html_writer::link($process->getFileUrl(amc\AmcProcess::PATH_AMC_ODS), 'ods',$opt);
echo  \html_writer::link($process->getFileUrl(amc\AmcProcess::PATH_APOGEE_CSV), 'apogee',$opt);
echo  \html_writer::end_div();


echo $OUTPUT->heading("Statistiques", 3);
echo $stats;
echo "<p>
        Si le résultat de la notation ne vous convient pas, vous pouvez modifier le barème puis relancer la correction.
    </p>";
;
    echo $OUTPUT->single_button( new moodle_url('/mod/automultiplechoice/grading.php'),
                                array( 'a'=>$quizz->id, 'action'=> 'grade')
                                , 'Relancer la correction');
echo $OUTPUT->box_end();



echo $output->footer();
