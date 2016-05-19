<?php

/**
 * Prepare the 2 pdf files (sujet + corrigé) and let the user download them
 *
 * @package    mod_automultiplechoice
 * @copyright  2013 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \mod\automultiplechoice as amc;

require_once __DIR__ . '/locallib.php';
require_once __DIR__ . '/models/AmcProcessPrepare.php';

global $DB, $OUTPUT, $PAGE;
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$controller = new amc\Controller();
$quizz = $controller->getQuizz();
$cm = $controller->getCm();
$course = $controller->getCourse();
$output = $controller->getRenderer('documents');

require_capability('mod/automultiplechoice:update', $controller->getContext());

$action = optional_param('action', '', PARAM_ALPHA);
if ($action === 'lock') {
    $quizz->amcparams->locked = true;
    amc\Log::build($quizz->id)->write('lock');
    $quizz->save();
} else if ($action === 'unlock') {
    $quizz->amcparams->locked = false;
    $quizz->save();
    redirect(new moodle_url('documents.php', array('a' => $quizz->id)));
} else if ($action === 'prepare') {
    array_map('unlink', glob($quizz->getDirName() . '/sujet*'));
    redirect(new moodle_url('documents.php', array('a' => $quizz->id)));
} else if ($action === 'randomize') {
    $quizz->amcparams->randomize();
    $quizz->save();
    array_map('unlink', glob($quizz->getDirName() . '/sujet*'));
    redirect(new moodle_url('documents.php', array('a' => $quizz->id)));
}

$PAGE->set_url('/mod/automultiplechoice/documents.php', array('id' => $cm->id));

$PAGE->requires->css(new moodle_url('assets/amc.css'));

echo $output->header();

$process = new amc\AmcProcessPrepare($quizz);

if ($quizz->isLocked()) {
    echo '<div class="informationbox notifyproblem alert alert-info">'
        . "Le questionnaire est actuellement verrouillé pour éviter les modifications entre l'impression et la correction."
        . HtmlHelper::buttonWithAjaxCheck('Déverrouiller (permettre les modifications du questionnaire)', $quizz->id, 'documents', 'unlock', 'unlock')
        . "</div>\n";

    echo $OUTPUT->heading("Fichiers PDF précédemment créés", 3);
    echo $process->getHtmlPdfLinks();
    if ($action == 'lock') {
        echo <<<EOL
    <div class="async-load" data-url="ajax/prepare.php">
        <div class="async-target" data-parameters='{"a": {$quizz->id}, "action": "zip"}'>
            Préparation de l'archive zip <span />
       </div>
    </div>
EOL;
    } else {
        echo $OUTPUT->heading("Archive zip", 3);
        echo $process->getHtmlZipLink();
    }
} else {
    foreach (amc\Log::build($quizz->id)->check('pdf') as $warning) {
        echo $OUTPUT->notification($warning, 'notifyproblem');
    }

    if ($quizz->hasDocuments() ) {
        ?>
        <div>
            <div>
                <?php
                echo $process->getHtmlPdfLinks();
                ?>
            </div>
            <div>
            <?php
                echo HtmlHelper::buttonWithAjaxCheck('Actualiser les documents', $quizz->id, 'documents', 'prepare', '');
                echo $OUTPUT->single_button(
                    new moodle_url('/mod/automultiplechoice/documents.php', array('a' => $quizz->id, 'action' => 'randomize')),
                    'Mélanger questions et réponses', 'post'
                );
    } else {
        ?>
        <div class="async-load" data-url="ajax/prepare.php">
            <div class="async-target" data-parameters='{"a": <?php echo $quizz->id; ?>, "action": "prepare"}'>
                Préparation des fichiers PDF <span />
            </div>
            <div class="async-post-load">
            <?php
                echo HtmlHelper::buttonWithAjaxCheck('Actualiser les documents', $quizz->id, 'documents', 'prepare', '');
                echo HtmlHelper::buttonWithAjaxCheck('Mélanger questions et réponses', $quizz->id, 'documents', 'randomize', '');
            }
            echo HtmlHelper::buttonWithAjaxCheck('Préparer les documents à imprimer et verrouiller le questionnaire', $quizz->id, 'documents', 'lock', '');
            ?>
            </div>
        </div>
<?php
}

echo $output->footer();
