<?php
namespace mod_automultiplechoice\output;

defined('MOODLE_INTERNAL') || die();

class tabs implements \renderable, \templatable
{
    /**
     * The auto multiple choice quiz.
     *
     * @var mod_automultiplechoice/local/models/quiz
     */
    protected $quiz;
    /**
     * Moodle context.
     *
     * @var moodle_context
     */
    protected $context;
    /**
     * Moodle course module.
     *
     * @var moodle_cm
     */
    protected $cm;
   
    /**
     * Construct
     *
     */
    public function __construct($quiz, $context, $cm) {
        $this->quiz = $quiz;
        $this->context = $context;
        $this->cm = $cm;
    }
    /**
     * Prepare data for use in a template
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        
        
       
    }

}