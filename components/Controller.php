<?php
/**
 * @package    mod_automultiplechoice
 * @copyright  2014 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

class Controller
{
    /**
     * @var Quizz
     */
    private $quizz;

    /**
     * @var StdClass
     */
    private $cm;

    /**
     * @var StdClass
     */
    private $course;

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
            $this->quizz = Quizz::findById($this->cm->instance);
        } elseif ($a) {
            $this->quizz = Quizz::findById($a);
            $this->course = $DB->get_record('course', array('id' => $this->quizz->course), '*', MUST_EXIST);
            $this->cm = \get_coursemodule_from_instance('automultiplechoice', $this->quizz->id, $this->course->id, false, MUST_EXIST);
        } else {
            error('You must specify a course_module ID or an instance ID');
        }
    }

    /**
     * @return Quizz
     */
    function getQuizz() {
        return $this->quizz;
    }

    /**
     * @return StdClass
     */
    function getCm() {
        return $this->cm;
    }

    /**
     * @return StdClass
     */
    function getCourse() {
        return $this->course;
    }

    /**
     * @return \context_module
     */
    public function getContext() {
        return \context_module::instance($this->cm->id);
    }
}