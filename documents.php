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

require_capability('mod/automultiplechoice:view', $controller->getContext());

$lock = optional_param('lock', false, PARAM_BOOL);
$unlock = optional_param('unlock', false, PARAM_BOOL);
if ($lock) {
    $quizz->amcparams->locked = true;
    $quizz->save();
} else if ($unlock) {
    $quizz->amcparams->locked = false;
    $quizz->save();
    redirect(new moodle_url('documents.php', array('a' => $quizz->id)));
}

$PAGE->set_url('/mod/automultiplechoice/documents.php', array('id' => $cm->id));

$PAGE->requires->css(new moodle_url('assets/amc.css'));
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('assets/async.js'));

echo $output->header();

$process = new amc\AmcProcessPrepare($quizz);

if ($quizz->isLocked()) {
    echo $OUTPUT->heading("Fichiers PDF précédemment créés", 3);
    echo $process->htmlPdfLinks();
    if ($lock) {
        echo <<<EOL
    <div class="async-load" data-url="ajax/prepare.php">
        <div class="async-target" data-parameters='{"a": {$quizz->id}, "action": "zip"}'>
            Préparation de l'archive zip <span />
       </div>
    </div>
EOL;
    } else {
        echo $OUTPUT->heading("Archive zip", 3);
        echo $process->htmlZipLink();
    }
    echo '<div>'
        . $OUTPUT->single_button(
                new moodle_url('/mod/automultiplechoice/documents.php', array('a' => $quizz->id, 'unlock' => 1)),
                'Déverrouiller (permettre les modifications du questionnaire)', 'post'
        )
        . '</div>';
} else {
    ?>
    <div class="async-load" data-url="ajax/prepare.php">
        <div class="async-target" data-parameters='{"a": <?php echo $quizz->id; ?>, "action": "prepare"}'>
            Préparation des fichiers PDF <span />
        </div>
        <div class="async-post-load">
            <button type="button" onclick="asyncReloadComponents();">Actualiser les documents</button>
            <?php
            echo $OUTPUT->single_button(
                new moodle_url('/mod/automultiplechoice/documents.php', array('a' => $quizz->id, 'lock' => 1)),
                'Préparer les documents à imprimer et verrouiller le questionnaire', 'post'
            );
            ?>
        </div>
    </div>
    <?php
}

echo $output->footer();
