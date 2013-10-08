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
require_once __DIR__ . '/models/Quizz.php';
require_once __DIR__ . '/models/AmcProcess.php';

$a  = optional_param('a', 0, PARAM_INT);  // automultiplechoice instance ID

if ($a) {
    $quizz = \mod\automultiplechoice\Quizz::findById($a);
    $course     = $DB->get_record('course', array('id' => $quizz->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('automultiplechoice', $quizz->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/automultiplechoice:view', $context);

/// Print the page header

$PAGE->set_url('/mod/automultiplechoice/prepare.php', array('id' => $cm->id));
$PAGE->set_title(format_string($quizz->name . " - préparation des fichiers"));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Output starts here
echo $OUTPUT->header();
echo $OUTPUT->heading($quizz->name . " - préparation des fichiers");



$process = new \mod\automultiplechoice\AmcProcess($quizz);
//var_dump($process);

$diag = $process->saveAmctxt();
if ($diag) {
    echo "<p>Fichier source enregistré.</p>\n";
} else {
    echo "<p>Erreur sur fichier source.</p>\n";
}

$diag = $process->createPdf();
if ($diag) {
    echo $OUTPUT->heading("Fichiers PDF créés");
    echo '<ul class="amc-files">';
    $url = moodle_url::make_pluginfile_url($context->id, 'mod_automultiplechoice', '', NULL,
        $process->relworkdir.'/', 'prepare-sujet.pdf');
    echo "<li>" . html_writer::link($url, 'prepare-sujet.pdf') . "</li>";

    $url = moodle_url::make_pluginfile_url($context->id, 'mod_automultiplechoice', '', NULL,
        $process->relworkdir.'/', 'prepare-corrige.pdf');
    echo "<li>" . html_writer::link($url, 'prepare-corrige.pdf') . "</li>";

    $url = moodle_url::make_pluginfile_url($context->id, 'mod_automultiplechoice', '', NULL,
        $process->relworkdir.'/', 'prepare-catalog.pdf');
    echo "<li>" . html_writer::link($url, 'prepare-catalog.pdf') . "</li>";
    echo "</ul>\n";
} else {
    echo "<p>Erreur lors de la création des fichiers PDF.</p>\n";
}


$url = new moodle_url('/mod/automultiplechoice/view.php', array('a' => $quizz->id));
$button = $OUTPUT->single_button($url, 'Retour questionnaire', 'post');
echo $button;
