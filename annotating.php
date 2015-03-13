<?php

/**
 * @package    mod_automultiplechoice
 * @copyright  2014 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \mod\automultiplechoice as amc;

require_once(__DIR__ . '/locallib.php');
require_once __DIR__ . '/models/AmcProcessAnnotate.php';


global $DB, $OUTPUT, $PAGE;
/* @var $DB moodle_database */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$controller = new amc\Controller();
$quizz = $controller->getQuizz();
$cm = $controller->getCm();
$course = $controller->getCourse();
$output = $controller->getRenderer('annotating');

$action = optional_param('action', '', PARAM_ALPHA);
$idnumber = optional_param('idnumber', '', PARAM_INT);
$copy = optional_param('copy', '', PARAM_INT);
$group = optional_param('group', '', PARAM_INT);
$page         = optional_param('page', 0, PARAM_INT);
$perpage      = optional_param('perpage', 20, PARAM_INT);        // how many per page

require_capability('mod/automultiplechoice:update', $controller->getContext());

/// Print the page header

$PAGE->set_url('/mod/automultiplechoice/annotating.php', array('id' => $cm->id));
$PAGE->requires->css(new moodle_url('assets/amc.css'));

$process = new amc\AmcProcessAnnotate($quizz);
if ($action === 'annotate') {
    if ($process->amcAnnote()) {
        redirect($PAGE->url);
    }
} else if ($action === 'setstudentaccess') {
    $quizz->studentaccess = optional_param('studentaccess', false, PARAM_BOOL);
    $quizz->corrigeaccess = optional_param('corrigeaccess', false, PARAM_BOOL);
    $quizz->save();
    redirect($PAGE->url);
} else if ($action === 'notification') {
    $studentsto = $process->getUsersIdsHavingAnotatedSheets();
    $okSends = $process->sendAnotationNotification($studentsto);
    amc\FlashMessageManager::addMessage(
        ($okSends == count($studentsto)) ? 'success' : 'error',
        $okSends . " messages envoyés pour " . count($studentsto) . " étudiants ayant une copie annotée."
    );
    redirect($PAGE->url);
}



// Output starts here
echo $output->header();

