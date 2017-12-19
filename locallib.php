<?php
/**
 * Internal library of functions for module automultiplechoice
 *
 * All the automultiplechoice specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_automultiplechoice
 * @copyright  2013-2014 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \mod\automultiplechoice as amc;

require_once dirname(dirname(__DIR__)) . '/config.php';
require_once "$CFG->libdir/formslib.php";
require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/models/Quizz.php';
require_once __DIR__ . '/models/AmcProcess.php';
require_once __DIR__ . '/components/HtmlHelper.php';
require_once __DIR__ . '/components/Controller.php';

defined('MOODLE_INTERNAL') || die();

global $DB;
/* @var $DB \moodle_database */

if (version_compare(phpversion(), '5.4.0') < 0) {
    error("This module requires PHP 5.4. It won't work with an older PHP.");
}

/**
 * Return the list of questions available to a given user.
 *
 * This function ignore the permissions set in Moodle, except for 'moodle/question:useall'.
 * Then it lists all the questions defined in the current course.
 *
 * @todo Check if the current user has access to "system", then add its questions.
 *
 * @param object $user User record
 * @param object $course Course record
 * @return array List of objects with fields: id, categoryname, title, timemodified
 */
function automultiplechoice_list_questions($user, $course) {
    global $DB, $CFG;

    $course_context = context_course::instance($course->id);

    if (!has_capability('moodle/question:useall', $course_context, $user)) {
        return array();
    }

    if ($CFG->version >= 2013111800) {
        $qtable = 'qtype_multichoice_options';
        $qfield = 'questionid';
    } else {
        $qtable = 'question_multichoice';
        $qfield = 'question';
    }
    $sql = "SELECT q.id, qc.name AS categoryname, q.name AS title, q.timemodified "
            . "FROM {question} q JOIN {question_categories} qc ON q.category = qc.id "
            . " JOIN {" . $qtable . "} qm ON qm.{$qfield}=q.id "
            . "WHERE q.hidden = 0 AND qc.contextid = " . $course_context->id
            . " ORDER BY qc.sortorder, q.name";
    return $DB->get_records_sql($sql);
}

/**
 * Parses the config setting 'instructions' to convert it into an associative array (instruction => title).
 * 
 * @return array
 */
function parse_default_instructions() {
    $raw = get_config('mod_automultiplechoice', 'instructions');
    if (!$raw) {
        return array();
    }
    $splitted = preg_split('/\n-{3,}\s*\n/s', $raw, -1, PREG_SPLIT_NO_EMPTY);
    $assoc = array();
    foreach ($splitted as $split) {
        $lines = explode("\n", $split, 2);
        $title = trim($lines[0]);
        if ($title) {
            $assoc[$lines[1]] = $title;
        }
    }
    $assoc[''] = 'vide';
    return $assoc;
}

/**
 * Return a user record.
 *
 * @todo Optimize? One query per user is doable, the difficulty is to sort results according to prefix order.
 *
 * @global \moodle_database $DB
 * @param string $idn
 * @return object Record from the user table.
 */
function getStudentByIdNumber($idn) {
    global $DB;
    $prefixestxt = get_config('mod_automultiplechoice', 'idnumberprefixes');
    $prefixes = array_filter(array_map('trim', preg_split('/\R/', $prefixestxt)));
    $prefixes[] = "";
    foreach ($prefixes as $p) {
        $user = $DB->get_record('user', array('idnumber' => $p . $idn, 'confirmed' => 1, 'deleted' => 0));
        if ($user) {
            return $user;
        }
    }
    return null;
}

/**
 * Returns a HTML button.
 *
 * @global type $OUTPUT
 * @param integer $id
 * @return string
 */
function button_back_to_activity($id) {
    global $OUTPUT;
    $url = new moodle_url('/mod/automultiplechoice/view.php', array('a' => $id));
    return '<div class="back-to-activity">'
            . $OUTPUT->single_button($url, 'Retour au questionnaire', 'get')
            . '</div>';
}

function displayLockButton(amc\Quizz $quizz) {
    global $OUTPUT;
    if (empty($quizz->errors)) {
        if ($quizz->isLocked()) {
            echo $OUTPUT->single_button(
                    new moodle_url('/mod/automultiplechoice/documents.php', array('a' => $quizz->id, 'unlock' => 1)),
                    'Déverrouiller (permettre les modifications du questionnaire)', 'post'
            );
        } else {
            echo $OUTPUT->single_button(
                        new moodle_url('/mod/automultiplechoice/documents.php', array('a' => $quizz->id, 'lock' => 1)),
                        'Préparer les documents à imprimer et verrouiller le questionnaire', 'post'
                );
        }
    } else {
        echo 'Préparer et verrouiller. ' . get_string('functiondisabled');
    }
}

function displayGradeInfo(amc\AmcProcess $process) {
    $gradetime = $process->lastlog('note');
    if ($gradetime) {
        echo "<div>Correction des copies déjà effectuée le " . amc\AmcProcess::isoDate($gradetime) . "</div>\n";
    }
}

function get_code($name) {
	    preg_match('/name-(?P<student>[0-9]+)[:-](?P<copy>[0-9]+).jpg$/', $name,$res);
	        return $res['student'].'_'.$res['copy'];

}

