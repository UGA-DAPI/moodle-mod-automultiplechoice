<?php

require_once(__DIR__ . '/locallib.php');
//require_once __DIR__ . '/models/AmcProcessGrade.php';

global $DB, $OUTPUT, $PAGE;
/* @var $DB moodle_database */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$controller = new \mod_automultiplechoice\local\controllers\view_controller();
$quiz = $controller->getQuiz();
$cm = $controller->getCm();
$course = $controller->getCourse();
$output = $controller->getRenderer('grading');
$action = optional_param('action', '', PARAM_ALPHA);

require_capability('mod/automultiplechoice:update', $controller->getContext());


$PAGE->set_url('/mod/automultiplechoice/grading.php', array('id' => $cm->id));
$PAGE->requires->css(new moodle_url('assets/amc.css'));

$process = new  \mod_automultiplechoice\local\amc\process($quiz);
if (!$process->isGraded() || $action === 'grade') {

    if ($process->amcNote()) {
        redirect($PAGE->url);
    }
}

// Has side effects, so must be called early.
$stats = $process->getHtmlStats();

// Output starts here
echo $output->header();


//echo $process->getHtmlErrors();
$warnings = \mod_automultiplechoice\local\helpers\log::build($quiz->id)->check('grading');
if ($warnings) {
    echo '<div class="informationbox notifyproblem alert alert-error">';
    foreach ($warnings as $warning) {
        echo $warning;
    }

    echo "<br /><br />";
    echo $OUTPUT->single_button( new moodle_url('/mod/automultiplechoice/grading.php',
                                array( 'a'=>$quiz->id, 'action'=> 'grade'))
                                , 'Relancer la correction');
    echo "</div>";
}
echo $OUTPUT->box_start('informationbox well');
echo $OUTPUT->heading("Notes", 2);
echo $OUTPUT->heading("Fichiers tableaux des notes", 3);
echo "<p>" . $process->usersknown . " copies identifiées et " . $process->usersunknown . " non identifiées. </p>";
$opt = array('class'=>'btn','target'=>'_blank');
echo  \html_writer::start_div('btn-group');
echo  \html_writer::link($process->getFileUrl($process::PATH_AMC_CSV), 'csv',$opt);
echo  \html_writer::link($process->getFileUrl($process::PATH_AMC_ODS), 'ods',$opt);
echo  \html_writer::link($process->getFileUrl($process::PATH_APOGEE_CSV), 'apogee',$opt);
echo  \html_writer::end_div();


echo $OUTPUT->heading("Statistiques", 3);
echo $stats;
echo "<p>
        Si le résultat de la notation ne vous convient pas, vous pouvez modifier le barème puis relancer la correction.
    </p>";
;
    echo $OUTPUT->single_button( new moodle_url('/mod/automultiplechoice/grading.php',
                                array( 'a'=>$quiz->id, 'action'=> 'grade'))
                                , 'Relancer la correction');
echo $OUTPUT->box_end();



echo $output->footer();
