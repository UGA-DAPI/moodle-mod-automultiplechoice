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
$userid = optional_param('userid', '', PARAM_INT);

require_capability('mod/automultiplechoice:update', $controller->getContext());

/// Print the page header

$PAGE->set_url('/mod/automultiplechoice/annotating.php', array('id' => $cm->id));
$PAGE->requires->css(new moodle_url('assets/amc.css'));

$process = new amc\AmcProcessAnnotate($quizz);
if ($action === 'anotate') {
    if ($process->amcAnnotePDF()) {
        redirect(new moodle_url('annotating.php', array('a' => $quizz->id)));
    }
} else if ($action === 'setstudentaccess') {
    $quizz->studentaccess = optional_param('studentaccess', false, PARAM_BOOL);
    $quizz->corrigeaccess = optional_param('corrigeaccess', false, PARAM_BOOL);
    $quizz->save();
    redirect(new moodle_url('annotating.php', array('a' => $quizz->id)));
} else if ($action === 'notification') {
    $studentsto = $process->getUsersIdsHavingAnotatedSheets();
    $okSends = $process->sendAnotationNotification($studentsto);
    amc\FlashMessageManager::addMessage(
        ($okSends == count($studentsto)) ? 'success' : 'error',
        $okSends . " messages envoyés pour " . count($studentsto) . " étudiants ayant une copie annotée."
    );
    redirect(new moodle_url('annotating.php', array('a' => $quizz->id)));
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
    echo HtmlHelper::buttonWithAjaxCheck('Regénérer les copies corrigées', $quizz->id, 'annotating', 'anotate', 'process');
    echo "</div>";
}
if ($process->hasAnotatedFiles()) {
    $url = $process->getFileUrl( $process->normalizeFilename('corrections'));
    echo $OUTPUT->box_start('informationbox well');
    echo $OUTPUT->heading("Copies corrigées", 2)
        . $OUTPUT->heading("Fichiers", 3)
        . \html_writer::link($url, $process->normalizeFilename('corrections'), array('target' => '_blank'));
    echo "<p><b>" . $process->countIndividualAnotations() . "</b> copies individuelles annotées (pdf) disponibles.</p>";

    echo HtmlHelper::buttonWithAjaxCheck('Mettre à jour les copies corrigées (annotées)', $quizz->id, 'annotating', 'anotate', 'process');

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
/*
    $groupmode    = groups_get_course_groupmode($course);   // Groups are being used
    $currentgroup = groups_get_course_group($course, true);

    if (!$currentgroup) {      // To make some other functions work better later
        $currentgroup = NULL;
    }
    $context = context_module::instance($cm->id);
    $isseparategroups = ($course->groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context));
    
    if (has_students($context)==0) {
        // no separate group access, can view only self
        $user_selector = false;
    } else {
        $user_selector = true;
    }

    
    $showonlyactiveenrol = !has_capability('moodle/course:viewsuspendedusers', $context);
    if (empty($userid)) {
        $gui = new graded_users_iterator($course, null, $currentgroup);
        $gui->require_active_enrolment($showonlyactiveenrol);
        $gui->init();
        // Add tabs
        print_grade_page_head($courseid, 'report', 'user');
        //groups_print_activity_menu($cm, $urlroot, $return=false, $hideallparticipants=false);groups_print_course_menu($course, $gpr->get_return_url('index.php?id='.$courseid, array('userid'=>0)));

        if ($user_selector) {
            $renderer = $PAGE->get_renderer('gradereport_user');
            echo $renderer->graded_users_selector('user', $course, $userid, $currentgroup, true);
        }

      
    } else { // Only show one user's report
        $report = new grade_report_user($courseid, $gpr, $context, $userid);

        $studentnamelink = html_writer::link(new moodle_url('/user/view.php', array('id' => $report->user->id, 'course' => $courseid)), fullname($report->user));
        print_grade_page_head($courseid, 'report', 'user', get_string('pluginname', 'gradereport_user') . ' - ' . $studentnamelink);
        //groups_print_activity_menu($cm, $urlroot, $return=false, $hideallparticipants=false);

        if ($user_selector) {
            $renderer = $PAGE->get_renderer('gradereport_user');
            $showallusersoptions = true;
            echo $renderer->graded_users_selector('user', $course, $userid, $currentgroup, $showallusersoptions);
        }

       
    }

 */


    
} else {
    echo HtmlHelper::buttonWithAjaxCheck('Générer les copies corrigées (annotées)', $quizz->id, 'annotating', 'anotate', 'process');
}

echo $output->footer();
