<?php

require_once(__DIR__ . '/locallib.php');

global $DB, $OUTPUT, $PAGE;
/* @var $DB moodle_database */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$sharedservice = new \mod_automultiplechoice\shared_service();
$quiz = $sharedservice->getQuiz();
$cm = $sharedservice->getCm();
$course = $sharedservice->getCourse();
$output = $sharedservice->getRenderer();
$context = $sharedservice->getContext();

$action = optional_param('action', '', PARAM_ALPHA);
$idnumber = optional_param('idnumber', '', PARAM_INT);
$copy = optional_param('copy', '', PARAM_INT);
$group = optional_param('group', '', PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 20, PARAM_INT);
$shouldassociate = optional_param('associate', false, PARAM_BOOL);

require_capability('mod/automultiplechoice:update', $context);

$PAGE->set_url('/mod/automultiplechoice/annotating.php', array('id' => $cm->id));

$process = new \mod_automultiplechoice\local\amc\annotate($quiz);
$associateprocess =  new \mod_automultiplechoice\local\amc\associate($quiz);

if ($action === 'annotate') {
    $process->amcAnnote();
} else if ($action === 'set') {
    $errors = $associateprocess->handle_manual_association($_POST);
    if (count($errors) > 0) {
        \mod_automultiplechoice\local\helpers\flash_message_manager::addMessage(
            'error',
            get_string('associating_error_associate', 'mod_automultiplechoice')
        );
    }
} else if ($action === 'setstudentaccess') {
    $quiz->studentaccess = optional_param('studentaccess', false, PARAM_BOOL);
    $quiz->corrigeaccess = optional_param('corrigeaccess', false, PARAM_BOOL);
    $quiz->save();
} else if ($action === 'notification') {
    $studentsto = $process->getUsersIdsHavingAnotatedSheets();
    $okSends = $process->sendAnotationNotification($studentsto);
    $message = get_string('annotating_notify', 'mod_automultiplechoice', ['nbSuccess' => okSends, 'nbStudents' => count($studentsto)]);
    \mod_automultiplechoice\local\helpers\flash_message_manager::addMessage(
        ($okSends === count($studentsto)) ? 'success' : 'error',
        $message
    );
}

if (!empty($action)) {
    redirect($PAGE->url);
}

$nbannotatedfiles = $process->countAnnotatedFiles();
// Output starts here
echo $output->header('annotating');

// Build proper data to pass to the view...
$datatodisplay = [];
if ($nbannotatedfiles > 0) {
    // Groups are being used.
    $groupmode    = groups_get_activity_groupmode($cm);
    $currentgroup = groups_get_activity_group($cm, true);
    // To make some other functions work better later.
    if (!$currentgroup) {
        $currentgroup = null;
    }
    $context = context_module::instance($cm->id);
    $isseparategroups = ($cm->groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context));

    $noenrol = has_students($context) === 0;
    if (!$noenrol) {
        $users = amc_get_student_users($cm, true, $group);
        $associateprocess->get_association();
        $userscopy = array_flip(array_merge($associateprocess->copymanual, $associateprocess->copyauto));
    }

    $url = new moodle_url('annotating.php', array('a' => $quiz->id));

    // Display all available users / copy
    if (empty($idnumber) && empty($copy)) {
        if ($noenrol) {
            $users = array_map('get_code', glob($process->workdir . '/cr/name-*.jpg'));
        }
        $usersdisplay = array_slice($users, $page * $perpage, $perpage);

        $datatodisplay = $process->get_all_users_data($usersdisplay, $userscopy, $noenrol);
    } else if ($shouldassociate) { // no user associated
        // Get all unknown users name caption in order to enable association within annotation view
        $datatodisplay = $process->get_unknown_users_captions($associateprocess->copyunknown);
    } else { // Only show one user's report

        // When a known student / user is set we do not use the copy parameter...
        // So we need to retrieve the copy from $userscopy
        if (!$copy) {
            $name = $userscopy[$idnumber];
            list($copy, $number) = explode('_', $name);
        } else {
            $number = $idnumber;
        }

        $pages = glob($process->workdir . '/cr/corrections/jpg/page-'.$copy.'-*-'.$number.'.jpg');

        foreach ($pages as $crpage) {
            $datatodisplay[] = [
              'url' => $process->getFileRealUrl(basename($crpage)),
              'label' => basename($crpage)
            ];
        }
    }
}

$normalizedfilename = $process->normalizeFilename('corrections');

$data = [
    'errors' =>  \mod_automultiplechoice\local\helpers\log::build($quiz->id)->check('annotating'),
    'showerrors' => !empty($errors),
    'nbannotated' => $nbannotatedfiles,
    'alreadyannoted' => $nbannotatedfiles > 0,
    'correctionfileurl' => $process->getFileActionUrl($normalizedfilename),
    'correctionfilename' => $normalizedfilename,
    'pager' => [
      'page' => $page,
      'perpage' => $perpage,
      'url' => $url,
      'pagecount' => count($users)
    ],
    'usersdata' => $datatodisplay,
    'students' => $noenrol ? [] : amc_get_users_for_select_element($cm, $idnumber, $currentgroup),
    'idnumber' => $idnumber,
    'group' => $group,
    'groups' => $noenrol ? [] : groups_get_activity_allowed_groups($cm),
    'isseparategroups' => $isseparategroups,
    'shouldassociate' => $shouldassociate,
    'noenrol' => $noenrol
];

// Page content.
$view = new \mod_automultiplechoice\output\view_annotation($quiz, $data);
echo $output->render_annotation_view($view);
echo $output->footer();
