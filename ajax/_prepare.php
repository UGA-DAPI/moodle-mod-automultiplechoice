<?php

require_once(dirname(__DIR__) . '/locallib.php');

global $DB, $OUTPUT, $PAGE;
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$controller = new \mod_automultiplechoice\local\controllers\view_controller();
$quiz = $controller->getQuiz();
$cm = $controller->getCm();
$course = $controller->getCourse();

require_capability('mod/automultiplechoice:update', $controller->getContext());

$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$redirect = optional_param('redirect', false, PARAM_BOOL);

$process = new \mod_automultiplechoice\local\amc\process($quiz);
$export = new \mod_automultiplechoice\local\amc\export($quiz);

if ($action == 'prepare') {
    if ($export->amcCreatePdf("latex")) {
        echo "<h3>Fichiers PDF nouvellement créés</h3>";
        echo $process->getHtmlPdfLinks();
    } else {
        echo $OUTPUT->error_text("Erreur lors de la création des fichiers PDF :" . $process->getLastError());
        exit();
    }
} else if ($action == 'zip') {
    // n'existe plus...
    if ($process->printAndZip()) {
        echo "<h3>Archive Zip créée</h3>";
        echo $process->getHtmlZipLink();
    } else {
        echo $OUTPUT->error_text("Erreur lors de la création de l'archive.");
        exit();
    }
}

if ($redirect) {
    redirect('/mod/automultiplechoice/documents.php?a=' . $quiz->id);
}
