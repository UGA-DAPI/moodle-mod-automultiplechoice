<?php

/**
 * @package    mod_automultiplechoice
 * @copyright  2014 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \mod\automultiplechoice as amc;

require_once(__DIR__ . '/locallib.php');

require_once __DIR__ . '/models/AmcProcessAssociate.php';

global $DB, $OUTPUT, $PAGE;
/* @var $DB moodle_database */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$controller = new amc\Controller();
$quizz = $controller->getQuizz();
$cm = $controller->getCm();
$course = $controller->getCourse();
$output = $controller->getRenderer('associating');

$action         = optional_param('action', '', PARAM_ALPHA);
$mode           = optional_param('mode', 'unknown', PARAM_ALPHA);
$usermode       = optional_param('usermode', 'without', PARAM_ALPHA);
$idnumber       = optional_param('idnumber', '',PARAM_ALPHA);
$copy           = optional_param('copy', '', PARAM_INT);
$page           = optional_param('page', 0, PARAM_INT);
$perpage        = optional_param('perpage', 20, PARAM_INT);        // how many per page
    
require_capability('mod/automultiplechoice:update', $controller->getContext());

/// Print the page header

$PAGE->set_url('/mod/automultiplechoice/associating.php', array('id' => $cm->id));
$PAGE->requires->css(new moodle_url('assets/amc.css'));
$url = new moodle_url('associating.php', array('a' => $quizz->id,'mode'=>$mode,'usermode'=>$usermode));
$process = new amc\AmcProcessAssociate($quizz);
 if ($action === 'associate') {
    if ($process->associate()) {
        redirect($url);
    }
} 
$process->get_association();


// Output starts here
echo $output->header();

echo $OUTPUT->box_start('informationbox well');
echo $OUTPUT->heading("Association", 2)
    . "<p>" . count($process->copyauto)." copies automatiquement identifiés, ".count($process->copymanual) . " copies manuellement identifiées et " . count($process->copyunknown) . " non identifiées. </p>";
$warnings = amc\Log::build($quizz->id)->check('associating');
if ($warnings) {
    echo '<div class="informationbox notifyproblem alert alert-error">';
    foreach ($warnings as $warning) {
        echo $warning;
    }

    echo "<br /><br />";
    echo $OUTPUT->single_button( new moodle_url('/mod/automultiplechoice/associating.php'),
                                array( 'a'=>$quizz->id, 'action'=> 'associate')
                                , 'Relancer l\'association');
    echo "</div>";
}else if (count($process->copyauto)){
echo $OUTPUT->single_button( new moodle_url('/mod/automultiplechoice/associating.php'),
                                array( 'a'=>$quizz->id, 'action'=> 'associate')
                                , 'Lancer l\'association');
}else{
echo $OUTPUT->single_button( new moodle_url('/mod/automultiplechoice/associating.php'),
                                array( 'a'=>$quizz->id, 'action'=> 'associate')
                                , 'Relancer l\'association');
}
$optionsmode =  array ('unknown'  => get_string('unknown', 'automultiplechoice'),
                  'manual' => get_string('manual', 'automultiplechoice'),
                  'auto' => get_string('auto', 'automultiplechoice'),
                  'all'   => get_string('all'));
$selectmode = new single_select($url, 'mode', $optionsmode, $mode, null, "mode");
$selectmode->set_label(get_string('associationmode', 'automultiplechoice'));
if ($mode=='unknown'){
    $namedisplay = $process->copyunknown;
}else if ($mode=='manual'){
    $namedisplay = $process->copymanual;
}else if ($mode=='auto'){
    $namedisplay = $process->copyauto;
}else if ($mode=='all'){
    $namedisplay = array_merge($process->copyunknown,$process->copymanual,$process->copyauto);
}
$optionsusermode =  array ('without'  => get_string('without', 'automultiplechoice'),
                  'all'   => get_string('all'));
$selectusermode = new single_select($url, 'usermode', $optionsusermode, $usermode, null, "usermode");
$selectusermode->set_label(get_string('associationusermode', 'automultiplechoice'));
$paging =  new paging_bar(count($namedisplay), $page, 20, $url, 'page');


echo $OUTPUT->render($selectmode);
echo $OUTPUT->render($selectusermode);
echo $OUTPUT->render($paging);
$namedisplay = array_slice($namedisplay,$page*$perpage, $perpage);
$excludeusers = ($usermode=='all') ? '' : array_merge($process->copymanual,$process->copyauto);
echo html_writer::start_div('amc_thumbnails');
echo html_writer::start_tag('ul',array('class'=>'thumbnails'));
foreach ($namedisplay as $name=>$idnumber){
   
    $thumbnailoutput = \html_writer::img($process->getFileUrl('name-'.$name.".jpg"),$name);
    $thumbnailoutput .= \html_writer::div($output->students_selector($url, $cm, $idnumber, '',$excludeusers ),'caption');
    $thumbnaildiv= \html_writer::div($thumbnailoutput,'thumbnail');
    echo html_writer::tag('li', $thumbnaildiv ); 
}
echo html_writer::end_tag('ul');
echo html_writer::end_div();
echo $OUTPUT->box_end();


echo $output->footer();
