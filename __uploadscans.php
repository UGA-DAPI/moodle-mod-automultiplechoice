<?php

require_once(__DIR__ . '/locallib.php');

global $DB, $OUTPUT, $PAGE;
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$controller = new \mod_automultiplechoice\local\controllers\view_controller();
$quiz = $controller->getQuiz();
$cm = $controller->getCm();
$course = $controller->getCourse();
$output = $controller->getRenderer('uploadscans');

require_capability('mod/automultiplechoice:update', $controller->getContext());

$PAGE->set_url('/mod/automultiplechoice/uploadscans.php', array('id' => $cm->id));


$process = new \mod_automultiplechoice\local\amc\upload($quiz);
$amclog = new \mod_automultiplechoice\local\amc\logger($quiz->id);


$action = optional_param('action', '', PARAM_ALPHA);
if ($action === 'deleteUploads') {
    $process->deleteUploads();
    redirect(new moodle_url('uploadscans.php', array('a' => $quiz->id)));
}

if ($action === 'delete') {
    $scan = optional_param('scan', 'all', PARAM_PATH);
    $process->deleteFailed($scan);
    redirect(new moodle_url('uploadscans.php', array('a' => $quiz->id)));
}
if (isset ($_FILES['scanfile']) ) { // Fichier reçu ?
    $errors = array();

    if ($_FILES['scanfile']["error"] > 0) {
        echo $OUTPUT->box(get_string('error') . $_FILES['scanfile']['error'], 'errorbox');
    } else {
        $amclog->write('upload');
        $filename = '/tmp/' . $_FILES['scanfile']['name'];
        if (!move_uploaded_file($_FILES['scanfile']['tmp_name'], $filename)) { // safer than rename()
            error(get_string('uploadscans_file_not_accessible', 'mod_automultiplechoice'));
        }

        $process->upload($filename);

        $scansStats = $process->statScans();
        if (!$scansStats['count']) {
            $errors[] = get_string('uploadscans_no_image_known', 'mod_automultiplechoice', ['nbpages' => $npages]);
        }

        // Output starts here
        echo $output->header(); // if the upload went well, the last tab will be enabled!
        foreach ($errors as $errorMsg) {
            echo $OUTPUT->box($errorMsg, 'errorbox');
        }
        if (!empty($scansStats['count'])) {
            echo $OUTPUT->notification(
                get_string(
                    'uploadscans_process_end_message',
                    'mod_automultiplechoice',
                    ['nbpages' => $npages, 'nbextracted' => $scansStats['count'], 'nbidentified' => $scansStats['nbidentified']]
                ),
                'notifymessage'
            );
        }

        $ko = round($_FILES['scanfile']['size'] / 1024);
        echo '<dl>';
        echo '<dt>';
        echo get_string('file');
        echo '</dt>';
        echo '<dd>';
        echo $_FILES['scanfile']['name'];
        echo '<dd>';
        echo '<dt>';
        echo get_string('file_type', 'mod_automultiplechoice');
        echo '</dt>';
        echo '<dd>';
        echo $_FILES['scanfile']['type'];
        echo '<dd>';
        echo '<dt>';
        echo get_string('size');
        echo '</dt>';
        echo '<dd>';
        echo $ko . 'Ko';
        echo '<dd>';
        echo '<dt>';
        echo get_string('location');
        echo '</dt>';
        echo '<dd>';
        echo $filename;
        echo '<dd>';
        echo '</dl>';
    }
} else {
    echo $output->header();
    $scansStats = $process->statScans();
}

// File upload.
if ($scansStats) {
    foreach (\mod_automultiplechoice\local\helpers\log::build($quiz->id)->check('upload') as $warning) {
        echo $OUTPUT->notification($warning, 'notifyproblem');
    }

    echo '<p class="notifymessage alert alert-info">';
    echo get_string('uploadscans_saved_sheets', 'mod_automultiplechoice', ['nbsaved' => $scansStats['count'], 'date' => $scansStats['timefr']]);
    echo '</p>';
    echo $OUTPUT->heading(get_string('uploadscans_add_sheets', 'mod_automultiplechoice'), 3);
    echo '<p>' . get_string('uploadscans_add_sheets_message', 'mod_automultiplechoice') . '</p>';
} else {
    echo '<p>'. get_string('uploadscans_no_sheets_uploaded', 'mod_automultiplechoice') .'</p>';
}
?>
<form id="form-uploadscans" action="uploadscans.php?a=<?php echo $quiz->id; ?>" method="post" enctype="multipart/form-data">
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
    echo $OUTPUT->heading(get_string('uploadscans_delete_sheets', 'mod_automultiplechoice'), 3);
    ?>
    <form action="?a=<?php echo $quiz->id; ?>" method="post" enctype="multipart/form-data">
        <p>
            <?php echo get_string('uploadscans_delete_sheets_warn', 'mod_automultiplechoice'); ?>
        </p>
        <div>
            <input type="hidden" name="action" value="deleteUploads" />
            <button type="submit" onclick="return confirm('<?php echo get_string('uploadscans_delete_sheets_confirm', 'mod_automultiplechoice') ?>');">
                <?php echo get_string('uploadscans_delete_sheets', 'mod_automultiplechoice'); ?>
            </button>
        </div>
    </form>
    <?php
}
if (($scansStats) && (($scansStats['count'] - $scansStats['nbidentified']) > 0)) {
    $array_failed = $process->get_failed_scans();
    if ($array_failed){
        $failedoutput = $OUTPUT->heading(get_string('uploadscans_unknown_scans', 'mod_automultiplechoice'), 3, 'helptitle');
        $failedoutput .= \html_writer::start_div('box generalbox boxaligncenter');
        $deleteallurl = new \moodle_url('uploadscans.php', array('a' => $quiz->id, 'action' => 'delete', 'scan' => 'all'));
        $deleteallbutton = new \single_button($deleteallurl, get_string('uploadscans_delete_unknown_scans', 'mod_automultiplechoice'));
        $deleteallbutton->add_confirm_action(get_string('confirm'));
        $downloadfailedurl = $process->getFileUrl($process->normalizeFilename('failed'));
        $failedoutput .= $OUTPUT->render($deleteallbutton);
        $failedoutput .= \html_writer::link($downloadfailedurl, get_string('uploadscans_download_unknown_scans', 'mod_automultiplechoice'), array('class' => 'btn', 'target '=> '_blank'));
        $failedoutput .= \html_writer::start_div('amc_thumbnails_failed row');
        $failedoutput .= \html_writer::start_div('thumbnails ');
        foreach ($array_failed as $scan) {
            $url = new \moodle_url(
                'uploadscans.php',
                array('a' => $quiz->id, 'action' => 'delete', 'scan' => $scan)
            );
            $deleteicon = $OUTPUT->action_icon($url, new \pix_icon('t/delete', get_string('delete')), new \confirm_action(get_string('confirm')));
            $scanoutput = \html_writer::link($process->getFileUrl($scan), \html_writer::img($process->getFileUrl($scan), $scan));
            $scanoutput .= \html_writer::div($deleteicon, 'caption');

            $failedoutput .= \html_writer::div($scanoutput, 'thumbnail col-xs-12 col-sm-6 col-md-4 col-lg-3');


        }
        $failedoutput .= \html_writer::end_div();
        $failedoutput .= \html_writer::end_div();
        $failedoutput .= \html_writer::end_div();
    } else {
        $failedoutput = get_string('uploadscans_install_sqlite3', 'mod_automultiplechoice');
    }

    echo $failedoutput;

}
echo $output->footer();
