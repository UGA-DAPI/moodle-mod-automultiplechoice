<?php
namespace mod_automultiplechoice\output;


defined('MOODLE_INTERNAL') || die();

class view_scansupload implements \renderable, \templatable {
    /**
     * The auto multiple choice quiz.
     *
     * @var \mod_automultiplechoice\local\models\quiz
     */
    protected $quiz;

    /**
     * Statistics from amc process scan.
     *
     * @var Array
     */
    protected $scanstats;

    /**
     * Uploaded file informations.
     *
     * @var Array
     */
    protected $fileinfos;

    /**
     * Form and process errors.
     *
     * @var Array
     */
    protected $errors;

    /**
     * Number of page scanned.
     *
     * @var int
     */
    protected $nbpages;

    /**
     * Array of failed scans.
     *
     * @var Array
     */
    protected $failed;

    /**
     * String url
     *
     * @var String
     */
    protected $failedurl;

    /**
     * Contruct
     *
     * @param mod_automultiplechoice/local/models/quiz $quiz A quiz
     * @param array $data
     */
    public function __construct($quiz, $data) {
        $this->quiz = $quiz;
        $this->errors = $data['errors'];
        $this->scanstats = $data['stats'];
        $this->fileinfos = $data['uploaded'];
        $this->nbpages = $data['nbpages'];
        $this->failed = $data['scanfailed'];
        $this->failedurl = $data['failedurl'];
    }

    /**
     * Prepare data for use in a template
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        $logs = \mod_automultiplechoice\local\helpers\log::build($this->quiz->id)->check('upload');
        $failed = [];
        $process = new \mod_automultiplechoice\local\amc\upload($this->quiz);

        foreach ($this->failed as $id => $scan) {
            $failed[] = [
                'id' => $scan,
                'link' => $process->getFileUrl($scan)
            ];
        }

        $content = [
          'quiz' => $this->quiz,
          'errors' => $this->errors,
          'fileinfos' => $this->fileinfos,
          'showstats' => !empty($this->scanstats['count']),
          'formsubmited' => count($this->fileinfos) > 0,
          'scanstats' => $this->scanstats,
          'nbpages' => $this->nbpages,
          'logs' => $logs,
          'showfailed' => count($failed) > 0,
          'failed' => $failed,
          'downloadfailedurl' => $this->failedurl,
          'showsqlitemessage' => !$failed || count($failed) === 0
        ];
        return $content;
    }
}
