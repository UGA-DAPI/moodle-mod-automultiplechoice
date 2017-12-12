<?php

namespace mod_automultiplechoice;

// If set could not be loaded via AJAX call ($.ajax->phpajaxhandler->phpclass->call service)
defined('MOODLE_INTERNAL') || die();

class shared_service
{
    /**
     * @var \mod_automultiplechoice\local\models\quiz
     */
    public $quiz;

    /**
     * @var StdClass
     */
    public $cm;

    /**
     * @var StdClass
     */
    public $course;

    public function __construct() {}


    /**
     * Parse the parameters "a" and "id".
     *
     * @global moodle_database $DB
     */
    public function parseRequest() {
        global $DB;
        $id = \optional_param('id', 0, PARAM_INT); // course_module ID, or
        $a  = \optional_param('a', 0, PARAM_INT);  // automultiplechoice instance ID
        if ($id) {
            $this->cm = \get_coursemodule_from_id('automultiplechoice', $id, 0, false, MUST_EXIST);
            $this->course = $DB->get_record('course', array('id' => $this->cm->course), '*', MUST_EXIST);
            $quizrecord = \mod_automultiplechoice\local\models\quiz::findById($this->cm->instance);
            $this->quiz = \mod_automultiplechoice\local\models\quiz::buildFromRecord($quizrecord);
        } else if ($a) {
            $quizrecord = \mod_automultiplechoice\local\models\quiz::findById($a);
            $this->quiz = \mod_automultiplechoice\local\models\quiz::buildFromRecord($quizrecord);
            $this->course = $DB->get_record('course', array('id' => $this->quiz->course), '*', MUST_EXIST);
            $this->cm = \get_coursemodule_from_instance('automultiplechoice', $this->quiz->id, $this->course->id, false, MUST_EXIST);

        } else {
            print_error('You must specify a course_module ID or an instance ID');
        }
    }

    /**
     * @return \mod_automultiplechoice\local\models\quiz
     */
    public function getQuiz() {
        return $this->quiz;
    }
    /**
     * @return StdClass
     */
    public function getCm() {
        return $this->cm;
    }
    /**
     * @return StdClass
     */
    public function getCourse() {
        return $this->course;
    }
    /**
     * @return \context_module
     */
    public function getContext() {
        return \context_module::instance($this->cm->id);
    }
    /**
     * Get the tabbed renderer that will replace $OUTPUT.
     *
     * @return \mod_automultiplechoice_renderer
     */
    public function getRenderer() {
        global $PAGE;
        $output = $PAGE->get_renderer('mod_automultiplechoice');
        $output->quiz = $this->quiz;
        $output->cm = $this->cm;
        return $output;
    }

    /**
     * [should_redirect_to_questions description]
     * @return boolean true if one should go to question page
     */
    public function should_redirect_to_questions() {
        return !$this->quiz->uselatexfile ? false : count($quiz->questions) > 0;
    }
}
