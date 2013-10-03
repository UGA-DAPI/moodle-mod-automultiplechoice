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
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/automultiplechoice:view', $context);

add_to_log($course->id, 'automultiplechoice', 'view', "view.php?id={$cm->id}", $quizz->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/automultiplechoice/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($quizz->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Output starts here
echo $OUTPUT->header();

if (!$quizz->validate()) {
    echo $OUTPUT->box_start('errorbox');
    echo '<dl>';
    foreach ($quizz->errors as $field => $error) {
        echo "<dt>" . get_string($field, 'automultiplechoice') . "</dt>\n"
                . "<dd>" . get_string($error, 'automultiplechoice') . "</dd>\n";
    }
    echo "</dl>\n";
    echo $OUTPUT->box_end();
}

echo $OUTPUT->box_start();

echo $OUTPUT->heading($quizz->name);
echo '<table class="flexible boxaligncenter generaltable">';
echo '<tbody>';
echo '<tr><th>' . get_string('description', 'automultiplechoice') . '</th><td>' . format_string($quizz->description) . '</td></tr>';
echo '<tr><th>' . get_string('comment', 'automultiplechoice') . '</th><td>' . format_string($quizz->comment) . '</td></tr>';
echo '</tbody></table>';

echo $OUTPUT->box_start();
echo $OUTPUT->heading("Questions", 3);

echo '<table class="flexible boxaligncenter generaltable">';
echo '<thead><tr><th>' . get_string('qtitle', 'automultiplechoice') . '</th><th>' . get_string('qscore', 'automultiplechoice') . '</th></tr></thead>';
echo '<tbody>';
foreach ($quizz->questions->getRecords() as $q) {
    echo '<tr><td>' . format_string($q->name) . '</td><td>' . $q->score . '</td></tr>';
}
echo '<tr><td></td><th>' . $quizz->score . '</th></tr>';
echo '</tbody></table>';

echo '<p>';
echo html_writer::link(
        new moodle_url('qselect.php', array('a' => $quizz->id)),
        get_string('editselection', 'automultiplechoice')
);
echo '</p>';


//***** Affiche les fichiers préparés (source et pdf)

$process = new \mod\automultiplechoice\AmcProcess($quizz);
$srcprepared = $process->lastlog('prepare:source');
if ($srcprepared) {
    echo "<p>Un fichier source préparé le " . $srcprepared . "</p>\n";
} else {
    echo "<p>Aucun fichier source préparé.\n";
}
$pdfprepared = $process->lastlog('prepare:pdf');
if ($pdfprepared) {
    echo "<p>Deux fichiers PDF préparés le " . $pdfprepared . "</p>\n";
} else {
    echo "<p>Aucun fichier PDF préparé.\n";
}


//******* Affiche les actions disponibles

// Main AMC actions and corresponding GUI labels
$actions = array(
    'prepare' => 'Préparation',
    'analyse' => 'Saisie',
    'note' => 'Notation',
    'export' => 'Rapports'
);

foreach ($actions as $action => $label) {
    $options = array('disabled' => 'disabled');
    if ($action == 'prepare') {
        $options = array();
    }
    $url = new moodle_url('/mod/automultiplechoice/' . $action. '.php', array('a' => $quizz->id));
    $button = $OUTPUT->single_button($url, $label , 'post', $options);
    echo $button;
}


echo $OUTPUT->box_end();
echo $OUTPUT->box_end();

echo $OUTPUT->footer();
