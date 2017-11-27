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
$output = $controller->getRenderer();
$action = optional_param('action', '', PARAM_ALPHA);

require_capability('mod/automultiplechoice:update', $controller->getContext());


$PAGE->set_url('/mod/automultiplechoice/grading.php', array('id' => $cm->id));

$process = new  \mod_automultiplechoice\local\amc\process($quiz);
if (!$process->isGraded() || $action === 'grade') {

    if ($process->amcNote()) {
        redirect($PAGE->url);
    }
}

// Has side effects, so must be called early.
$stats = $process->getStats2();

// Print Header to page
echo $output->header('grading');

//echo $process->getHtmlErrors();
$errors = \mod_automultiplechoice\local\helpers\log::build($quiz->id)->check('grading');

$data = [
    'errors' => $errors,
    'showerrors' => !empty($errors),
    'nbusersknown' => $process->get_known_users(),
    'nbusersunknown' => $process->get_unknown_users(),
    'filesurls' => [
      'csv' => $process->getFileUrl($process::PATH_AMC_CSV),
      'ods' => $process->getFileUrl($process::PATH_AMC_ODS),
      'apogee' => $process->getFileUrl($process::PATH_APOGEE_CSV)
    ],
    'stats' => $stats
];

// Page content.
$view = new \mod_automultiplechoice\output\view_grading($quiz, $data);
echo $output->render_grading_view($view);


echo $output->footer();
