<?php

require_once(__DIR__ . '/locallib.php');

global $OUTPUT, $PAGE;
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$sharedservice = new \mod_automultiplechoice\shared_service();
$quiz = $sharedservice->getQuiz();
$cm = $sharedservice->getCm();
$course = $sharedservice->getCourse();
$output = $sharedservice->getRenderer();

// url params
$action         = optional_param('action', '', PARAM_ALPHA);
$mode           = optional_param('mode', 'unknown', PARAM_ALPHA);
$usermode       = optional_param('usermode', 'without', PARAM_ALPHA);
$idnumber       = optional_param('idnumber', '', PARAM_INT);
$page           = optional_param('page', 0, PARAM_INT);
$perpage        = optional_param('perpage', 20, PARAM_INT); // for pager but no way to set it... maybe just a variable ?

require_capability('mod/automultiplechoice:update', $sharedservice->getContext());

$PAGE->set_url('/mod/automultiplechoice/associating.php', array('id' => $cm->id));

$url = new moodle_url('associating.php', array('a' => $quiz->id, 'mode' => $mode, 'usermode' => $usermode));

$associateprocess = new \mod_automultiplechoice\local\amc\associate($quiz);
$process = new \mod_automultiplechoice\local\amc\process($quiz);

// In amc grading is supposed to occure before association but this is not ideal for teachers workflow so this is a "tweak".
if (!$process->isGraded()) {
    if ($process->amcNote()) {
        redirect($PAGE->url);
    }
}

if ($action === 'associate') {
    $associateprocess->create_student_csv();
    if (!$process->amcNote()) {
        \mod_automultiplechoice\local\helpers\flash_message_manager::addMessage(
            'error',
            get_string('associating_error_note', 'mod_automultiplechoice')
        );
        redirect($url);
    }
    if (!$associateprocess->amcAssociation()) {
        \mod_automultiplechoice\local\helpers\flash_message_manager::addMessage(
            'error',
            get_string('associating_error_associate', 'mod_automultiplechoice')
        );
        redirect($url);
    }
} else if ($action === 'set') {
    // manual association
    // I dont understand why it's not possible to use moodle optional_param to read $_POST values
    $errors = $associateprocess->handle_manual_association($_POST);
    if (count($errors) > 0) {
        \mod_automultiplechoice\local\helpers\flash_message_manager::addMessage(
            'error',
            get_string('associating_error_associate', 'mod_automultiplechoice')
        );
        redirect($url);
    }
}

$associateprocess->get_association();

echo $output->header('associating');

$errors = \mod_automultiplechoice\local\helpers\log::build($quiz->id)->check('associating');

$associationmodes = $associateprocess->get_association_modes();
$usermodes = $associateprocess->get_user_modes();
$allusersdata = $associateprocess->get_all_users_data($mode);
$visiblesheets = array_slice($allusersdata, $page * $perpage, $perpage);
$excludeusers = ($usermode === 'all') ? '' : array_merge($associateprocess->copymanual, $associateprocess->copyauto);

$usersdata = [];
foreach ($visiblesheets as $name => $usernumber) {
    $usersdata[] = [
      'students' => amc_get_users_for_select_element($cm, $usernumber, '', $excludeusers),
      'fileurl' => $associateprocess->getFileRealUrl('name-'.$name.".jpg"),
      'filename' => $name
    ];
}

$data = [
    'errors' => $errors,
    'showerrors' => !empty($errors),
    'isrelaunch' => !empty($errors) || !empty($associateprocess->copyauto),
    'nbcopyauto' => count($associateprocess->copyauto),
    'nbcopymanual' => count($associateprocess->copymanual),
    'nbcopyunknown' => count($associateprocess->copyunknown),
    'associationmodes' => $associationmodes,
    'associationmode' => $mode,
    'usermodes' => $usermodes,
    'usermode' => $usermode,
    'usersdata' => $usersdata,
    'pager' => [
      'page' => $page,
      'perpage' => $perpage,
      'url' => $url,
      'pagecount' => count($namedisplay)
    ]
];

// Page content.
$view = new \mod_automultiplechoice\output\view_association($quiz, $data);
echo $output->render_association_view($view);
echo $OUTPUT->footer();
