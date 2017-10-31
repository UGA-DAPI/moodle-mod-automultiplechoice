<?php
namespace mod_automultiplechoice\local\controllers;
defined('MOODLE_INTERNAL') || die();
class view_controller
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
    public function __construct() {
        $this->parseRequest();
        \require_login($this->course, true, $this->cm);
    }
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
            $this->quiz = \mod_automultiplechoice\local\models\quiz::findById($this->cm->instance);
        } else if ($a) {
            $this->quiz = \mod_automultiplechoice\local\models\quiz::findById($a);
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
     * @param string $currenttab
     * @return \mod_automultiplechoice_renderer
     */
    public function getRenderer($currenttab = '') {
        global $PAGE;
        $output = $PAGE->get_renderer('mod_automultiplechoice');
        $output->quiz = $this->quiz;
        $output->cm = $this->cm;
        $output->currenttab = $currenttab;
        return $output;
    }
}
