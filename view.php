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

use \mod\automultiplechoice as amc;

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
    $quizz = amc\Quizz::findById($cm->instance);
} elseif ($a) {
    $quizz = amc\Quizz::findById($a);
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
            . "entre l'impression et la correction. Vous pouvez accéder aux documents via le bouton "
            . "<em>[" . get_string('prepare', 'automultiplechoice') . "]</em>.</p>";
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
$optionsNormal = array();
$optionsDisabled = array('disabled' => 'disabled');
$process = new amc\AmcProcess($quizz);
?>
<div id="main-actions" class="checklock" data-checklockid="<?php echo $quizz->id; ?>">
    <ul class="amc-process">
        <li data-check="process">
            <?php
            echo $OUTPUT->single_button(
                    new moodle_url('/mod/automultiplechoice/prepare.php', array('a' => $quizz->id)),
                    get_string('prepare', 'automultiplechoice'),
                    'get',
                    empty($quizz->errors) ? $optionsNormal : $optionsDisabled
            );
            displayPrepareInfo($quizz, $process);
            //displayLockButton($quizz);
            ?>
        </li>
        <li class="amc-process-next"></li>
        <li>
            <?php
            echo $OUTPUT->single_button(
                    new moodle_url('/mod/automultiplechoice/prepare.php', array('a' => $quizz->id)),
                    get_string('prepare-locked', 'automultiplechoice'),
                    'get',
                    empty($quizz->errors) && $quizz->isLocked() ? $optionsNormal : $optionsDisabled
            );
            ?>
        </li>
    </ul>
    <ul class="amc-process">
        <li data-check="process,pdf">
            <?php
            echo $OUTPUT->single_button(
                    new moodle_url('/mod/automultiplechoice/scan.php', array('a' => $quizz->id)),
                    get_string('analyse', 'automultiplechoice') ,
                    'post',
                    empty($quizz->errors) && $quizz->isLocked() ? $optionsNormal : $optionsDisabled
            );
            $scans = $process->statScans();
            displayScanInfo($scans);
            ?>
        </li>
        <li class="amc-process-next"></li>
        <li data-check="process,pdf,upload">
            <?php
            echo $OUTPUT->single_button(
                    new moodle_url('/mod/automultiplechoice/note.php', array('a' => $quizz->id, 'action' => 'note')),
                    get_string('note', 'automultiplechoice'),
                    'post',
                    empty($quizz->errors) && $quizz->isLocked() && $scans ? $optionsNormal : $optionsDisabled
            );
            //displayGradesInfo();
            ?>
        </li>
    </ul>
</div>
<?php


echo $OUTPUT->box_end();

echo $OUTPUT->box_end(); // Quizz

echo $OUTPUT->footer();


// helper functions

function displayPrepareInfo($quizz, $process) {
    $preparetime = $process->lastlog('prepare:source');
    if ($preparetime) {
        echo "<div>Dernière préparation des fichiers PDF le " . amc\AmcProcess::isoDate($preparetime) . "</div>\n";
    } else {
        echo "<div>Aucun fichier PDF préparé.</div>\n";
    }
}

function displayLockButton($quizz) {
    global $OUTPUT;
    if (empty($quizz->errors)) {
        if ($quizz->isLocked()) {
            echo $OUTPUT->single_button(
                    new moodle_url('/mod/automultiplechoice/prepare.php', array('a' => $quizz->id, 'unlock' => 1)),
                    'Déverrouiller (permettre les modifications du questionnaire)', 'post'
            );
        } else {
            echo $OUTPUT->single_button(
                        new moodle_url('/mod/automultiplechoice/prepare.php', array('a' => $quizz->id, 'lock' => 1)),
                        'Préparer les documents à imprimer et verrouiller le questionnaire', 'post'
                );
        }
    } else {
        echo 'Préparer et verrouiller. ' . get_string('functiondisabled');
    }
}

function displayScanInfo($scans) {
    if ($scans) {
        echo "<div>{$scans['count']} pages scannées ont été déposées le {$scans['timefr']}.</div>\n";
    } else {
        echo "<div>Pas de copies déposées. La notation est donc désactivée.</div>";
    }
}

function displayGradeInfo() {
    $gradetime = $process->lastlog('note');
    if ($gradetime) {
        echo "<div>Correction des copies déjà effectuée le " . amc\AmcProcess::isoDate($gradetime) . "</div>\n";
    }
}
