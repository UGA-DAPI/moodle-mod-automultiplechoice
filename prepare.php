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
require_once __DIR__ . '/models/AmcProcessPrepare.php';

$a  = optional_param('a', 0, PARAM_INT);  // automultiplechoice instance ID
$action = optional_param('action', '', PARAM_ALPHANUMEXT);

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
//var_dump($_POST);


$process = new \mod\automultiplechoice\AmcProcessPrepare($quizz);
//var_dump($process);

if ($action == 'prepare') {
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
        $url = $process->getFileUrl('prepare-sujet.pdf');
        echo "<li>" . html_writer::link($url, 'prepare-sujet.pdf') . "</li>";

        $url = $url = $process->getFileUrl('prepare-corrige.pdf');
        echo "<li>" . html_writer::link($url, 'prepare-corrige.pdf') . "</li>";

        $url = $url = $process->getFileUrl('prepare-catalog.pdf');
        echo "<li>" . html_writer::link($url, 'prepare-catalog.pdf') . "</li>";
        echo "</ul>\n";
    } else {
        echo "<p>Erreur lors de la création des fichiers PDF.</p>\n";
    }

    $diag = $process->amcMeptex();
    if ($diag) {
        echo $OUTPUT->heading("Mise en page (amc meptex) terminée.");
    } else {
        echo "<p>Erreur lors du calcul de mise en page (amc meptex).</p>\n";
    }
}


if ( isset($_POST['submit']) && $_POST['submit'] == 'zip' ) {
    $diag = $process->printAndZip(isset($_POST['split']));
    if ($diag) {
        echo "Fichier Zip créé : ";
        $url = $url = $process->getFileUrl('sujets.zip');
        echo html_writer::link($url, 'sujets.zip') . "\n";
    } else {
        echo "<p>Erreur lors de la création de l'archive.</p>";
    }

} else {
    // Bouton imprimer
    echo '<form action="prepare.php?a='. $quizz->id .'" method="post">' . "\n";
    echo '<label for="split">Feuilles réponses séparées</label>'. "\n" ;
    echo '<input type="checkbox" name="split" id="split">' . "<br />\n" ;
    echo '<label for="submit">Télécharger archive zip</label>' ;
    echo '<input type="submit" name="submit" value="zip">'. "\n" ;
    echo '</form>' . "\n" ;

}

echo "<p></p>";
$url = new moodle_url('/mod/automultiplechoice/view.php', array('a' => $quizz->id));
$button = $OUTPUT->single_button($url, 'Retour questionnaire', 'post');
echo $button;
