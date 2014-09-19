<?php

/**
 * Prepare the 2 pdf files (sujet + corrigé) and let the user download them
 *
 * @package    mod_automultiplechoice
 * @copyright  2013 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* @var $DB moodle_database */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

global $DB, $OUTPUT, $PAGE;

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once __DIR__ . '/models/Quizz.php';
require_once __DIR__ . '/models/AmcProcessPrepare.php';

$a  = optional_param('a', 0, PARAM_INT);  // automultiplechoice instance ID
$lock = optional_param('lock', false, PARAM_BOOL);
$unlock = optional_param('unlock', false, PARAM_BOOL);

if ($a) {
    $quizz = \mod\automultiplechoice\Quizz::findById($a);
    $course     = $DB->get_record('course', array('id' => $quizz->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('automultiplechoice', $quizz->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/automultiplechoice:view', $context);

if ($lock) {
    $quizz->amcparams->locked = true;
    $quizz->save();
} else if ($unlock) {
    $quizz->amcparams->locked = false;
    $quizz->save();
    redirect(new moodle_url('view.php', array('a' => $quizz->id)));
}

$PAGE->set_context($context);
$PAGE->set_url('/mod/automultiplechoice/documents.php', array('id' => $cm->id));
$PAGE->set_title(format_string($quizz->name . " - préparation des fichiers"));
$PAGE->set_heading(format_string($course->fullname));

$PAGE->requires->css(new moodle_url('assets/amc.css'));
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('assets/async.js'));

echo $OUTPUT->header();
echo $OUTPUT->heading($quizz->name . " - fichiers PDF");

$process = new \mod\automultiplechoice\AmcProcessPrepare($quizz);

if ($quizz->isLocked()) {
    echo "<h3>Fichiers PDF précédemment créés</h3>";
    echo $process->htmlPdfLinks();
    if ($lock) {
        echo <<<EOL
    <div class="async-load" data-url="ajax/prepare.php">
        <div class="async-target" data-parameters='{"a": {$quizz->id}, "action": "zip"}'>
            Préparation de l'archive Zip <span />
       </div>
    </div>
    <noscript>
    TODO
    </noscript>
EOL;
    } else {
        echo "<h3>Archive Zip</h3>";
        echo $process->htmlZipLink();
    }
    echo '<div>'
        . $OUTPUT->single_button(
                new moodle_url('/mod/automultiplechoice/documents.php', array('a' => $quizz->id, 'unlock' => 1)),
                'Déverrouiller (permettre les modifications du questionnaire)', 'post'
        )
        . '</div>';
} else {
    echo <<<EOL
    <div class="async-load" data-url="ajax/prepare.php">
        <div class="async-target" data-parameters='{"a": {$quizz->id}, "action": "prepare"}'>
            Préparation des fichiers PDF <span />
       </div>
    </div>
    <noscript>
    TODO : form and submit button that posts to ajax/prepare.php with a redirect option on.
    </noscript>
EOL;
    echo '<div>'
        . $OUTPUT->single_button(
                new moodle_url('/mod/automultiplechoice/documents.php', array('a' => $quizz->id, 'lock' => 1)),
                'Préparer les documents à imprimer et verrouiller le questionnaire', 'post'
        )
        . '</div>';
}

echo button_back_to_activity($quizz->id);

echo $OUTPUT->footer();
