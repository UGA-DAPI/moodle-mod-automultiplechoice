<?php

namespace mod_automultiplechoice\output;

defined('MOODLE_INTERNAL') || die();

class view_questions implements \renderable, \templatable {

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
        $questions = [];
        $questionindex = 1;
        foreach ($this->quiz->questions as $questionitem) {
            $item = new \stdClass();
            $item->id = $questionitem->id;
            $item->name = $questionitem->name;
            $item->section = $questionitem->getType() === 'section';
            $questionscore = $questionitem->score;
            if ($questionscore <= 0) {
                $questionscore = empty($questionitem->defaultmark) ? '' : sprintf('%.2f', $questionitem->defaultmark);
            }
            $item->score = $questionscore;
            if ($questionitem->getType() === 'section') {
                $item->index = -1;
            } else {
                $item->index = $questionindex;
                $questionindex++;
            }
            $questions[] = $item;
        }

        $courseid = $this->data['courseid'];
        $content = [
          'quiz' => $this->quiz,
          'questions' => $questions,
          'importfilequestionsnurl' => new \moodle_url('/question/import.php', array('courseid' => $courseid)),
          'importquestionsurl' => new \moodle_url('/local/questionssimplified/edit_wysiwyg.php', array('courseid' => $courseid)),
          'createquestionsurl' =>  new \moodle_url('/local/questionssimplified/edit_standard.php', array('courseid' => $courseid)),
          'questionbankurl' => new \moodle_url('/question/edit.php', array('courseid' => $courseid))
        ];
        return $content;
    }
}
