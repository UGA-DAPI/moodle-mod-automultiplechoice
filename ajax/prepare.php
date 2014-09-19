<?php

/*
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

global $CFG;

require_once dirname(dirname(dirname(__DIR__))) . '/config.php';
require_once dirname(__DIR__) . '/models/Quizz.php';
require_once dirname(__DIR__) . '/models/AmcProcessPrepare.php';

$a  = optional_param('a', 0, PARAM_INT);  // automultiplechoice instance ID
$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$redirect = optional_param('redirect', false, PARAM_BOOL);

if ($a) {
    $quizz = \mod\automultiplechoice\Quizz::findById($a);
    $course     = $DB->get_record('course', array('id' => $quizz->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('automultiplechoice', $quizz->id, $course->id, false, MUST_EXIST);
} else {
    require_login();
    echo $OUTPUT->error_text('You must specify an instance ID');
    exit();
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/automultiplechoice:view', $context);

$process = new \mod\automultiplechoice\AmcProcessPrepare($quizz);

if ($action == 'prepare') {
    if ($process->amcCreatePdf("latex")) {
        echo "<h3>Fichiers PDF nouvellement créés</h3>";
        echo $process->htmlPdfLinks();
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
        echo $process->htmlZipLink();
    } else {
        echo $OUTPUT->error_text("Erreur lors de la création de l'archive.");
        exit();
    }
}

if ($redirect) {
    redirect('/mod/automultiplechoice/documents.php?a=' . $quizz->id);
}
