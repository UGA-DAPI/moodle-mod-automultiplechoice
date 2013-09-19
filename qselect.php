<?php

/**
 * Selects questions.
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_automultiplechoice
 * @copyright  2013 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

global $DB, $OUTPUT, $PAGE;

require_once(dirname(dirname(__DIR__)) . '/config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/locallib.php');
require_once __DIR__ . '/models/Quizz.php';

$a  = required_param('a', PARAM_INT);  // instance ID

//$automultiplechoice  = $DB->get_record('automultiplechoice', array('id' => $a), '*', MUST_EXIST);
$quizz = \mod\automultiplechoice\Quizz::findById($a);
$course     = $DB->get_record('course', array('id' => $quizz->course), '*', MUST_EXIST);
$cm         = get_coursemodule_from_instance('automultiplechoice', $quizz->id, $course->id, false, MUST_EXIST);

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/automultiplechoice:addinstance', $context);

// form submitted?
$questions = \mod\automultiplechoice\QuestionList::fromForm('question');
if ($questions) {
    $quizz->questions = $questions;
    if ($questions->validate($quizz)) {
        if ($quizz->save()) {
            redirect(new moodle_url('view.php', array('a' => $quizz->id)));
        } else {
            die("Could not save into automultiplechoice");
        }
    }
}

// add_to_log($course->id, 'automultiplechoice', 'view', "qselect.php?id={$cm->id}", $quizz->name, $cm->id);

$PAGE->set_url('/mod/automultiplechoice/qselect.php', array('a' => $quizz->id));
$PAGE->set_title(format_string($quizz->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_cacheable(false);

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->js(new moodle_url('assets/dataTables/jquery.dataTables.min.js'));
$PAGE->requires->css(new moodle_url('assets/dataTables/css/jquery.dataTables.css'));

$PAGE->requires->js(new moodle_url('assets/qselect.js'));
$PAGE->requires->css(new moodle_url('assets/amc.css'));


$available_questions = automultiplechoice_list_questions($USER, $COURSE);

echo $OUTPUT->header();


echo $OUTPUT->box_start();
echo $OUTPUT->heading(get_string('questionselect', 'automultiplechoice'));
if ($questions && $questions->errors) {
    echo $OUTPUT->box_start('errorbox');
    echo $OUTPUT->heading(get_string('errors', 'automultiplechoice'), 3);
    echo '<ul class="errors">';
    foreach ($questions->errors as $e) {
        echo '<li>ERROR (to localize, etc): ' . $e . '</li>';
    }
    echo '</ul>';
    echo $OUTPUT->box_end();
}
?>
<table id="questions-list">
    <thead>
        <tr>
            <th><?php echo get_string('qcategory', 'automultiplechoice'); ?></th>
            <th><?php echo get_string('qtitle', 'automultiplechoice'); ?></th>
            <th><?php echo get_string('date'); ?></th>
            <th><?php echo get_string('actions'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($available_questions as $q) {
            echo '<tr id="q-' . $q->id . '">'
                . '<td>' . format_string($q->categoryname) . '</td>'
                . '<td class="qtitle">' . format_string($q->title) . '</td>'
                . '<td>' . date('Y-m-d', $q->timemodified) . '</td>'
                . '<td><button type="button" data-qid="' . $q->id . '">+</button></td>'
                . '</tr>';
        }
        ?>
    </tbody>
</table>
<div class="datatable-end"></div>
<?php
echo $OUTPUT->box_end();

echo $OUTPUT->box_start();
echo $OUTPUT->heading(get_string('questionselected', 'automultiplechoice'));
?>
<p>
    <?php echo get_string('sortmsg', 'automultiplechoice'); ?>
</p>
<form name="questions-form" action="qselect.php" method="post">
<p>
    <input name="a" value="<?php echo $quizz->id; ?>" type="hidden" />
    <button type="submit"><?php echo get_string('savesel', 'automultiplechoice'); ?></button>
</p>
<ul id="questions-selected">
    <li style="display: none;" class="ui-state-default">
        <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
        <label></label>
        <input name="question[id][]" value="" type="hidden" disabled="disabled" />
        <button type="button">X</button>
        <label class="qscore">
            <?php echo get_string('qscore', 'automultiplechoice'); ?> :
            <input name="question[score][]" value="" type="text" disabled="disabled" />
        </label>
    </li>
    <?php
    if (count($quizz->questions)) {
        foreach ($quizz->questions->getRecords() as $q) {
            echo '
    <li class="ui-state-default" id="qsel-' . $q->id . '">
        <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
        <label>' . format_string($q->name) . '</label>
        <input name="question[id][]" value="' . $q->id . '" type="hidden" />
        <button type="button">X</button>
        <label class="qscore">
            ' . get_string('qscore', 'automultiplechoice') . ' :
            <input name="question[score][]" value="' . $q->score . '" type="text" />
        </label>
    </li>
                ';
        }
    }
    ?>
</ul>
</form>

<?php
echo $OUTPUT->box_end();

echo $OUTPUT->footer();
