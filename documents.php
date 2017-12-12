<?php

require_once(__DIR__ . '/locallib.php');


global $DB, $OUTPUT, $PAGE;
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$sharedservice = new \mod_automultiplechoice\shared_service();
$sharedservice->parseRequest();
$quiz = $sharedservice->getQuiz();
$cm = $sharedservice->getCm();
$course = $sharedservice->getCourse();
\require_login($course, true, $cm);
$output = $sharedservice->getRenderer();

require_capability('mod/automultiplechoice:update', $sharedservice->getContext());

// Handle Form submission.
$action = optional_param('action', '', PARAM_ALPHA);
$process = new \mod_automultiplechoice\local\amc\process($quiz);

$errors = [];
if ($action === 'lock') {
    $quiz->amcparams->locked = true;
    \mod_automultiplechoice\local\helpers\log::build($quiz->id)->write('lock');
    $quiz->save();
    array_map('backup_source', glob($quiz->getDirName() . '/prepare-source.*'));
    if (!$process->amcMeptex()) {
        $process->errors[] = get_string('documents_meptex_error', 'mod_automultiplechoice');
        $errors[] = get_string('documents_meptex_error', 'mod_automultiplechoice');
    }
    copy($quiz->getDirName().'/data/capture.sqlite', $quiz->getDirName().'/data/capture.sqlite.orig');
} else if ($action === 'unlock') {
    $quiz->amcparams->locked = false;
    $quiz->save();
    redirect(new moodle_url('documents.php', array('a' => $quiz->id)));
} else if ($action === 'prepare') {
    array_map('unlink', glob($quiz->getDirName() . '/sujet*'));
    redirect(new moodle_url('documents.php', array('a' => $quiz->id)));
} else if ($action === 'randomize') {
    $quiz->amcparams->randomize();
    $quiz->save();
    \mod_automultiplechoice\local\helpers\log::build($this->quizz->id)->write('saving');
    $zipfile = $process->workdir.'/'.$process->normaliseFileName('sujets');
    if (file_exists($zipfile)) {
        unlink($zipfile);
    }
    $mask = $pre . "/imprime/*.pdf";
    array_map('unlink', glob($mask));
    redirect(new moodle_url('documents.php', array('a' => $quiz->id)));
} else if ($action === 'restore') {
    array_map('restore_source', glob($quiz->getDirName() . '/*.orig'));
    copy($quiz->getDirName().'/data/capture.sqlite.orig', $quiz->getDirName().'data/capture.sqlite');
}

$PAGE->set_url('/mod/automultiplechoice/documents.php', array('id' => $cm->id));

echo $output->header('documents');

if (!$quiz->isLocked()) {
    foreach (\mod_automultiplechoice\local\helpers\log::build($quiz->id)->check('pdf') as $warning) {
        echo $OUTPUT->notification($warning, 'notifyproblem');
    }
}

$data = [
  'errors' => $errors,
  'canrestore' => has_capability('mod/automultiplechoice:restoreoriginalfile', $sharedservice->getContext()),
  'ziplink' => $process->getZipLink(),
  'pdflinks' => $process->getPdfLinks(),
  'canlock' => $quiz->hasDocuments()
];


// Dashboard content.
$view = new \mod_automultiplechoice\output\view_documents($quiz, $data);
echo $output->render_documents_view($view);
echo $OUTPUT->footer();
