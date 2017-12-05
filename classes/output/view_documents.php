<?php

namespace mod_automultiplechoice\output;

defined('MOODLE_INTERNAL') || die();

class view_documents implements \renderable, \templatable {
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
            'ziplink' => $this->data['ziplink'],
            'pdflinks' => $this->data['pdflinks'],
            'canrestore' => $this->data['canrestore'],
            'canlock' => $this->data['canlock']
        ];

        return $content;
    }
}
