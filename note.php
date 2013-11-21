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
require_once __DIR__ . '/models/AmcProcessGrade.php';

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
$context = context_module::instance($cm->id);
require_capability('mod/automultiplechoice:view', $context);

/// Print the page header

$PAGE->set_url('/mod/automultiplechoice/note.php', array('id' => $cm->id));
$PAGE->set_title(format_string($quizz->name . " - notation"));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$PAGE->requires->css(new moodle_url('assets/amc.css'));

// Output starts here
echo $OUTPUT->header();
echo $OUTPUT->heading($quizz->name . " - notation");

$process = new \mod\automultiplechoice\AmcProcessGrade($quizz);
//var_dump($process);

if ($action == 'note') { // On arrive de la page générale view.php
    $diag = $process->amcPrepareBareme();
    if ($diag) {
        echo "<p>Barème extrait.</p>\n";
    } else {
        echo "<p>Erreur sur l'extraction du barème.</p>\n";
    }

    $diag = $process->amcNote();
    if ($diag) {
        echo "<p>Notes calculées.</p>\n";
    } else {
        echo "<p>Erreur sur le calcul des notes.</p>\n";
    }

    $diag = $process->amcExport();
    $urls = array();
    if ($diag) {
        echo $OUTPUT->heading("Fichier CSV créé");
        $urls['scores.csv'] = $process->getFileUrl(mod\automultiplechoice\AmcProcessGrade::PATH_AMC_CSV);
    } else {
        echo "<p>Erreur lors de l'export CSV des notes.</p>\n";
    }

    if ($process->writeFileWithIdentifiedStudents()) {
        $urls['scores_names'] = $process->getFileUrl(mod\automultiplechoice\AmcProcessGrade::PATH_FULL_CSV);
        list($grades, $count) = $process->readMarks();
        echo "<p>" . $count['known'] . " copies identifiées et " . $count['unknown'] . " non identifiées. </p>";
        var_dump($grades);
    } else {
        error("Could not create CSV file with identified students.");
    }

    echo '<ul class="amc-files">';
    foreach ($urls as $name => $url) {
        echo "<li>" . html_writer::link($url, $name) . "</li>";
    }
    echo "</ul>\n";
}


if ( isset($_POST['submit']) && $_POST['submit'] == 'Annotations' ) {
    $diag = $process->amcAnnotePdf();
    if ($diag) {
        echo "Fichier PDF créé : ";
        $url = $url = $process->getFileUrl('cr/corrections/pdf/corrections_tous.pdf');
        echo html_writer::link($url, 'corrections_tous.pdf') . "\n";
    } else {
        echo "<p>Erreur lors de la création du PDF.</p>";
    }

} else {
    // Bouton imprimer
    echo '<form action="note.php?a='. $quizz->id .'" method="post">' . "\n";
    //echo '<label for="compose">Copies composées</label>'. "\n" ;
    //echo '<input type="checkbox" name="compose" id="compose">' . "<br />\n" ;
    echo '<label for="submit">Télécharger copies annotées </label>' ;
    echo '<input type="submit" name="submit" value="Annotations">'. "\n" ;
    echo '</form>' . "\n" ;
}


\automultiplechoice_update_grades($DB->get_record('automultiplechoice', array('id' => $quizz->id), '*'));

echo button_back_to_activity($quizz->id);

echo $OUTPUT->footer();
