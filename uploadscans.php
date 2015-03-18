<?php

/**
 * Upload then analyzes the scanned pages
 *
 * @package    mod_automultiplechoice
 * @copyright  2013 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \mod\automultiplechoice as amc;

require_once(__DIR__ . '/locallib.php');
require_once(__DIR__ . '/models/AmcProcessUpload.php');

global $DB, $OUTPUT, $PAGE;
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$controller = new amc\Controller();
$quizz = $controller->getQuizz();
$cm = $controller->getCm();
$course = $controller->getCourse();
$output = $controller->getRenderer('uploadscans');

require_capability('mod/automultiplechoice:update', $controller->getContext());

/// Print the page header

$PAGE->set_url('/mod/automultiplechoice/uploadscans.php', array('id' => $cm->id));
$PAGE->requires->css(new moodle_url('assets/amc.css'));

$process = new \mod\automultiplechoice\AmcProcessUpload($quizz);
$amclog = new \mod\automultiplechoice\Log($quizz->id);
//var_dump($process);

$action = optional_param('action', '', PARAM_ALPHA);
if ($action === 'deleteUploads') {
    $process->deleteUploads();
    redirect(new moodle_url('uploadscans.php', array('a' => $quizz->id)));
}

if ($action === 'delete') {
    $scan =  optional_param('scan', 'all', PARAM_FILE);
    $process->deleteFailed($scan);
    redirect(new moodle_url('uploadscans.php', array('a' => $quizz->id)));
}
if (isset ($_FILES['scanfile']) ) { // Fichier reçu ?
    $errors = array();

    if ($_FILES['scanfile']["error"] > 0) {
        echo $OUTPUT->box("Erreur : " . $_FILES['scanfile']['error'], 'errorbox');
    } else {
        $amclog->write('upload');
        $filename = '/tmp/' . $_FILES['scanfile']['name'];
        if (!move_uploaded_file($_FILES['scanfile']['tmp_name'], $filename)) { // safer than rename()
            error("Impossible d'accéder au fichier déposé");
        }

        $process->upload($filename);

        $scansStats = $process->statScans();
        if (!$scansStats['count']) {
            $errors[] = "Erreur, $npages pages scannées mais aucune image n'a été reconnue (pas de PPM).";
        }

        // Output starts here
        echo $output->header(); // if the upload went well, the last tab will be enabled!
        foreach ($errors as $errorMsg) {
            echo $OUTPUT->box($errorMsg, 'errorbox');
        }
        if (!empty($scansStats['count'])) {
            echo $OUTPUT->notification(
                "Le processus s'est achevé : {$process->nbPages} pages nouvellement scannées, {$scansStats['count']} extraites, {$scansStats['nbidentified']} pages avec marqueurs.",
                'notifymessage'
            );
        }

        $ko = round($_FILES['scanfile']['size'] / 1024);
        echo "<dl>
            <dt>Fichier déposé</dt> <dd>{$_FILES['scanfile']['name']}</dd>
            <dt>Type</dt> <dd>{$_FILES['scanfile']['type']}</dd>
            <dt>Taille</dt> <dd>{$ko} ko</dd>
            <dt>Emplacement</dt> <dd>{$filename}</dd>
            </dl>\n";
    }
} else {
    echo $output->header();
    $scansStats = $process->statScans();
}

// Upload du fichier
if ($scansStats) {
    foreach (amc\Log::build($quizz->id)->check('upload') as $warning) {
        echo $OUTPUT->notification($warning, 'notifyproblem');
    }

    echo '<p class="notifymessage alert alert-info">' . "Copies enregistrées : <b>{$scansStats['count']}</b> pages scannées ont été déposées le {$scansStats['timefr']}.</p>\n";
    echo $OUTPUT->heading("Ajouter des copies", 3);
    echo "<p>Si vous déposez de nouvelles pages scannées, elles seront ajoutées aux précédentes.</p>";
} else {
    echo "<p>Aucune copie n'a encore été déposée.</p>";
}
?>
<form id="form-uploadscans" action="uploadscans.php?a=<?php echo $quizz->id; ?>" method="post" enctype="multipart/form-data">
    <div>
        <label for="scanfile">Fichier scan (PDF ou TIFF)</label>
        <input type="file" name="scanfile" id="scanfile" accept="application/pdf,image/tiff">
    </div>
    <div>
        <input type="submit" name="submit" value="Envoyer">
    </div>
</form>
<?php
if ($scansStats) {
    echo $OUTPUT->heading("Effacer les copies", 3);
    ?>
    <form action="?a=<?php echo $quizz->id; ?>" method="post" enctype="multipart/form-data">
        <p>
            Vous pouvez effacer les copies déjà déposées.
            Ceci effacera aussi les notes.
            Vous pourrez ensuite déposer de nouveaux scans.
        </p>
        <div>
            <input type="hidden" name="action" value="deleteUploads" />
            <button type="submit" onclick="return confirm('Supprimer définitivement les copies déposées sur le serveur ?');">Effacer les copies déposées</button>
        </div>
    </form>
    <?php
}
if (($scansStats) && (($scansStats['count']-$scansStats['nbidentified'])>0)){
    $array_failed= $process->get_failed_scans();
    if ($array_failed){
        $failedoutput = $OUTPUT->heading('Scans non reconnus',3,'helptitle');
        $failedoutput .= \html_writer::start_div('box generalbox boxaligncenter');
        $deleteallurl = new \moodle_url('uploadscans.php', array('a' => $quizz->id, 'action' => 'delete','scan'=>'all'));
        $deleteallbutton= new \single_button($deleteallurl, 'Effacer tous les scans non reconnus');
        $deleteallbutton->add_confirm_action(get_string('confirm'));
        $downloadfailedurl = $process->getFileUrl($process->normalizeFilename('failed'));
        $failedoutput .= $OUTPUT->render($deleteallbutton);
        $failedoutput .= \html_writer::link($downloadfailedurl, 'Télécharger tous les scans non reconnus',array('class'=>'btn','target'=>'_blank'));
        $failedoutput .= \html_writer::start_tag('ul',array('class'=>'unlist'));
        foreach ($array_failed as $scan) {
            $url = new \moodle_url('uploadscans.php', array('a'=>$quizz->id,'action'=>'delete', 'scan'=>$scan));
            $deleteicon = $OUTPUT->action_icon($url,new \pix_icon('t/delete',get_string('delete')),new \confirm_action(get_string('confirm')));
            $scanoutput = \html_writer::link($process->getFileUrl($scan),$scan);
            $failedoutput .= \html_writer::tag('li', $scanoutput . $deleteicon); 
        }
        $failedoutput .= \html_writer::end_tag('ul' );
        $failedoutput .= \html_writer::end_div();
    }else{
        $failedoutput = 'Demandez à votre administrateur système d\'installer php-sqlite3 pour voir les fichiers non reconnus';
    }

        echo $failedoutput;
    
}
echo $output->footer();
