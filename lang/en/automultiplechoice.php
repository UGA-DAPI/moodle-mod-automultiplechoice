<?php

/**
 * English strings for automultiplechoice
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_automultiplechoice
 * @copyright  2013 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'automultiplechoice';
$string['modulenameplural'] = 'automultiplechoices';
$string['modulename_help'] = 'The automultiplechoice module allows to quickly create a quizz to be printed then graded by AMC.';
$string['automultiplechoice'] = 'AutoMultipleChoice';
$string['pluginadministration'] = 'AutoMultipleChoice administration';
$string['pluginname'] = 'automultiplechoice';
$string['noautomultiplechoices'] = 'Aucune instance de automultiplechoice n\'est définie dans ce cours';
$string['automultiplechoice:addinstance'] = 'Create a printable quizz';
$string['automultiplechoice:update'] = 'Update a printable quizz';
$string['automultiplechoice:view'] = 'View a quizz or one\'s marked work';

$string['dashboard'] = 'Dashboard';
$string['documents'] = 'Documents';
$string['uploadscans'] = 'Upload answers';
$string['grading'] = 'Grading';

$string['instructionsheader'] = 'Instructions';
$string['automultiplechoicename'] = 'Nom du questionnaire';
$string['instructions'] = 'Top Instructions';
$string['description'] = 'Instructions';
$string['comment'] = 'Commentaire';
$string['qnumber'] = 'Nb. questions';
$string['score'] = 'Points Total';

$string['automultiplechoicename_help'] = 'Le nom complet du questionnaire';
$string['instructions_help'] = 'The text associated to this will be inserted at the top of the custom instructions.';
$string['description_help'] = 'La description qui sera imprimée sur chaque questionnaire, contenant l\'introduction et les consignes.';


$string['comment_help'] = 'Un commentaire pour l\'auteur, qui ne sera pas imprimé.';
$string['qnumber_help'] = 'Le nombre de questions prévisionnel du questionnaire, pour validation.';
$string['score_help'] = 'Le score total du questionnaire (en points), pour validation.';

$string['amcparams'] = 'AMC Parameters';
$string['amc_minscore'] = 'Minimal score';
$string['amc_copies'] = 'Versions Number';
$string['amc_questionsColumns'] = '# columns for questions';
$string['amc_questionsColumns_help'] = 'If set to "Auto", questions will be printed on two columns if they are numerous.';
$string['amc_shuffleq'] = 'Shuffle questions';
$string['amc_shufflea'] = 'Shuffle answers';
$string['amc_separatesheet'] = 'Separate answer sheet';
$string['amc_answerSheetColumns'] = '# columns on this sheet';
$string['amc_grademax'] = 'Maximum Final Grade';
$string['amc_gradegranularity'] = 'Grading Precision';
$string['amc_graderounding'] = 'Grade Rouding';
$string['anonymous'] = 'Anonymous';
$string['amc_lstudent'] = 'Instructions / student #';
$string['amc_lname'] = 'Instructions / name';
$string['amc_lstudent_help'] = 'Text displayed aside the grid where a student inputs his ID number.';
$string['amc_lname_help'] = 'Title of the frame displayed in the top right column, where the student is asked to input some text, e.g. its name.';
$string['amc_lstudent_default'] = 'Please code your student number opposite, and write your name in the box below.';
$string['amc_lname_default'] = 'Name and surname';
$string['amc_markmulti'] = 'Mark when multiple answers';
$string['amc_markmulti_help'] = 'A clover leaf will appear when a question has more than one right answer.';

$string['amc_score'] = 'Show instructions score';
$string['amc_score_help'] = 'The instructions of calculate score print on the subject.';

$string['amc_customlayout'] = 'Custom layout';
$string['amc_customlayout_help'] = ' Customize QCM layout.';

$string['questionselect'] = 'Select questions';
$string['questionselected'] = 'Selected Questions';
$string['sortmsg'] = 'You can reorder the selected questions by dragging them with the mouse.';
$string['qexpected'] = '{$a} questions expected.';
$string['savesel'] = 'Save this selection';
$string['qcategory'] = 'Question Category';
$string['qtitle'] = 'Question';
$string['qscore'] = 'Points';
$string['amc_displaypoints'] = 'Display Scores';
$string['scoringrules'] = 'Scoring Rules';
$string['scoringset'] = 'Scoring strategy';
$string['scoringsystem'] = 'Scoring system';
$string['insertsection'] = 'Insert a new section here';

$string['editselection'] = 'Update this selection';

$string['validateql_wrong_number'] = 'The number of questions is not the number expected.';
$string['validateql_wrong_sum'] = 'The sum of the questions\' score does not match the expected total score.' ;
$string['validateql_wrong_score'] = 'The score of at least one question is not valid.';
$string['validate_positive_int'] = 'This should be a strictly positive number.';
$string['validate_poszero_int'] = 'This should be a positive number, or zero.';
$string['validate_under_maxscore'] = 'This should be lesser than the total score.';
$string['validateql_deletedquestions'] = 'Some questions could not be found and were probably deleted.';
$string['validate_copies_without_shuffle'] = 'Having muliple copies without any shuffling is meaningless.';

$string['prepare'] = 'Preview PDF question sheets';
$string['prepare-locked'] = 'Download the final documents';
$string['analyse'] = 'Submit student copies';
$string['note'] = 'Grades and annotated copies';
$string['export'] = 'Reports';

$string['questionoperations'] = 'Before selecting questions, you may...';
$string['importfilequestions'] = 'Import file with questions'; 
$string['importquestions'] = 'Import questions';
$string['createquestions'] = 'Create questions';
