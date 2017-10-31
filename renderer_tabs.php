<?php
/**
 * @package    mod_automultiplechoice
 * @copyright  2014 Silecs <http://wwww.silecs.info>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* @var $OUTPUT core_renderer */
global $OUTPUT;

/* @var $quiz mod\automultiplechoice\Quizz */
/* @var $cm \stdClass */

$tabs = array(
    new tabobject(
        'dashboard',
        new moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/view.php?a={$quiz->id}"),
        get_string('dashboard', 'automultiplechoice')
    ),
    new tabobject(
        'settings',
        new moodle_url("{$CFG->wwwroot}/course/modedit.php?update={$cm->id}"),
        "1. " . get_string('settings')
    ),
    new tabobject(
        'questions',
        new moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/questions.php?a={$quiz->id}"),
        "2. " . get_string('questions', 'question')
    ),
    new tabobject(
        'scoringsystem',
        new moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/scoringsystem.php?a={$quiz->id}"),
        "3. " . get_string('scoringsystem', 'automultiplechoice')
    ),
    new tabobject(
        'documents',
        new moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/documents.php?a={$quiz->id}"),
        "4. " . get_string('documents', 'automultiplechoice')
    ),
    new tabobject(
        'uploadscans',
        new moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/uploadscans.php?a={$quiz->id}"),
        "5. " . get_string('uploadscans', 'automultiplechoice')
    ),
    new tabobject(
        'grading',
        new moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/grading.php?a={$quiz->id}"),
        "6. " . get_string('grading', 'automultiplechoice')
    ),
);

$inactive = array();
$activated = array();
if (empty($quiz->name)) {
    $currenttab = 'dashboard';
    $inactive = array('dashboard', 'questions', 'scoringsystem', 'documents', 'uploadscans', 'grading');
} else if (empty($quiz->questions)) {
    $currenttab = 'questions';
    $inactive = array('dashboard', 'scoringsystem', 'documents', 'uploadscans', 'grading');
} else if (!$quiz->validate()) {
    $inactive = array('documents', 'uploadscans', 'grading');
} else if (!empty($quiz->errors) || !$quiz->isLocked()) {
    $inactive = array('uploadscans', 'grading');
} else if (!$quiz->hasScans()) {
    $inactive = array('grading');
}
if ($quiz->isLocked()) {
    $inactive[] = 'questions';
}
if (!isset($currenttab)) {
    $currenttab = 'dashboard';
}

?>
<div class="groupdisplay">
    <?php echo $OUTPUT->tabtree($tabs, $currenttab, $inactive, $activated); ?>
</div>
