<?php
namespace mod_automultiplechoice\output;
defined('MOODLE_INTERNAL') || die();
class questions implements \renderable, \templatable
{
    /**
     * The auto multiple choice quiz.
     *
     * @var \mod_automultiplechoice\local\models\quiz
     */
    protected $quiz;

    /**
     * An array containing page data
     *
     * @var array
     */
    protected $process;
    protected $questionlist;
    protected $allquestions;
    /**
     * Contruct
     *
     * @param array $content An array of renderable headings
     */
    public function __construct($quiz, $process, $questionlist, $allquestions) {
        $this->quiz = $quiz;
        $this->process = $process;
        $this->questionlist = $questionlist;
        $this->allquestions = $allquestions;
    }
    /**
     * Prepare data for use in a template
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        $questions = [];
        $i = 1;
        foreach ($this->questionlist as $questionitem) {
            $item = new \stdClass();
            $item->id = $questionitem->id;
            $item->name = $questionitem->name;
            $item->section = $questionitem->getType() === 'section';
            $item->index = $i;
            $questions[] = $item;
            $i++;
        }
        $content = [
          'title' => $this->quiz->name,
          'locked' => $this->quiz->isLocked(),
          'errors' => $this->questionlist->errors,
          'questions' => $questions
        ];
        return $content;
    }
}