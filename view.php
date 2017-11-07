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

    $anotatedfile = $process->getUserAnotatedSheet($USER->idnumber);
    if ($quiz->studentaccess && $anotatedfile) {
        $PAGE->set_url('/mod/automultiplechoice/view.php', array('id' => $cm->id));
        echo $output->header();

        $url = $process->getFileUrl($anotatedfile);
        echo '<p>';
        echo get_string('studentview_one_corrected_sheet', 'mod_automultiplechoice');
        echo '</p>';
        echo \html_writer::link($url, $anotatedfile, array('target' => '_blank')) . "</p>";

        if ($quiz->corrigeaccess) {
            $corrige = $process->normalizeFilename('corrige');
            $link = \html_writer::link($process->getFileUrl($corrige), $corrige, array('target' => '_blank'));
            echo '<p>';
            echo get_string('studentview_view_corrected_sheet', 'mod_automultiplechoice');
            echo $link;
            echo '</p>';
        }
        echo $output->footer();
    } else {
        echo $output->header();
        echo $output->heading(get_string('studentview_no_corrected_sheet', 'mod_automultiplechoice'));
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
    $unlockbutton = new \single_button($unlockurl, 'Déverrouiller (permettre les modifications du questionnaire)');
    $message = \mod_automultiplechoice\local\helpers\log::build($quiz->id)->check('unlock');
    if ($message) {
        $unlockbutton->add_confirm_action(implode('\n', $message));
    }
    echo '<div class="informationbox notifyproblem alert alert-info">'
        . get_string('quiz_is_locked', 'mod_automultiplechoice')
        . get_string('access_documents', 'mod_automultiplechoice')
        . '<em>'.get_string('documents', 'mod_automultiplechoice').'</em>'
        . $OUTPUT->render($unlockbutton)
        . "</div>";
}

echo $OUTPUT->heading("1. " . get_string('settings'), 3);
\mod_automultiplechoice\local\helpers\html::printTableQuiz($quiz, array('instructions', 'description'));

echo $OUTPUT->heading("2. " . get_string('questions', 'question'), 3);
\mod_automultiplechoice\local\helpers\html::printTableQuiz($quiz, array('qnumber'));

echo $OUTPUT->heading("3. " . get_string('scoringsystem', 'automultiplechoice'), 3);
\mod_automultiplechoice\local\helpers\html::printTableQuiz($quiz, array('score', 'grademax', 'scoringset'));

echo $OUTPUT->heading("4. " . get_string('documents', 'automultiplechoice'), 3);
if ($quiz->isLocked()) {
    echo '<div>';
    echo get_string('subjects_ready_for_distribution', 'mod_automultiplechoice');
    echo '</div>';
    echo $process->getHtmlZipLink();
    echo $process->getHtmlPdfLinks();
    echo '<div>'
        . $OUTPUT->render($unlockbutton)
        . '</div>';
} else {
    if ( $quiz->hasDocuments() ) {
        echo '<div>';
        echo get_string('preparatory_documents_ready', 'mod_automultiplechoice');
        echo '</div>';
        echo $process->getHtmlPdfLinks();
    } else {
        echo '<div>';
        echo get_string('no_document_available', 'mod_automultiplechoice');
        echo '</div>';
    }
    $preparetime = $process->lastlog('prepare:pdf');
    if ($preparetime) {
        echo '<div>';
        echo get_string('pdf_last_prepare_date', 'mod_automultiplechoice', ['date' => $process::isoDate($preparetime)]);
        echo '</div>';
    } else {
        echo '<div>';
        echo get_string('pdf_none_prepared', 'mod_automultiplechoice', ['date' => $process::isoDate($preparetime)]);
        echo '</div>';
    }
}

echo $OUTPUT->heading("5. " . get_string('uploadscans', 'automultiplechoice'), 3);
$scans = $process->statScans();
if ($scans) {
    echo '<div>';
    echo get_string('dashboard_nb_page_scanned', 'mod_automultiplechoice', ['nbpage' => $scans['count'], 'date' => $scans['timefr']]);
    echo '</div>';
} else {
    echo '<div>';
    echo get_string('uploadscans_no_sheets_uploaded', 'mod_automultiplechoice');
    echo '</div>';
}
echo $OUTPUT->heading( "6. " . get_string('associating', 'automultiplechoice'), 3);


echo $OUTPUT->heading("7. " . get_string('grading', 'automultiplechoice'), 3);
if ($scans && $process->isGraded()) {
    echo $process->getHtmlStats();
} else {
    echo '<div>';
    echo get_string('dashboard_no_sheets_corrected', 'mod_automultiplechoice');
    echo '</div>';
}
echo $OUTPUT->heading( "8. " . get_string('annotating', 'automultiplechoice'), 3);

echo $output->footer();
