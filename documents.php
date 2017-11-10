<?php

require_once(__DIR__ . '/locallib.php');


global $DB, $OUTPUT, $PAGE;
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$controller = new \mod_automultiplechoice\local\controllers\view_controller();
$quiz = $controller->getQuiz();
$cm = $controller->getCm();
$course = $controller->getCourse();
$output = $controller->getRenderer('documents');

require_capability('mod/automultiplechoice:update', $controller->getContext());


// Handle Form submission.
$action = optional_param('action', '', PARAM_ALPHA);
$process = new \mod_automultiplechoice\local\amc\process($quiz);
if ($action === 'lock') {
    $quiz->amcparams->locked = true;
    \mod_automultiplechoice\local\helpers\log::build($quiz->id)->write('lock');
    $quiz->save();
    array_map('backup_source', glob($quiz->getDirName() . '/prepare-source.*'));
    if (!$process->amcMeptex()) {
        $process->errors[] = get_string('documents_meptex_error', 'mod_automultiplechoice');
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

echo $output->header();


if ($quiz->isLocked()) {
    $unlockurl = new \moodle_url('documents.php', array('a' => $quiz->id, 'action' => 'unlock'));
    $unlockbutton = new \single_button(
        $unlockurl,
        get_string('unlock_quiz', 'mod_automultiplechoice')
    );
    $message = \mod_automultiplechoice\local\helpers\log::build($quiz->id)->check('unlock');
    if ($message) {
        $unlockbutton->add_confirm_action(implode('\n', $message));
    }
    echo '<div class="informationbox notifyproblem alert alert-info">'
        . '<div>' .get_string('quiz_is_locked', 'mod_automultiplechoice') .'</div>'
        . $OUTPUT->render($unlockbutton)
        . "</div>";

} else {
    foreach (\mod_automultiplechoice\local\helpers\log::build($quiz->id)->check('pdf') as $warning) {
        echo $OUTPUT->notification($warning, 'notifyproblem');
    }
}

// Dashboard content.
$view = new \mod_automultiplechoice\output\documents($quiz);
echo $output->render_documents_view($view);
echo $OUTPUT->footer();