$warnings = amc\Log::build($quizz->id)->check('annotating');
if ($warnings) {
    echo '<div class="informationbox notifyproblem alert alert-error">';
    foreach ($warnings as $warning) {
        echo $warning;
    }

    echo "<br /><br />";
    echo $OUTPUT->single_button( new moodle_url('/mod/automultiplechoice/annotating.php',
                                array( 'a'=>$quizz->id, 'action'=> 'annotate'))
                                , 'Regénérer les copies corrigées');
    echo "</div>";
}
if ($process->countAnnotatedFiles()>0) {
   $url = $process->getFileUrl( $process->normalizeFilename('corrections'));
    echo $OUTPUT->box_start('informationbox well');
    echo $OUTPUT->heading("Copies corrigées", 2)
        . $OUTPUT->heading("Fichiers", 3)
        . \html_writer::link($url, $process->normalizeFilename('corrections'), array('target' => '_blank'));
    echo "<p><b>" . $process->countAnnotatedFiles() . "</b> copies individuelles annotées disponibles.</p>";
    echo $OUTPUT->single_button( new moodle_url('/mod/automultiplechoice/annotating.php',
                                array( 'a'=>$quizz->id, 'action'=> 'annotate'))
                                , 'Mettre à jour les copies corrigées (annotées)');
/*

    echo $OUTPUT->heading("Accès aux copies", 3);
    echo "<p>Permettre l'accès de chaque étudiant</p>\n";
    echo '<form action="?a=' . $quizz->id .'" method="post">' . "\n";
    echo '<ul>';
    $ckcopie = ($quizz->studentaccess ? 'checked="checked"' : '');
    $ckcorrige = ($quizz->corrigeaccess ? 'checked="checked"' : '');
    echo '<li><input type="checkbox" name="studentaccess" ' .$ckcopie. '>à sa copie corrigée annotée</input></li>' ;
    echo '<li><input type="checkbox" name="corrigeaccess" ' .$ckcorrige. '>au corrigé complet</input></li>' ;
    echo '</ul>';
    echo '<input type="hidden" name="action" value="setstudentaccess" value="1" />';
    echo '<button type="submit">Permettre ces accès</button>';
    echo '</form>';

    echo $OUTPUT->heading("Envoi des copies", 3);
    echo $OUTPUT->single_button(
        new moodle_url(
            '/mod/automultiplechoice/annotating.php',
            array('a' => $quizz->id, 'action' => 'notification')
        ),
        'Envoyer la correction par message Moodle à chaque étudiant',
        'post'
    );
    echo $OUTPUT->box_end();
 */
    $groupmode    = groups_get_activity_groupmode($cm);   // Groups are being used
    $currentgroup = groups_get_activity_group($cm, true);

    if (!$currentgroup) {      // To make some other functions work better later
        $currentgroup = NULL;
    }
    $context = context_module::instance($cm->id);
    $isseparategroups = ($cm->groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context));
    
    $users = amc_get_student_users($cm,true, $group);
    $noenrol = false;
    if (count($users) == 0){
        $noenrol =true;
    }else{
       $process->get_association();
       $userscopy= array_flip(array_merge($process->copymanual,$process->copyauto));
    }

    if (empty($idnumber) and empty($copy)) {

       if ($noenrol) {
             $users = array_map('get_code',glob($process->workdir . '/cr/name-*.jpg'));       
       
       }else{
           $url = new moodle_url('annotating.php', array('a' => $quizz->id));
           groups_print_activity_menu($cm, $url);
           echo $output->students_selector($url, $cm, $idnumber, $currentgroup);
       }

       $paging =  new paging_bar(count($users), $page, 20, $url, 'page');

       echo $OUTPUT->render($paging);
       $usersdisplay = array_slice($users,$page*$perpage, $perpage);
       echo html_writer::start_div('amc_thumbnails');
       echo html_writer::start_tag('ul',array('class'=>'thumbnails'));
       foreach ($usersdisplay as $user){
           if (!$noenrol){
               if (isset($userscopy[$user->idnumber])){
                  $name= $userscopy[$user->idnumber]; 
               }else{
              $name="0_0"; 
               }
           }
           $copy = explode('_',$name);
           $thumbnailimg = \html_writer::img($process->getFileUrl('name-'.$name.".jpg"),$name);
           $thumbnailoutput = \html_writer::link(new moodle_url('annotating.php', array('a' => $quizz->id,'copy'=>$copy[0],'idnumber'=>$copy[1])),$thumbnailimg,array('class'=>'thumbnail'));
           echo html_writer::tag('li', $thumbnailoutput ); 
       }
       echo html_writer::end_tag('ul');
        echo html_writer::end_div();
      
    } else { // Only show one user's report

        if (!$noenrol) {
            $url = new moodle_url('annotating.php', array('a' => $quizz->id));
            groups_print_activity_menu($cm, $url);
            echo $output->students_selector($url, $cm, $idnumber, $currentgroup);
        }
    if (!$copy){
        $name = $userscopy[$idnumber];
        list($copy,$idnumber) = explode('_',$name);
    }
    $pages = glob($process->workdir . '/cr/corrections/jpg/page-'.$copy.'-*-'.$idnumber.'.jpg');
//var_dump($pages);     error($pages);
    foreach ($pages as $page){
        echo \html_writer::img($process->getFileUrl(basename($page)),$page);
    }
       
    }




    
} else {
        echo $OUTPUT->single_button( new moodle_url('/mod/automultiplechoice/annotating.php',
                                array( 'a'=>$quizz->id, 'action'=> 'annotate'))
                                , 'Générer les copies corrigées');
}

echo $output->footer();
