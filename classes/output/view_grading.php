<?php

namespace mod_automultiplechoice\output;

defined('MOODLE_INTERNAL') || die();

class view_grading implements \renderable, \templatable {
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
     * Construct
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
            'showerrors' => $this->data['showerrors'],
            'nbusersknown' => $this->data['nbusersknown'],
            'nbusersunknown' => $this->data['nbusersunknown'],
            'filesurls' => $this->data['filesurls'],
            'stats' => $this->data['stats']
        ];

        return $content;
    }
}
