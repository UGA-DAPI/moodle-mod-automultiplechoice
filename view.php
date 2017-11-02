<?php

/* @var $DB moodle_database */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */


require_once(__DIR__ . '/locallib.php');

global $OUTPUT, $PAGE, $CFG;

$controller = new \mod_automultiplechoice\local\controllers\view_controller();
$quiz = $controller->getQuiz();
$cm = $controller->getCm();
$course = $controller->getCourse();
$output = $controller->getRenderer('dashboard');
$process = new \mod_automultiplechoice\local\amc\process($quiz);

if (!count($quiz->questions)) {
    redirect(new moodle_url('questions.php', array('a' => $quiz->id)));
}

$PAGE->set_url('/mod/automultiplechoice/view.php', array('id' => $cm->id));
$PAGE->requires->js(
    new moodle_url('/mod/automultiplechoice/assets/scoringsystem.js')
);

$viewContext = $controller->getContext();
require_capability('mod/automultiplechoice:view', $viewContext);
if (!has_capability('mod/automultiplechoice:update', $viewContext) ) { // simple étudiant
    //$anotatedfile = "cr-".$USER->id.".pdf";
    $anotatedfile = $process->getUserAnotatedSheet($USER->idnumber);
    if ($quiz->studentaccess && $anotatedfile) {
        $PAGE->set_url('/mod/automultiplechoice/view.php', array('id' => $cm->id));
        echo $output->header();

        $url = $process->getFileUrl($anotatedfile);
        echo "<p>Vous avez une copie corrigée : " ;
        echo \html_writer::link($url, $anotatedfile, array('target' => '_blank')) . "</p>\n";

        if ($quiz->corrigeaccess) {
            $corrige = $process->normalizeFilename('corrige');
            $link = \html_writer::link($process->getFileUrl($corrige), $corrige, array('target' => '_blank'));
            echo "<p>Vous pouvez consulter le corrigé : " . $link . "</p>\n";
        }
        echo $output->footer();
    } else {
        echo $output->header();
        echo $output->heading("Vous n'avez pas de copie corrigée pour ce QCM");
        echo $output->footer();
    }
    return;
}

// Teacher or admin with editing capability

add_to_log($course->id, 'automultiplechoice', 'view', "view.php?id={$cm->id}", $quiz->name, $cm->id);

// Output starts here
echo $output->header();

if (!$quiz->validate()) {
    echo $OUTPUT->box_start('errorbox');
    echo '<p>' . get_string('someerrorswerefound') . '</p>';
    echo '<dl>';
    foreach ($quiz->errors as $field => $error) {
        $field = preg_replace('/^(.+)\[(.+)\]$/', '${1}_${2}', $field);
        echo "<dt>" . get_string($field, 'automultiplechoice') . "</dt>\n"
                . "<dd>" . get_string($error, 'automultiplechoice') . "</dd>\n";
    }
    echo "</dl>\n";
    echo $OUTPUT->box_end();
}

if ($quiz->isLocked()) {
    // cannot put a button if we use $OUTPUT->notification
    $unlockurl = new \moodle_url('documents.php', array('a' => $quiz->id, 'action' => 'unlock'));
    $unlockbutton= new \single_button($unlockurl, 'Déverrouiller (permettre les modifications du questionnaire)');
    $message = \mod_automultiplechoice\local\helpers\log::build($quiz->id)->check('unlock');
    if ($message){
        $unlockbutton->add_confirm_action(implode('\n',$message));
    }
    echo '<div class="informationbox notifyproblem alert alert-info">'
        . "Le questionnaire est actuellement verrouillé pour éviter les modifications entre l'impression et la correction."
        . " Vous pouvez accéder aux documents via l'onglet <em>Sujets</em>."
        . $OUTPUT->render($unlockbutton)
        . "</div>\n";
}

echo $OUTPUT->heading("1. " . get_string('settings'), 3);
\mod_automultiplechoice\local\helpers\html::printTableQuiz($quiz, array('instructions', 'description'));

echo $OUTPUT->heading("2. " . get_string('questions', 'question'), 3);
\mod_automultiplechoice\local\helpers\html::printTableQuiz($quiz, array('qnumber'));

echo $OUTPUT->heading("3. " . get_string('scoringsystem', 'automultiplechoice'), 3);
\mod_automultiplechoice\local\helpers\html::printTableQuiz($quiz, array('score', 'grademax', 'scoringset'));

echo $OUTPUT->heading("4. " . get_string('documents', 'automultiplechoice'), 3);
if ($quiz->isLocked()) {
    echo "<div>Les sujets sont prêts à être distribués.</div>\n";
    echo $process->getHtmlZipLink();
    echo $process->getHtmlPdfLinks();
    echo '<div>'
        . $OUTPUT->render($unlockbutton)
        . '</div>';
} else {
    if ( $quiz->hasDocuments() ) {
        echo "<div>Les sujets n'ont pas encore été figés mais les documents préparatoires sont disponibles.</div>\n";
        echo $process->getHtmlPdfLinks();
    } else {
        echo "<div>Aucun document n'est encore disponible.</div>\n";
    }
    $preparetime = $process->lastlog('prepare:pdf');
    if ($preparetime) {
        echo "<div>Dernière préparation des sujets PDF le " . $process::isoDate($preparetime) . "</div>\n";
    } else {
        echo "<div>Aucun sujet PDF n'a encore été préparé.</div>\n";
    }
}

echo $OUTPUT->heading("5. " . get_string('uploadscans', 'automultiplechoice'), 3);
$scans = $process->statScans();
if ($scans) {
    echo "<div>{$scans['count']} pages scannées ont été déposées le {$scans['timefr']}.</div>\n";
} else {
    echo "<div>Aucune copie n'a encore été déposée.</div>";
}
echo $OUTPUT->heading( "6. " . get_string('associating', 'automultiplechoice'), 3);


echo $OUTPUT->heading("7. " . get_string('grading', 'automultiplechoice'), 3);
if ($scans && $process->isGraded()) {
    echo $process->getHtmlStats();
} else {
    echo "<div>Aucune copie n'a encore été notée ou corrigée.</div>";
}
echo $OUTPUT->heading( "8. " . get_string('annotating', 'automultiplechoice'), 3);

echo $output->footer();
