<?php

/**
 * @package    mod_automultiplechoice
 * @copyright  2014 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \mod\automultiplechoice as amc;

require_once __DIR__ . '/locallib.php';
require_once __DIR__ . '/models/Grade.php';
require_once __DIR__ . '/models/AmcProcessPrepare.php';

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

$process = new amc\Grade($quizz);
if (!$process->isGraded() || $action === 'grade') {
    $prepare = new amc\AmcProcessPrepare($quizz);
    $prepare->saveFormat('latex');
    unset($prepare);
    if ($process->grade()) {       
        redirect(new moodle_url('grading.php', array('a' => $quizz->id)));
    }
} elseif ($action === 'anotate') {
    if ($process->anotate()) {
        redirect(new moodle_url('grading.php', array('a' => $quizz->id)));
    }
} elseif ($action === 'setstudentaccess') {
    $quizz->studentaccess = optional_param('studentaccess', false, PARAM_BOOL);
    $quizz->corrigeaccess = optional_param('corrigeaccess', false, PARAM_BOOL);
    $quizz->save();
    redirect(new moodle_url('grading.php', array('a' => $quizz->id)));
} else if ($action === 'notification') {
    $studentsto = $process->getUsersIdsHavingAnotatedSheets();
    $okSends = $process->sendAnotationNotification($studentsto);
    amc\FlashMessageManager::addMessage(
        ($okSends == count($studentsto)) ? 'success' : 'error',
        $okSends . " messages envoyés pour " . count($studentsto) . " étudiants ayant une copie annotée."
    );
    redirect(new moodle_url('grading.php', array('a' => $quizz->id)));
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

echo $process->getHtmlErrors();
$warnings = amc\Log::build($quizz->id)->check('grading');
if ($warnings) {
    echo '<div class="informationbox notifyproblem alert alert-error">';
    foreach ($warnings as $warning) {
        echo $warning;
    }

    echo "<br /><br />";
    echo HtmlHelper::buttonWithAjaxCheck('Relancer la correction', $quizz->id, 'grading', 'grade', 'process');
    echo HtmlHelper::buttonWithAjaxCheck('Regénérer les copies corrigées', $quizz->id, 'grading', 'anotate', 'process');
    echo "</div>";
}
echo $OUTPUT->box_start('informationbox well');
echo $OUTPUT->heading("Notes", 2)
    . $OUTPUT->heading("Fichiers tableaux des notes", 3)
    . "<p>" . $process->usersknown . " copies identifiées et " . $process->usersunknown . " non identifiées. </p>"
    . $process->getHtmlCsvLinks()
    . $OUTPUT->heading("Statistiques", 3)
    . $stats
    . "<p>
        Si le résultat de la notation ne vous convient pas, vous pouvez modifier le barème puis relancer la correction.
    </p>";
;
echo HtmlHelper::buttonWithAjaxCheck('Relancer la correction', $quizz->id, 'grading', 'grade', 'process');
echo $OUTPUT->box_end();



echo $output->footer();
