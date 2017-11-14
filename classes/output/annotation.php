<?php

namespace mod_automultiplechoice\output;

defined('MOODLE_INTERNAL') || die();

class annotation implements \renderable, \templatable {
    /**
     * The auto multiple choice quiz.
     *
     * @var \mod_automultiplechoice\local\models\quiz
     */
    protected $quiz;

    /**
     * Contruct
     *
     * @param mod_automultiplechoice/local/models/quiz $quiz A quiz
     */
    public function __construct($quiz) {
        $this->quiz = $quiz;
    }
    
    /**
     * Prepare data for use in a template
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
       

        $content = [
            'quiz' => $this->quiz
        ];

        return $content;
    }
}