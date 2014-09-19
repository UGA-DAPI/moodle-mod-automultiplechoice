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

$PAGE->set_url('/mod/automultiplechoice/scan.php', array('id' => $cm->id));
$PAGE->requires->css(new moodle_url('assets/amc.css'));

// Output starts here
echo $output->header();

$process = new \mod\automultiplechoice\AmcProcess($quizz);
$amclog = new \mod\automultiplechoice\Log($quizz->id);
//var_dump($process);

if (isset ($_FILES['scanfile']) ) { // Fichier reçu
    if ($_FILES['scanfile']["error"] > 0) {
        echo "Erreur: " . $_FILES['scanfile']['error'] . "<br>";
    } else {
        $filename = '/tmp/' . $_FILES['scanfile']['name'];
        rename($_FILES['scanfile']['tmp_name'], $filename);
        $amclog->write('upload');

        echo "Upload : " . $_FILES['scanfile']['name'] . "<br>";
        echo "Type : " . $_FILES['scanfile']['type'] . "<br>";
        echo "Taille : " . round($_FILES['scanfile']['size'] / 1024) . " ko<br>";
        echo "Emplacement : " . $filename;
        echo "<br><br>\n";

        /** @todo ce bloc meptex est-il nécessaire ? **/
        $diag = $process->amcMeptex();
        if (!$diag) {
            echo "<p>Erreur lors du calcul de mise en page (amc meptex).</p>\n";
        }

        $npages = $process->amcGetimages($filename);
        if ($npages) {
            echo "Pages : " . $npages ."<br>";
        } else {
            echo "Erreur découpage scan (amc getimages) <br>";
        }

        $analyse = $process->amcAnalyse(true);
        if (!$analyse) {
            echo "Erreur lors de l'analyse (amc analyse) <br>.";
        }

    }
} else {
    // Upload du fichier
    ?>
    <form id="form-uploadscans" action="uploadscans.php?a=<?php echo $quizz->id; ?>" method="post" enctype="multipart/form-data">
        <div>
            <label for="scanfile">Fichier scan (PDF, TIFF…)</label>
            <input type="file" name="scanfile" id="scanfile"><br>
        </div>
        <div>
            <input type="submit" name="submit" value="Envoyer">
        </div>
    </form>
    <?php
}

echo button_back_to_activity($quizz->id);

echo $output->footer();
