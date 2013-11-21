<?php

/**
 * Shows details of a particular instance of automultiplechoice
 *
 * @package    mod_automultiplechoice
 * @copyright  2013 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* @var $DB moodle_database */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

global $DB, $OUTPUT, $PAGE, $CFG;

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once("$CFG->libdir/formslib.php");
require_once(dirname(__FILE__).'/lib.php');
require_once __DIR__ . '/models/Quizz.php';
require_once __DIR__ . '/models/AmcProcess.php';
require_once __DIR__ . '/models/HtmlHelper.php';

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // automultiplechoice instance ID

if ($id) {
    $cm         = get_coursemodule_from_id('automultiplechoice', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $quizz = \mod\automultiplechoice\Quizz::findById($cm->instance);
} elseif ($a) {
    $quizz = \mod\automultiplechoice\Quizz::findById($a);
    $course     = $DB->get_record('course', array('id' => $quizz->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('automultiplechoice', $quizz->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

if (!count($quizz->questions)) {
    redirect(new moodle_url('qselect.php', array('a' => $quizz->id)));
}
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/automultiplechoice:view', $context);

add_to_log($course->id, 'automultiplechoice', 'view', "view.php?id={$cm->id}", $quizz->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/automultiplechoice/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($quizz->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('assets/scoring.js'));
$PAGE->requires->css(new moodle_url('assets/amc.css'));

// Output starts here
echo $OUTPUT->header();

if (!$quizz->validate()) {
    echo $OUTPUT->box_start('errorbox');
    echo '<p>' . get_string('someerrorswerefound') . '</p>';
    echo '<dl>';
    foreach ($quizz->errors as $field => $error) {
        $field = preg_replace('/^(.+)\[(.+)\]$/', '${1}_${2}', $field);
        echo "<dt>" . get_string($field, 'automultiplechoice') . "</dt>\n"
                . "<dd>" . get_string($error, 'automultiplechoice') . "</dd>\n";
    }
    echo "</dl>\n";
    echo $OUTPUT->box_end();
}

echo $OUTPUT->box_start();
echo $OUTPUT->heading($quizz->name);
if ($quizz->isLocked()) {
    echo '<p class="warning">Le questionnaire est actuellement verrouillé pour éviter les modifications '
            . "entre l'impression et la correction. Vous pouvez accéder aux documents via le bouton <em>Génération et visualisation</em>."
            . "</p>";
}
HtmlHelper::printTableQuizz($quizz);
echo '<p class="continuebutton">';
echo html_writer::link(
        new moodle_url('/course/modedit.php', array('update' => $cm->id, 'return' => 1)),
        get_string('editsettings')
);
echo '</p>';

// Questions
echo $OUTPUT->box_start();
echo $OUTPUT->heading(
        html_writer::link(new moodle_url('qselect.php', array('a' => $quizz->id)), "Questions"),
        3
);
HtmlHelper::printFormFullQuestions($quizz);
if (!$quizz->isLocked()) {
    echo '<p class="continuebutton">';
    echo html_writer::link(
            new moodle_url('qselect.php', array('a' => $quizz->id)),
            get_string('editselection', 'automultiplechoice')
    );
    echo '</p>';
}
echo $OUTPUT->box_end();

echo $OUTPUT->box_start();


// Display available actions
echo '<ul id="main-actions">';
echo "<li>";
if (empty($quizz->errors)) {
    $options = array();
} else {
    echo '<p>' . get_string('functiondisabled') . '</p>';
    $options = array('disabled' => 'disabled');
}
$url = new moodle_url('/mod/automultiplechoice/prepare.php', array('a' => $quizz->id));
echo $OUTPUT->single_button($url, get_string('prepare', 'automultiplechoice') , 'get', $options);

// Display prepared files (source & pdf)
$process = new \mod\automultiplechoice\AmcProcess($quizz);
echo $process->statPrepare();
echo "</li>";

if ($quizz->isLocked()) {
    echo '<li>'
        . $OUTPUT->single_button(
                new moodle_url('/mod/automultiplechoice/prepare.php', array('a' => $quizz->id, 'unlock' => 1)),
                'Déverrouiller (permettre les modifications du questionnaire)', 'post'
        )
        . '</li>';
} else {
    echo '<li>'
        . $OUTPUT->single_button(
                new moodle_url('/mod/automultiplechoice/prepare.php', array('a' => $quizz->id, 'lock' => 1)),
                'Préparer les documents à imprimer et verrouiller le questionnaire', 'post'
        )
        . '</li>';
}


if (!$quizz->isLocked()) {
    $options = array('disabled' => 'disabled');
}

echo "<li>";
$url = new moodle_url('/mod/automultiplechoice/scan.php', array('a' => $quizz->id));
echo $OUTPUT->single_button($url, get_string('analyse', 'automultiplechoice') , 'post', $options);
echo $process->statScans();
echo "</li>";

echo "<li>";
$url = new moodle_url('/mod/automultiplechoice/note.php', array('a' => $quizz->id, 'action' => 'note'));
echo $OUTPUT->single_button($url, get_string('note', 'automultiplechoice') , 'post', $options);
echo "</li>";

echo "</ul>";


echo $OUTPUT->box_end();

echo $OUTPUT->box_end(); // Quizz

echo $OUTPUT->footer();
