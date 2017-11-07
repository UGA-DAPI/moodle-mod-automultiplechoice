<?php

require_once(__DIR__ . '/locallib.php');

global $DB, $OUTPUT, $PAGE;
/* @var $DB moodle_database */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$controller = new \mod_automultiplechoice\local\controllers\view_controller();
$quiz = $controller->getQuiz();
$cm = $controller->getCm();
$course = $controller->getCourse();
$output = $controller->getRenderer('annotating');

$action = optional_param('action', '', PARAM_ALPHA);
$idnumber = optional_param('idnumber', '', PARAM_INT);
$copy = optional_param('copy', '', PARAM_INT);
$group = optional_param('group', '', PARAM_INT);
$page         = optional_param('page', 0, PARAM_INT);
$perpage      = optional_param('perpage', 20, PARAM_INT);

require_capability('mod/automultiplechoice:update', $controller->getContext());

$PAGE->set_url('/mod/automultiplechoice/annotating.php', array('id' => $cm->id));

$process = new \mod_automultiplechoice\local\amc\annotate($quiz);

if ($action === 'annotate') {
    if ($process->amcAnnote()) {
        redirect($PAGE->url);
    }
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





// Output starts here
echo $output->header();

$warnings = \mod_automultiplechoice\local\helpers\log::build($quiz->id)->check('annotating');
if ($warnings) {
    echo '<div class="informationbox notifyproblem alert alert-error">';
    foreach ($warnings as $warning) {
        echo $warning;
    }

    echo "<br /><br />";
    echo $OUTPUT->single_button( 
        new moodle_url(
            '/mod/automultiplechoice/annotating.php',
            array( 'a' => $quiz->id, 'action' => 'annotate')
        ),
        get_string('annotating_rebuilt_sheets', 'mod_automultiplechoice')
    );
    echo "</div>";
}
if ($process->countAnnotatedFiles() > 0) {
    $url = $process->getFileUrl($process->normalizeFilename('corrections'));
    echo $OUTPUT->box_start('informationbox well');
    echo $OUTPUT->heading(get_string('annotating_corrected_sheets', 'mod_automultiplechoice'), 2)
        . $OUTPUT->heading(get_string('files', 'core'), 3)
        . \html_writer::link($url, $process->normalizeFilename('corrections'), array('target' => '_blank'));
    echo "<p><b>" . $process->countAnnotatedFiles() . "</b>" . get_string('annotating_individual_sheets_available', 'mod_automultiplechoice') . "</p>";
    echo $OUTPUT->single_button(
        new moodle_url(
            '/mod/automultiplechoice/annotating.php',
            array('a' => $quiz->id, 'action' => 'annotate')
        ),
        get_string('annotating_update_corrected_sheets', 'mod_automultiplechoice')
    );

    // Groups are being used.
    $groupmode    = groups_get_activity_groupmode($cm);
    $currentgroup = groups_get_activity_group($cm, true);

    // To make some other functions work better later.
    if (!$currentgroup) {
        $currentgroup = null;
    }
    $context = context_module::instance($cm->id);
    $isseparategroups = ($cm->groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context));

    $users = amc_get_student_users($cm, true, $group);
    $noenrol = false;
    if (count($users) === 0) {
        $noenrol = true;
    } else {
        $process->get_association();
        $userscopy = array_flip(array_merge($process->copymanual, $process->copyauto));
    }
    $url = new moodle_url('annotating.php', array('a' => $quiz->id));

    if (empty($idnumber) && empty($copy)) {

        if ($noenrol) {
             $users = array_map('get_code', glob($process->workdir . '/cr/name-*.jpg'));

        } else {
            groups_print_activity_menu($cm, $url);
            echo $output->students_selector($url, $cm, $idnumber, $currentgroup);
        }

        $paging = new paging_bar(count($users), $page, 20, $url, 'page');

        echo $OUTPUT->render($paging);
        $usersdisplay = array_slice($users, $page * $perpage, $perpage);
        echo html_writer::start_div('amc_thumbnails');
        echo html_writer::start_tag('ul', array('class' => 'thumbnails'));
        foreach ($usersdisplay as $user) {
            if (!$noenrol) {
                if (isset($userscopy[$user->idnumber])) {
                    $name = $userscopy[$user->idnumber];
                } else {
                    $name = "0_0";
                }
            } else {
                $name = $user;
            }
            $copy = explode('_', $name);
            $thumbnailimg = \html_writer::img($process->getFileUrl('name-'.$name.".jpg"), $name);
            $thumbnailoutput = \html_writer::link(new moodle_url('annotating.php', array('a' => $quiz->id, 'copy'=> $copy[0], 'idnumber' => $copy[1])), $thumbnailimg, array('class' => 'thumbnail'));
            echo html_writer::tag('li', $thumbnailoutput );
        }
        echo html_writer::end_tag('ul');
        echo html_writer::end_div();

    } else { // Only show one user's report

        if (!$noenrol) {
            groups_print_activity_menu($cm, $url);
            echo $output->students_selector($url, $cm, $idnumber, $currentgroup);
        }
        if (!$copy) {
            $name = $userscopy[$idnumber];
            list($copy, $idnumber) = explode('_', $name);
        }
        $pages = glob($process->workdir . '/cr/corrections/jpg/page-'.$copy.'-*-'.$idnumber.'.jpg');

        foreach ($pages as $page) {
            echo \html_writer::img($process->getFileUrl(basename($page)), $page);
        }

    }
} else {
    echo $OUTPUT->single_button(
        new moodle_url(
            '/mod/automultiplechoice/annotating.php',
            array('a' => $quiz->id, 'action' => 'annotate')
        ),
        get_string('annotating_generate_corrected_sheets', 'mod_automultiplechoice')
    );
}

echo $output->footer();
