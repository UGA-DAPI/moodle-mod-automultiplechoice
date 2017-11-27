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

// Annotating.
$string['annotating_notify'] = '{$a->nbSuccess} sent messages for {$a->nbStudents} students with an annotated sheet.';
$string['annotating_rebuilt_sheets'] = 'Rebuilt sheets';
$string['annotating_corrected_sheets'] = 'Corrected sheets';
$string['annotating_individual_sheets_available'] = ' individual annotated sheets available.';
$string['annotating_update_corrected_sheets'] = 'Update corrected sheets (annotated)';
$string['annotating_generate_corrected_sheets'] = 'Generate corrected sheets';

// Annotate process.
$string['annotate_correction_available'] = 'Correction available';
$string['annotate_correction_available_body'] = 'Votre copie corrigée est disponible pour le QCM {$a->name}';
$string['annotate_correction_link'] = ' at ';

// Associating.
$string['associating_heading'] = 'Association';
$string['associating_sheets_identified'] = '{$a->automatic} sheets automaticaly identified, {$a->manualy} sheets manualy identified and {$a->unknown} unknown.';
$string['associating_relaunch_association'] = 'Relaunch association';
$string['associating_launch_association'] = 'Launch l\'association';


// Common.
$string['unlock_quiz'] = 'Unlock (allow quiz to be updated)';
$string['lock_quiz'] = 'Lock quiz';
$string['quiz_is_locked'] = 'Quiz is locked to avoid changes between printing and correction.';
$string['quiz_save_error'] = 'Error while saving the quiz.';
$string['file_type'] = 'File type';
$string['access_documents'] = 'You can access documents via this tab';
$string['error_could_not_create_directory'] = 'Could not create directory. Please contact the administrator.';
$string['error_could_not_write_directory'] = 'Could not write in directory. Please contact the administrator.';
$string['error_amc_getimages'] = 'Erreur while executing amc getimages.';
$string['error_amc_analyse'] = 'Erreur while executing amc analyse.';
$string['save'] = 'Save';

// Dashboard.
$string['subjects_ready_for_distribution'] = 'Subjects ready for distribution.';
$string['preparatory_documents_ready'] = 'The subjects have not yet been fixed but the preparatory documents are available';
$string['no_document_available'] = 'No document available';
$string['pdf_last_prepare_date'] = 'Last preparation of PDF subjects at ';
$string['pdf_none_prepared'] = 'No PDF have yet been prepared.';
$string['dashboard_nb_page_scanned'] = '{$a->nbpages} scanned pages where uploaded at {$a->date}';
$string['dashboard_no_sheets_corrected'] = 'No sheet corrected nore noted.';

// Documents.
$string['documents_meptex_error'] = 'Error while computing layout (amc meptex).';
$string['documents_pdf_created'] = 'Created PDF files.';
$string['documents_zip_archive'] = 'ZIP archive.';
$string['documents_restore_original_version'] = 'Restore original version';
$string['documents_mix_answers_and_questions'] = 'Mix questions and answers';

// Export process.
$string['export_amc_cmd_failed'] = 'Exec of `{$a->cmd}` failed. Is AMC installed ?';
$string['export_archive_open_failed'] = 'Can not open archive {$a->error}';
$string['export_archive_create_failed'] = 'Error while creating the ZIP : file could not be created. {$a->mask}';
$string['export_file_write_access_error'] = 'The file {$a->file} could not be created. Contact your sysadmin for a permission problem.';
$string['export_file_create_error'] = 'The file could not be created. Contact your sysadmin.';
$string['export_dir_access_error'] = 'The /export folder is not writable. Contact your sysadmin.';

