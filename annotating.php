<?php

/**
 * @package    mod_automultiplechoice
 * @copyright  2014 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \mod\automultiplechoice as amc;

require_once(__DIR__ . '/locallib.php');
require_once __DIR__ . '/models/AmcProcessAnnotate.php';


global $DB, $OUTPUT, $PAGE;
/* @var $DB moodle_database */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$controller = new amc\Controller();
$quizz = $controller->getQuizz();
$cm = $controller->getCm();
$course = $controller->getCourse();
$output = $controller->getRenderer('annotating');
$action = optional_param('action', '', PARAM_ALPHA);

require_capability('mod/automultiplechoice:update', $controller->getContext());

/// Print the page header

$PAGE->set_url('/mod/automultiplechoice/annotating.php', array('id' => $cm->id));
$PAGE->requires->css(new moodle_url('assets/amc.css'));

$process = new amc\AmcProcessAnnotate($quizz);
if ($action === 'anotate') {
    if ($process->amcAnnotatePDF()) {
        redirect(new moodle_url('annotating.php', array('a' => $quizz->id)));
    }
} else if ($action === 'setstudentaccess') {
    $quizz->studentaccess = optional_param('studentaccess', false, PARAM_BOOL);
    $quizz->corrigeaccess = optional_param('corrigeaccess', false, PARAM_BOOL);
    $quizz->save();
    redirect(new moodle_url('annotating.php', array('a' => $quizz->id)));
} else if ($action === 'notification') {
    $studentsto = $process->getUsersIdsHavingAnotatedSheets();
    $okSends = $process->sendAnotationNotification($studentsto);
    amc\FlashMessageManager::addMessage(
        ($okSends == count($studentsto)) ? 'success' : 'error',
        $okSends . " messages envoyés pour " . count($studentsto) . " étudiants ayant une copie annotée."
    );
    redirect(new moodle_url('annotating.php', array('a' => $quizz->id)));
}



// Output starts here
echo $output->header();


if ($process->hasAnotatedFiles()) {
    $url = $process->getFileUrl('cr/corrections/pdf/' . $process->normalizeFilename('corrections'));
    echo $OUTPUT->box_start('informationbox well');
    echo $OUTPUT->heading("Copies corrigées", 2)
        . $OUTPUT->heading("Fichiers", 3)
        . \html_writer::link($url, $process->normalizeFilename('corrections'), array('target' => '_blank'));
    echo "<p><b>" . $process->countIndividualAnotations() . "</b> copies individuelles annotées (pdf) disponibles.</p>";

    echo HtmlHelper::buttonWithAjaxCheck('Mettre à jour les copies corrigées (annotées)', $quizz->id, 'annotating', 'anotate', 'process');

    echo $OUTPUT->heading("Accès aux copies", 3);
    echo "<p>Permettre l'accès de chaque étudiant</p>\n";
    echo '<form action="?a=' . $quizz->id .'" method="post">' . "\n";
    echo '<ul>';
    $ckcopie = ($quizz->studentaccess ? 'checked="checked"' : '');
    $ckcorrige = ($quizz->corrigeaccess ? 'checked="checked"' : '');
    echo '<li><input type="checkbox" name="studentaccess" ' .$ckcopie. '>à sa copie corrigée annotée</input></li>' ;
    echo '<li><input type="checkbox" name="corrigeaccess" ' .$ckcorrige. '>au corrigé complet</input></li>' ;
    echo '</ul>';
    echo '<input type="hidden" name="action" value="setstudentaccess" value="1" />';
    echo '<button type="submit">Permettre ces accès</button>';
    echo '</form>';

    echo $OUTPUT->heading("Envoi des copies", 3);
    echo $OUTPUT->single_button(
        new moodle_url(
            '/mod/automultiplechoice/annotating.php',
            array('a' => $quizz->id, 'action' => 'notification')
        ),
        'Envoyer la correction par message Moodle à chaque étudiant',
        'post'
    );
    echo $OUTPUT->box_end();
} else {
    echo HtmlHelper::buttonWithAjaxCheck('Générer les copies corrigées (annotées)', $quizz->id, 'annotating', 'anotate', 'process');
}

echo $output->footer();
