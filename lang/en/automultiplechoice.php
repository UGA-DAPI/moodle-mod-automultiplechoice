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

$string['automultiplechoicename'] = 'Nom du questionnaire';
$string['instructions'] = 'Top Instructions';
$string['description'] = 'Instructions';
$string['comment'] = 'Commentaire';
$string['qnumber'] = 'Nb. questions';
$string['score'] = 'Total score';

$string['automultiplechoicename_help'] = 'Le nom complet du questionnaire';
$string['instructions_help'] = 'The text associated to this will be inserted at the top of the custom instructions.';
$string['description_help'] = 'La description qui sera imprimée sur chaque questionnaire, contenant l\'introduction et les consignes.';
$string['comment_help'] = 'Un commentaire pour l\'auteur, qui ne sera pas imprimé.';
$string['qnumber_help'] = 'Le nombre de questions prévisionnel du questionnaire, pour validation.';
$string['score_help'] = 'Le score total du questionnaire (en points), pour validation.';

$string['amcparams'] = 'AMC Parameters';
$string['amc_copies'] = 'Versions Number';
$string['amc_shuffleq'] = 'Shuffle questions';
$string['amc_shufflea'] = 'Shuffle answers';
$string['amc_separatesheet'] = 'Separate answer sheet';
$string['amc_lstudent'] = 'Instructions / student #';
$string['amc_lname'] = 'Instructions / name';
$string['amc_lstudent_help'] = 'Text displayed aside the grid where a student inputs his ID number.';
$string['amc_lname_help'] = 'Title of the frame displayed in the top right column, where the student is asked to input some text, e.g. its name.';
$string['amc_lstudent_default'] = 'Please code your student number opposite, and write your name in the box below.';
$string['amc_lname_default'] = 'Name and surname';

$string['questionselect'] = 'Select questions';
$string['questionselected'] = 'Selected Questions';
$string['sortmsg'] = 'You can reorder the selected questions by dragging them with the mouse.';
$string['savesel'] = 'Save this selection';
$string['qcategory'] = 'Question Category';
$string['qtitle'] = 'Question';
$string['qscore'] = 'Points';
$string['scoring'] = 'Scoring method';

$string['editselection'] = 'Update this selection';

$string['validateql_wrong_number'] = 'The number of questions is not the number expected.';
$string['validateql_wrong_sum'] = 'The sum of the questions\' score does not match the expected total score.' ;
$string['validateql_wrong_score'] = 'The score of at least one question is not valid.';
$string['validate_positive_int'] = 'This should be a strictly positive number.';

$string['prepare'] = 'Prepare';
$string['analyse'] = 'Analyze';
$string['note'] = 'Grade';
$string['export'] = 'Reports';

$string['questionoperations'] = 'Before selecting questions, you may...';
$string['importquestions'] = 'Import questions';
$string['createquestions'] = 'Create questions';