// Grading.
$string['grading_relaunch_correction'] = 'Relaunch correction';
$string['grading_notes'] = 'Notes';
$string['grading_file_notes_table'] = 'Files notes tables';
$string['grading_sheets_identified'] = '{$a->known} sheets identified and {$a->unknown} unknown.';
$string['grading_statistics'] = 'Statistics';
$string['grading_not_satisfying_notation'] = 'If the result of the notation does not satisfy you, you can change the scale and relaunch the correction.';
$string['grading_size'] = 'Workforce';
$string['grading_mean'] = 'Mean';
$string['grading_median'] = 'Median';
$string['grading_mode'] = 'Mode';
$string['grading_range'] = 'Range';

// Logs messages.
$string['log_process_running'] = 'AMC is already running since {$a->time} minutes.';
$string['log_scoring_edited'] = 'Scoring system has been updated since last subject preparation.';
$string['log_questions_changed'] = 'Question selection has changed since last subject preparation.';
$string['log_pdf_changed_since_last_analyse'] = 'MCQ PDF has been modified since the last subjects analyse.';
$string['log_pdf_changed_since_last_upload'] = 'MCQ PDF has been modified since the last sheets upload.';
$string['log_last_lock_after_last_upload'] = 'The last lock of the MCQ has been done after the last sheets upload.';
$string['log_last_analyse_after_last_upload'] = 'The last subjects analyse has been done after the last sheets upload.';
$string['log_relaunch_correction_uploads'] = 'Some sheets have been uploaded after the last notation. Relaunch correction ?';
$string['log_relaunch_correction_scale'] = 'The scoring system has been updated since the last notation. Relaunch correction ?';
$string['log_relaunch_association_uploads'] = 'Some sheets have been uploaded since the last association. Relaunch association ?';
$string['log_relaunch_association_grading'] = 'Some sheets have been annotated since the last association. Relaunch association ?';
$string['log_sheets_no_grading'] = 'Sheets have not yet been annotated.';
$string['log_relaunch_export_grading'] = 'The last notation is more recent than the exports. Re-generate exports ?';
$string['log_relaunch_annotation_grading'] = 'The last notation is more recent than the annotated sheets. Re-generate annotation ?';
$string['log_relaunch_annotate_annotating'] = 'he last annotation is more recent than the corrected sheets (PDF). Re-generate corrected sheets (PDF)?';
$string['log_unlock_uploads_exists'] = 'Some scaned sheets have already been uploaded. In case of MCQ update those sheets wont be valid any more.';

// Process.
$string['process_no_quiz_id'] = 'No quiz ID';
$string['process_no_amc_format'] = 'Error, no MCQ format for AMC.';
$string['process_unable_to_write_file'] = 'Could not write the file for AMC. Check the space available on disk.';
$string['process_statements_file'] = 'This file contains all statements regrouped. <span class="warning">Do not use this file for students.</span>';
$string['process_catalog_file'] = 'Question catalog.';
$string['process_corrections_file'] = 'Corrections of each version.';
$string['process_archive'] = 'This archive contains one variant by statement.';
$string['catalog'] = 'Catalog';
$string['corrections'] = 'Corrections';

// Questions.
$string['questions_recent_question_not_visible'] = 'If your recent questions are not visible, try to refresh the page (F5) and sort by descendant date.';
$string['question_remove_confirm'] = 'Are you sure you want to remove the question <strong>{$a}</strong> from selection?';
// Scoring.
$string['scoring_scale_extract_error'] = 'Error while extracting scale';
$string['scoring_scale_save_success'] = 'Scale changes saved.';
$string['scoring_allocate_points'] = 'Allocate points.';
$string['scoring_show_hide_answers'] = 'Show/hide answers.';

