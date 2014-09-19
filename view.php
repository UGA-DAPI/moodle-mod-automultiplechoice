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

require_once __DIR__ . '/locallib.php';

global $OUTPUT, $PAGE, $CFG;

$controller = new amc\Controller();
$quizz = $controller->getQuizz();
$cm = $controller->getCm();
$course = $controller->getCourse();
$output = $controller->getRenderer('dashboard');

if (!count($quizz->questions)) {
    redirect(new moodle_url('qselect.php', array('a' => $quizz->id)));
}

require_capability('mod/automultiplechoice:view', $controller->getContext());

add_to_log($course->id, 'automultiplechoice', 'view', "view.php?id={$cm->id}", $quizz->name, $cm->id);

// Output starts here
$PAGE->set_url('/mod/automultiplechoice/view.php', array('id' => $cm->id));
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('assets/scoring.js'));
$PAGE->requires->css(new moodle_url('assets/amc.css'));

echo $output->header();

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

echo $OUTPUT->heading("1. " . get_string('settings'), 3);
HtmlHelper::printTableQuizz($quizz, array('instructions', 'description'));

echo $OUTPUT->heading("2. " . get_string('questions', 'question'), 3);
HtmlHelper::printTableQuizz($quizz, array('qnumber'));

echo $OUTPUT->heading("3. " . get_string('scoringsystem', 'automultiplechoice'), 3);
HtmlHelper::printTableQuizz($quizz, array('score', 'scoringset'));

echo $OUTPUT->heading("4. " . get_string('documents', 'automultiplechoice'), 3);
/**
 * @todo Fill in
 */

echo $OUTPUT->heading("5. " . get_string('uploadscans', 'automultiplechoice'), 3);
/**
 * @todo Fill in
 */

echo $OUTPUT->heading("6. " . get_string('grading', 'automultiplechoice'), 3);
/**
 * @todo Fill in
 */




/**
 * @todo Filter what follows down to the footer.
 */
if ($quizz->isLocked()) {
    echo '<p class="warning">Le questionnaire est actuellement verrouillé pour éviter les modifications '
            . "entre l'impression et la correction. Vous pouvez accéder aux documents via le bouton "
            . "<em>[" . get_string('prepare', 'automultiplechoice') . "]</em>.</p>";
}
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
                    new moodle_url('/mod/automultiplechoice/documents.php', array('a' => $quizz->id)),
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
                    new moodle_url('/mod/automultiplechoice/documents.php', array('a' => $quizz->id)),
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

echo $output->footer();
