<?php
/* @var $OUTPUT core_renderer */
global $OUTPUT;

/* @var $quiz mod_automultiplechoice\local\models\quiz */
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
        'associating',
        new moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/associating.php?a={$quiz->id}"),
        "6. " . get_string('associating', 'automultiplechoice')
    ),
    new tabobject(
        'grading',
        new moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/grading.php?a={$quiz->id}"),
        "7. " . get_string('grading', 'automultiplechoice')
    ),
    new tabobject(
        'annotating',
        new moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/annotating.php?a={$quiz->id}"),
        "8. " . get_string('annotating', 'automultiplechoice')
    ),
);

$inactive = array();
$activated = array();
if (empty($quiz->name)) {
    $currenttab = 'dashboard';
    $inactive = array('dashboard', 'questions', 'scoringsystem', 'documents', 'uploadscans', 'associating', 'grading', 'annotating');
} else if (empty($quiz->questions)) {
    $currenttab = 'questions';
    $inactive = array('dashboard', 'scoringsystem', 'documents', 'uploadscans', 'associating', 'grading', 'annotating');
} else if (!$quiz->validate()) {
    $inactive = array('documents', 'uploadscans', 'associating', 'grading', 'annotating');
} else if (!empty($quiz->errors) || !$quiz->isLocked()) {
    $inactive = array('uploadscans', 'associating', 'grading', 'annotating');
} else if (!$quiz->hasScans()) {
    $inactive = array('associating', 'grading', 'annotating');
}
if ($quiz->isLocked()) {
    $inactive[] = 'questions';
}
if (has_students($context) === 0) {
    $inactive = array('associating');
}
if (!isset($currenttab)) {
    $currenttab = 'dashboard';
}

?>
<div class="groupdisplay">
    <?php echo $OUTPUT->tabtree($tabs, $currenttab, $inactive, $activated); ?>
</div>
