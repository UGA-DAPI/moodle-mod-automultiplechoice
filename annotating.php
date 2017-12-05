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

$action = optional_param('action', '', PARAM_ALPHA);
$idnumber = optional_param('idnumber', '', PARAM_INT);
$copy = optional_param('copy', '', PARAM_INT);
$group = optional_param('group', '', PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 20, PARAM_INT);
$shouldassociate = optional_param('associate', false, PARAM_BOOL);

require_capability('mod/automultiplechoice:update', $sharedservice->getContext());

$PAGE->set_url('/mod/automultiplechoice/annotating.php', array('id' => $cm->id));

$process = new \mod_automultiplechoice\local\amc\annotate($quiz);
$associateprocess =  new \mod_automultiplechoice\local\amc\associate($quiz);

if ($action === 'annotate') {
    if ($process->amcAnnote()) {
        redirect($PAGE->url);
    }
} else if ($action === 'set') {
    $errors = $associateprocess->handle_manual_association($_POST);
    if (count($errors) > 0) {
        \mod_automultiplechoice\local\helpers\flash_message_manager::addMessage(
            'error',
            get_string('associating_error_associate', 'mod_automultiplechoice')
        );
    }
    redirect($PAGE->url);
} else if ($action === 'setstudentaccess') {
    $quiz->studentaccess = optional_param('studentaccess', false, PARAM_BOOL);
    $quiz->corrigeaccess = optional_param('corrigeaccess', false, PARAM_BOOL);
    $quiz->save();
    redirect($PAGE->url);
} else if ($action === 'notification') {
    $studentsto = $process->getUsersIdsHavingAnotatedSheets();
    $okSends = $process->sendAnotationNotification($studentsto);
    $message = get_string('annotating_notify', 'mod_automultiplechoice', ['nbSuccess' => okSends, 'nbStudents' => count($studentsto)]);
    \mod_automultiplechoice\local\helpers\flash_message_manager::addMessage(
        ($okSends === count($studentsto)) ? 'success' : 'error',
        $message
    );
    redirect($PAGE->url);
}


$nbannotatedfiles = $process->countAnnotatedFiles();
// Output starts here
echo $output->header('annotating');

// Build proper data to pass to the view...
$datatodisplay = [];
$unknowncopies = [];
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

    $users = amc_get_student_users($cm, true, $group);

    $noenrol = false;
    if (count($users) === 0) {
        $noenrol = true;
    } else {
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
        foreach ($usersdisplay as $user) {
            if ($noenrol) {
                // Display the "Name img" produced by amc
                $copy = explode('_', $user);
                $link = new moodle_url(
                    'annotating.php',
                    array('a' => $quiz->id, 'copy' => $copy[0], 'idnumber' => $copy[1])
                );
                $datatodisplay[] = [
                    'url' => $process->getFileRealUrl('name-'.$user.".jpg"),
                    'label' => $user,
                    'link' => $link->out(false)
                ];
            } else if (isset($userscopy[$user->idnumber])) {
                $name = $userscopy[$user->idnumber]; // on s'en sert pas...
                // If more than one parameter in url the query params results in ?a=124&amp;idnumber=14985456425... do not ask me why...
                // So the proper way to do this is to create the moodle_url and then call $url->out(false) method
                $link =  new moodle_url(
                    'annotating.php',
                    array('a' => $quiz->id, 'idnumber' => $user->idnumber)
                );
                // Display user full name
                $datatodisplay[] = [
                    'label' => $user->lastname . ' ' . $user->firstname,
                    'link' => $link->out(false)
                ];
            }
        }
        // Unknown copy(ies)
        foreach ($associateprocess->copyunknown as $key => $value) {
            // Display the "Name img" produced by amc
            $copy = explode('_', $key);
            $link = new moodle_url(
                'annotating.php',
                array('a' => $quiz->id, 'copy' => $copy[0], 'idnumber' => $copy[1], 'associate' => true)
            );
            // Get all cr versions
            $pages = glob($process->workdir . '/cr/corrections/jpg/page-'.$copy[0].'-*-'.$copy[1].'.jpg');

            if (count($pages) > 0 && !empty($pages[0])) {
                // pick the first one to display a caption
                $cr = $pages[0];
                $unknowncopies[] = [
                    'url' =>  $process->getFileRealUrl(basename($cr)),
                    'label' => $key,
                    'link' => $link->out(false)
                ];
            }
        }

    } else { // Only show one user's report

        // When a known student / user is set we do not use the copy parameter...
        // So we need to retrieve the copy from $userscopy
        if (!$copy) {
            $name = $userscopy[$idnumber];
            list($copy, $number) = explode('_', $name);
        } else {
            $number = $idnumber;
        }

        // Several versions could exist
        $pages = glob($process->workdir . '/cr/corrections/jpg/page-'.$copy.'-*-'.$number.'.jpg');

        foreach ($pages as $crpage) {
            $datatodisplay[] = [
              'url' => $process->getFileRealUrl(basename($crpage)),
              'label' => basename($crpage),
              'code' => $copy.'_'.$number
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
    'students' => amc_get_users_for_select_element($cm, $idnumber, $currentgroup),
    'idnumber' => $idnumber,
    'copy' => $copy,
    'group' => $group,
    'cm' => $noenrol ? '' : $cm,
    'groups' => $noenrol ? [] : groups_get_activity_allowed_groups($cm),
    'isseparategroups' => $isseparategroups,
    'shouldassociate' => $shouldassociate,
    'unknowncopies' => $unknowncopies,
    'unassociatedusers' => amc_get_users_for_select_element($cm, $idnumber, '', array_merge($associateprocess->copyauto, $associateprocess->copymanual))
];

// Page content.
$view = new \mod_automultiplechoice\output\view_annotation($quiz, $data);
echo $output->render_annotation_view($view);
echo $output->footer();
