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
     *
     * @var array a set of usefull data
     */
    protected $data;

    /**
     * Contruct
     *
     * @param mod_automultiplechoice/local/models/quiz $quiz A quiz
     * @param array $data A set of usefull data
     */
    public function __construct($quiz, $data) {
        $this->quiz = $quiz;
        $this->data = $data;
    }

    /**
     * Prepare data for use in a template
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        $content = [
          'quiz' => $this->quiz,
          'errors' => $this->data['errors'],
          'fileinfos' => $this->data['uploaded'],
          'showstats' => !empty($this->data['stats']['count']),
          'formsubmited' => count($this->fileinfos) > 0,
          'scanstats' => $this->data['stats'],
          'nbpages' => $this->data['nbpages'],
          'logs' => $this->data['logs'],
          'showfailed' => count($this->data['scanfailed']) > 0,
          'failed' => $this->data['scanfailed'],
          'downloadfailedurl' => $this->data['failedurl'],
          'showsqlitemessage' => $this->data['showsqlitemessage']
        ];
        return $content;
    }
}
