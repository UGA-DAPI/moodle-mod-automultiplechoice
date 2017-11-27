<?php

require_once(__DIR__ . '/locallib.php');

global $DB, $OUTPUT, $PAGE;
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$controller = new \mod_automultiplechoice\local\controllers\view_controller();
$quiz = $controller->getQuiz();
$cm = $controller->getCm();
$course = $controller->getCourse();
// Get the main renderer and sets current Tab.
$output = $controller->getRenderer();

require_capability('mod/automultiplechoice:update', $controller->getContext());

$PAGE->set_url('/mod/automultiplechoice/uploadscans.php', array('id' => $cm->id));


$uploadprocess = new \mod_automultiplechoice\local\amc\upload($quiz);
$process = new \mod_automultiplechoice\local\amc\process($quiz);
$amclog = new \mod_automultiplechoice\local\amc\logger($quiz->id);



$action = optional_param('action', '', PARAM_ALPHA);
if ($action === 'deleteUploads') {
    $uploadprocess->deleteUploads();
    redirect(new moodle_url('uploadscans.php', array('a' => $quiz->id)));
}

if ($action === 'delete') {
    $scan = optional_param('scan', 'all', PARAM_PATH);
    $uploadprocess->deleteFailed($scan);
    redirect(new moodle_url('uploadscans.php', array('a' => $quiz->id)));
}

// Form errors.
$errors = [];
// Uploaded file infos.
$uploaded = [];

$nbpages = 0;
if (isset ($_FILES['scanfile']) ) {
    //$errors = array();

    if ($_FILES['scanfile']['error'] > 0) {
        //echo $OUTPUT->box(get_string('error') . $_FILES['scanfile']['error'], 'errorbox');
        $errors[] = get_string('error') . $_FILES['scanfile']['error'];
    } else {
        $amclog->write('upload');
        $filename = '/tmp/' . $_FILES['scanfile']['name'];
        if (!move_uploaded_file($_FILES['scanfile']['tmp_name'], $filename)) { // safer than rename()
            //error(get_string('uploadscans_file_not_accessible', 'mod_automultiplechoice'));
            $errors[] = get_string('uploadscans_file_not_accessible', 'mod_automultiplechoice');
        }

        // This can also generate errors...
        $uploadprocess->upload($filename);
        $uploaderrors = $uploadprocess->getLastErrors();

        array_merge($errors, $uploaderrors);

        $scansStats = $process->statScans();

        $nbpages = $uploadprocess->nbPages;

        if (!$scansStats['count']) {
            $errors[] = get_string('uploadscans_no_image_known', 'mod_automultiplechoice', ['nbpages' => $nbpages]);
        }

        $uploaded = [
            'size' => round($_FILES['scanfile']['size'] / 1024),
            'name' =>  $_FILES['scanfile']['name'],
            'type' => $_FILES['scanfile']['type'],
            'location' => $filename
        ];
    }
} else {
    $scansStats = $process->statScans();
    $nbpages = $uploadprocess->nbPages;
}

echo $output->header('uploadscans');

$failed = [];
if (($scansStats) && (($scansStats['count'] - $scansStats['nbidentified']) > 0)) {
    $failed = $uploadprocess->get_failed_scans();
}

$data = [
    'stats' => $scansStats,
    'uploaded' => $uploaded,
    'errors' => $errors,
    'nbpages' => $nbpages,
    'scanfailed' => $failed,
    'faileddowloandurl' => $process->getFileUrl($process->normalizeFilename('failed'))->get_path()
];

$view = new \mod_automultiplechoice\output\view_scansupload($quiz, $data);
echo $output->render_scansupload_view($view);

echo $OUTPUT->footer();
