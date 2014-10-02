<?php

/**
 * @package    mod_automultiplechoice
 * @copyright  2014 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \mod\automultiplechoice as amc;

require_once dirname(__DIR__) . '/locallib.php';
require_once dirname(__DIR__) . '/models/AmcProcessPrepare.php';

global $DB, $OUTPUT, $PAGE;
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$controller = new amc\Controller();
$quizz = $controller->getQuizz();
$cm = $controller->getCm();
$course = $controller->getCourse();

require_capability('mod/automultiplechoice:update', $controller->getContext());

$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$redirect = optional_param('redirect', false, PARAM_BOOL);

$process = new amc\AmcProcessPrepare($quizz);

if ($action == 'prepare') {
    if ($process->amcCreatePdf("latex")) {
        echo "<h3>Fichiers PDF nouvellement créés</h3>";
        echo $process->getHtmlPdfLinks();
    } else {
        echo $OUTPUT->error_text("Erreur lors de la création des fichiers PDF :" . $process->getLastError());
        exit();
    }

    if (!$process->amcMeptex()) {
        echo $OUTPUT->error_text("Erreur lors du calcul de mise en page (amc meptex).");
        exit();
    }
} else if ($action == 'zip') {
    if ($process->printAndZip()) {
        echo "<h3>Archive Zip créée</h3>";
        echo $process->getHtmlZipLink();
    } else {
        echo $OUTPUT->error_text("Erreur lors de la création de l'archive.");
        exit();
    }
}

if ($redirect) {
    redirect('/mod/automultiplechoice/documents.php?a=' . $quizz->id);
}
