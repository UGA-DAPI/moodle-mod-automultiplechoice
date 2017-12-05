<?php

require_once(__DIR__ . '/locallib.php');

global $DB, $OUTPUT, $PAGE;
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$sharedservice = new \mod_automultiplechoice\shared_service();
$quiz = $sharedservice->getQuiz();
$cm = $sharedservice->getCm();
$course = $sharedservice->getCourse();
// Get the main renderer and sets current Tab.
$output = $sharedservice->getRenderer();

require_capability('mod/automultiplechoice:update', $sharedservice->getContext());

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
if (isset ($_FILES['scanfile'])) {

    if ($_FILES['scanfile']['error'] > 0) {
        $errors[] = get_string('error') . $_FILES['scanfile']['error'];
    } else {
        $amclog->write('upload');
        $filename = '/tmp/' . $_FILES['scanfile']['name'];
        if (!move_uploaded_file($_FILES['scanfile']['tmp_name'], $filename)) {
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
$showsqlitemsg = false;
if (($scansStats) && (($scansStats['count'] - $scansStats['nbidentified']) > 0)) {
    if (extension_loaded('sqlite3')) {
        $failedscans = $uploadprocess->get_failed_scans();
        foreach ($failedscans as $id => $scan) {
            $failed[] = [
                'id' => $scan,
                'link' => $uploadprocess->getFileActionUrl($scan)
            ];
        }
    } else {
        $showsqlitemsg = true;
    }
}

$data = [
    'stats' => $scansStats,
    'uploaded' => $uploaded,
    'errors' => $errors,
    'nbpages' => $nbpages,
    'scanfailed' => $failed,
    'failedurl' => $process->getFileActionUrl($process->normalizeFilename('failed'))->get_path(),
    'logs' =>  \mod_automultiplechoice\local\helpers\log::build($quiz->id)->check('upload'),
    'showsqlitemsg' => $showsqlitemsg
];

$view = new \mod_automultiplechoice\output\view_scansupload($quiz, $data);
echo $output->render_scansupload_view($view);

echo $OUTPUT->footer();
