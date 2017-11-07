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
$output = $controller->getRenderer('associating');

$action         = optional_param('action', '', PARAM_ALPHA);
$mode           = optional_param('mode', 'unknown', PARAM_ALPHA);
$usermode       = optional_param('usermode', 'without', PARAM_ALPHA);
$idnumber       = optional_param('idnumber', '', PARAM_ALPHA);
$copy           = optional_param('copy', '', PARAM_INT);
$page           = optional_param('page', 0, PARAM_INT);
$perpage        = optional_param('perpage', 20, PARAM_INT);

require_capability('mod/automultiplechoice:update', $controller->getContext());

$PAGE->set_url('/mod/automultiplechoice/associating.php', array('id' => $cm->id));

$url = new moodle_url('associating.php', array('a' => $quiz->id, 'mode' => $mode, 'usermode' => $usermode));



$process = new \mod_automultiplechoice\local\amc\associate($quiz);

if ($action === 'associate') {
    if ($process->associate()) {
        redirect($url);
    }
}

$process->get_association();

echo $output->header();
echo $OUTPUT->box_start('informationbox well');
echo $OUTPUT->heading(get_string('associating_heading', 'mod_automultiplechoice'), 2)
    . "<p>" . get_string('associating_heading', 'mod_automultiplechoice', ['automatic' => count($process->copyauto), 'manualy' => count($process->copymanual), 'unknown' => count($process->copyunknown)])."</p>";

$warnings = \mod_automultiplechoice\local\helpers\log::build($quiz->id)->check('associating');

if ($warnings) {
    echo '<div class="informationbox notifyproblem alert alert-error">';
    foreach ($warnings as $warning) {
        echo $warning;
    }

    echo "<br /><br />";
    echo $OUTPUT->single_button( new moodle_url('/mod/automultiplechoice/associating.php',
                                array( 'a' => $quiz->id, 'action' => 'associate'))
                                , get_string('associating_relaunch_association', 'mod_automultiplechoice'));
    echo "</div>";
} else if (count($process->copyauto)) {
    echo $OUTPUT->single_button( new moodle_url('/mod/automultiplechoice/associating.php',
                                array( 'a' => $quiz->id, 'action' => 'associate'))
                                , get_string('associating_launch_association', 'mod_automultiplechoice'));
} else {
    echo $OUTPUT->single_button( new moodle_url('/mod/automultiplechoice/associating.php',
                                array( 'a' => $quiz->id, 'action' => 'associate'))
                                , get_string('associating_relaunch_association', 'mod_automultiplechoice'));
}
$optionsmode = array ('unknown'  => get_string('unknown', 'automultiplechoice'),
                  'manual' => get_string('manual', 'automultiplechoice'),
                  'auto' => get_string('auto', 'automultiplechoice'),
                  'all'   => get_string('all'));
$selectmode = new single_select($url, 'mode', $optionsmode, $mode, null, "mode");
$selectmode->set_label(get_string('associationmode', 'automultiplechoice'));
if ($mode === 'unknown') {
    $namedisplay = $process->copyunknown;
} else if ($mode === 'manual') {
    $namedisplay = $process->copymanual;
} else if ($mode === 'auto') {
    $namedisplay = $process->copyauto;
} else if ($mode === 'all') {
    $namedisplay = array_merge($process->copyunknown, $process->copymanual, $process->copyauto);
}
$optionsusermode = array ('without'  => get_string('without', 'automultiplechoice'),
                  'all'   => get_string('all'));

$selectusermode = new single_select($url, 'usermode', $optionsusermode, $usermode, null, "usermode");
$selectusermode->set_label(get_string('associationusermode', 'automultiplechoice'));
$paging = new paging_bar(count($namedisplay), $page, 20, $url, 'page');


echo $OUTPUT->render($selectmode);
echo $OUTPUT->render($selectusermode);
echo $OUTPUT->render($paging);
$namedisplay = array_slice($namedisplay, $page * $perpage, $perpage);
$excludeusers = ($usermode === 'all') ? '' : array_merge($process->copymanual, $process->copyauto);
echo html_writer::start_div('amc_thumbnails');
echo html_writer::start_tag('ul', array('class' => 'thumbnails'));
foreach ($namedisplay as $name => $idnumber) {
    $thumbnailoutput = \html_writer::img($process->getFileUrl('name-'.$name.".jpg"), $name);
    $thumbnailoutput .= \html_writer::div($output->students_selector($url, $cm, $idnumber, '', $excludeusers ), 'caption');
    $thumbnaildiv = \html_writer::div($thumbnailoutput, 'thumbnail');
    echo html_writer::tag('li', $thumbnaildiv );
}
echo html_writer::end_tag('ul');
echo html_writer::end_div();
echo $OUTPUT->box_end();


echo $output->footer();