// Scan upload.
$string['uploadscans_file_not_accessible'] = 'File not accessible.';
$string['uploadscans_no_image_known'] = 'Error, {$a->nbpages} scanned pages bot no image was recognized (no PPM).';
$string['uploadscans_process_end_message'] = 'The process ended: {$a->nbpages} pages newly scanned, {$a->nbextracted} extracted, {$a->nbidentified} pages with marks.';
$string['uploadscans_saved_sheets'] = 'Saved sheets : <b>{$a->nbsaved}</b> scanned pages where uploaded at {$a->date}';
$string['uploadscans_add_sheets'] = 'Add sheets';
$string['uploadscans_add_sheets_message'] = 'If you upload new sheets they will be added to the existing ones.';
$string['uploadscans_no_sheets_uploaded'] = 'No sheet uploaded yet.';
$string['uploadscans_delete_sheets'] = 'Delete sheets';
$string['uploadscans_delete_sheets_warn'] = 'You can delete existing uploaded sheets. This action will erase notes. After that you\'ll be able to uplad new scans.';
$string['uploadscans_delete_sheets_confirm'] = 'Definitly delete uploaded sheets on the server ?';
$string['uploadscans_unknown_scans'] = 'Unknown scans';
$string['uploadscans_delete_unknown_scans'] = 'Delete all unknown scans';
$string['uploadscans_download_unknown_scans'] = 'Download all unknown scans';
$string['uploadscans_install_sqlite3'] = 'Ask your system admin to install php-sqlite3 in order to see unknown files.';
$string['uploadscans_file'] = 'Scanned file (PDF or TIFF)';

// Settings.
$string['settings_latex_path_short'] = 'Path to XelateX';
$string['settings_latex_path_full'] = 'Path to LateX XelateX engine';
$string['settings_amctemplate_short'] = 'AMC template';
$string['settings_amctemplate_full'] = 'Path to the AMC tree template for new projects';
$string['settings_code_length_short'] = 'Code length';
$string['settings_code_length_full'] = 'Student code length for AMC display';
$string['settings_instructionslstudent_short'] = 'Instructions / student number';
$string['settings_instructionslstudent_full'] = 'Default value of the homonymous field, when creating paper questionnaires.';
$string['settings_instructionslstudent_default'] = 'Please code the student number here, and write your name below.';
$string['settings_instructionslnamestd_short'] = 'Identification area / Standard';
$string['settings_instructionslnamestd_full'] = 'Default instruction for the field when creating a new standard paper questionnaire.';
$string['settings_instructionslnamestd_default'] = 'Name et first name';
$string['settings_instructionslnameanon_short'] = 'Identification area / Anonymous';
$string['settings_instructionslnameanon_full'] = 'Default instruction for the field when creating an anonymous paper questionnaire.';
$string['settings_instructions_short'] = 'Default instructions';
$string['settings_instructions_full'] = 'Elements are separed by a line of at least 3 dashes. The first line of each block will be the title displayed in the dropdown list. Example:<pre>Concours\\nVous avez 4 heures.\\nL\'anonymat est garanti.\\n---\\nFirst Test\\nPlease use a pencil and gray each selected case completely.</pre>';
$string['settings_idnumberprefixes_short'] = 'Prefix for student number';
$string['settings_idnumberprefixes_full'] = '<p>Prefixes, one per row. Beware of spaces.</p><p>Each prefix will be inserted at the beginning of the student number of each sheet, until the identification of the student among the moodle users (cf LDAP import and idnumber). If the student can not be found, a no prefix identification will be attempted.</p>';


// Student view.
$string['studentview_one_corrected_sheet'] = 'You have one corrected sheet:';
$string['studentview_no_corrected_sheet'] = 'No corrected sheet for this MCQ';
$string['studentview_view_corrected_sheet'] = 'You can view the correction here:';


// OTHERS.

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
$string['documents'] = 'Subjects';
$string['uploadscans'] = 'Upload answers';
$string['associating'] = 'Associating';
$string['grading'] = 'Grading';
$string['annotating'] = 'Annotating';

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

$string['associationusermode'] = 'Show students';
$string['associationmode'] = 'Association mode';

$string['unknown'] = 'Unknown';
$string['manual'] = 'Manual identification';
$string['auto'] = 'Automatic Identification';

$string['without'] = 'Without sheet';
$string['selectuser'] = 'Select student';

$string['questionoperations'] = 'Before selecting questions, you may...';
$string['importfilequestions'] = 'Import file';
$string['importquestions'] = 'Import questions';
$string['createquestions'] = 'Create questions';
