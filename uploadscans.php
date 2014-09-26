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

global $DB, $OUTPUT, $PAGE;
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$controller = new amc\Controller();
$quizz = $controller->getQuizz();
$cm = $controller->getCm();
$course = $controller->getCourse();
$output = $controller->getRenderer('uploadscans');

require_capability('mod/automultiplechoice:view', $controller->getContext());

/// Print the page header

$PAGE->set_url('/mod/automultiplechoice/uploadscans.php', array('id' => $cm->id));
$PAGE->requires->css(new moodle_url('assets/amc.css'));

$process = new \mod\automultiplechoice\AmcProcess($quizz);
$amclog = new \mod\automultiplechoice\Log($quizz->id);
//var_dump($process);

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

        if (!$process->amcMeptex()) {
            $errors[] = "Erreur lors du calcul de mise en page (amc meptex).";
        }

        $npages = $process->amcGetimages($filename);
        if (!$npages) {
            $errors[] = "Erreur découpage scan (amc getimages)";
        }

        $analyse = $process->amcAnalyse(true);
        if (!$analyse) {
            $errors[] = "Erreur lors de l'analyse (amc analyse).";
        }

        $scansStats = $process->statScans();
        if (!$scansStats['count']) {
            $errors[] = "Erreur, $npages pages scannées mais aucune image n'a été reconnue (pas de PPM).";
        }

        // Output starts here
        echo $output->header(); // if the upload went well, the last tab will be enabled!
        if (!empty($scansStats['count'])) {
            echo $OUTPUT->box("Le processus s'est achevé : $npages pages scannées, {$scansStats['count']} traitées.", 'informationbox');
        }
        foreach ($errors as $errorMsg) {
            echo $OUTPUT->box($errorMsg, 'errorbox');
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
    // Upload du fichier
    // Output starts here
    echo $output->header();
    $scans = $process->statScans();
    if ($scans) {
        echo "<p>{$scans['count']} pages scannées ont été déposées le {$scans['timefr']}.</p>\n";
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
}

echo $output->footer();
