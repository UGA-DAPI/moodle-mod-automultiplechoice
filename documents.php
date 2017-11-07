<?php

/**
 * Prepare the 2 pdf files (sujet + corrigé) and let the user download them
 *
 * @package    mod_automultiplechoice
 * @copyright  2013 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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

$action = optional_param('action', '', PARAM_ALPHA);
$process = new \mod_automultiplechoice\local\amc\process($quiz);
if ($action === 'lock') {
    $quiz->amcparams->locked = true;
    \mod_automultiplechoice\local\helpers\log::build($quiz->id)->write('lock');
    $quiz->save();
    array_map('backup_source', glob($quiz->getDirName() . '/prepare-source.*'));
    if (!$process->amcMeptex()) {
        $process->errors[] = gt_string('documents_meptex_error', 'mod_automultiplechoice');
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
    copy($quiz->getDirName().'/data/capture.sqlite.orig',$quiz->getDirName().'data/capture.sqlite');
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
        . get_string('quiz_is_locked', 'mod_automultiplechoice')
        . $OUTPUT->render($unlockbutton)
        . "</div>\n";

    echo $OUTPUT->heading(get_string('documents_pdf_created', 'mod_automultiplechoice'), 3);
    echo $process->getHtmlPdfLinks();

    echo $OUTPUT->heading(get_string('documents_zip_archive', 'mod_automultiplechoice'), 3);
    echo $process->getHtmlZipLink();
    if (has_capability('mod/automultiplechoice:restoreoriginalfile', $controller->getContext())) {
        echo $OUTPUT->single_button(
                new moodle_url('/mod/automultiplechoice/documents.php', array('a' => $quiz->id, 'action' => 'restore')),
                get_string('documents_restore_original_version', 'mod_automultiplechoice'), 'post'
            );
    }

} else {
    foreach (\mod_automultiplechoice\local\helpers\log::build($quiz->id)->check('pdf') as $warning) {
        echo $OUTPUT->notification($warning, 'notifyproblem');
    }
    echo $OUTPUT->heading(get_string('documents_pdf_created', 'mod_automultiplechoice'), 3);
    echo $process->getHtmlPdfLinks();
    echo $OUTPUT->single_button(
                    new moodle_url('/mod/automultiplechoice/documents.php', array('a' => $quiz->id, 'action' => 'randomize')),
                    get_string('documents_mix_answers_and_questions', 'mod_automultiplechoice'));

            echo $OUTPUT->single_button(
                    new moodle_url('/mod/automultiplechoice/documents.php', array('a' => $quiz->id, 'action' => 'lock')),
                    get_string('lock_quiz', 'mod_automultiplechoice'));
}

echo $output->footer();
