<?php


/**
 * Shows details of a particular instance of automultiplechoice
 * Renders the first view for an automultiplechoice module detail (ie automultiplechoice Dashboard)
 */
/* @var $DB moodle_database */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

require_once(__DIR__ . '/locallib.php');

global $DB, $OUTPUT, $PAGE, $CFG;
// Not usefull any more
//$controller = new \mod_automultiplechoice\local\controllers\view_controller();
// Read GET parameters.
// Course_module ID.
$id = \optional_param('id', 0, PARAM_INT);
// Automultiplechoice instance ID.
$a  = \optional_param('a', 0, PARAM_INT);
// View that we have to display.
$page = \optional_param('page', 'dashboard', PARAM_TEXT);
$PAGE->set_url('/mod/automultiplechoice/view.php', array('id' => $cm->id));
// Retrieve quiz, course and coursemodule from parameters.
if ($id) {
    $cm = \get_coursemodule_from_id('automultiplechoice', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $quiz = \mod_automultiplechoice\local\models\quiz::findById($cm->instance);
} else if ($a) {
    $quiz = \mod_automultiplechoice\local\models\quiz::findById($a);
    $course = $DB->get_record('course', array('id' => $quiz->course), '*', MUST_EXIST);
    $cm = \get_coursemodule_from_instance('automultiplechoice', $quiz->id, $course->id, false, MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');die;
}

// get renderer in classes/output/renderer.php
$output = $PAGE->get_renderer('mod_automultiplechoice');

$process = new  \mod_automultiplechoice\local\amc\process($quiz);
if (!count($quiz->questions)) {
    // @TODO call appropriate renderer
    die('no questions');
}
$viewcontext = \context_module::instance($cm->id);
require_capability('mod/automultiplechoice:view', $viewcontext);
// Output starts here.
echo $output->header();
// Simple student.
if (!has_capability('mod/automultiplechoice:update', $viewcontext)) {
    $studentview = new \mod_automultiplechoice\output\student_view($quiz, $process, $USER);
    echo $output->render_student_view($studentview);
    echo $output->footer();
    return;
}
// Teacher or admin with editing capability.
// Add a log.
add_to_log($course->id, 'automultiplechoice', 'view', "view.php?id={$cm->id}", $quiz->name, $cm->id);


// Errors. (in the main moodle template?).
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


// Unlock button. In the main moodle template ?
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


// The current page.
// One among dashboard, annotate, grade, scans, associate, documents, scoring, questions.
// Settings are handled by mod_form.php.
if ($page === 'dashboard') {
    $tabs = new \mod_automultiplechoice\output\tabs($quiz, $viewcontext, $cm, $page);
    $dashboard = new \mod_automultiplechoice\output\dashboard($quiz, $process);
    echo $output->render_dashboard($dashboard, $tabs->export_for_template($output));
} else if ($page === 'questions') {
    require_capability('mod/automultiplechoice:addinstance', $viewcontext);
    $questionssubmitted = \mod_automultiplechoice\local\models\question_list::fromForm('question');
    if ($questionssubmitted) {
        $quiz->questions = $questionssubmitted;
        if ($quiz->save()) {
            \mod_automultiplechoice\local\amc\logger::build($quiz->id)->write('saving');
        } else {
            die("Could not save into automultiplechoice");
        }
    }
    // Remove deleted questions.
    $quiz->validate();

    // All available questions for the connected user... Module questions only
    $availablequestions = automultiplechoice_list_questions($USER, $course);
    $tabs = new \mod_automultiplechoice\output\tabs($quiz, $viewcontext, $cm, $page);
    $questionspage = new \mod_automultiplechoice\output\questions($quiz, $process, $quiz->questions, $availablequestions);
    echo $output->render_questions($questionspage, $tabs->export_for_template($output));
} else if ($page === 'scoring') {
    $settings = new \mod_automultiplechoice\output\dashboard($quiz, $process);
    echo $output->render_dashboard($dashboard);
} else if ($page === 'documents') {
    $settings = new \mod_automultiplechoice\output\dashboard($quiz, $process);
    echo $output->render_dashboard($dashboard);
} else if ($page === 'scans') {
    $settings = new \mod_automultiplechoice\output\dashboard($quiz, $process);
    echo $output->render_dashboard($dashboard);
} else if ($page === 'associate') {
    $settings = new \mod_automultiplechoice\output\dashboard($quiz, $process);
    echo $output->render_dashboard($dashboard);
} else if ($page === 'grade') {
    $settings = new \mod_automultiplechoice\output\dashboard($quiz, $process);
    echo $output->render_dashboard($dashboard);
} else if ($page === 'annotate') {
    $settings = new \mod_automultiplechoice\output\dashboard($quiz, $process);
    echo $output->render_dashboard($dashboard);
} else {
    return;
}
echo $output->footer();