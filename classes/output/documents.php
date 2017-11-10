<?php

namespace mod_automultiplechoice\output;

defined('MOODLE_INTERNAL') || die();

class documents implements \renderable, \templatable {
    /**
     * An array containing page data
     *
     * @var array
     */
    protected $quiz;

    /**
     * Contruct
     *
     * @param array $content An array of renderable headings
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
        $controller = new \mod_automultiplechoice\local\controllers\view_controller();
        $canrestore = has_capability('mod/automultiplechoice:restoreoriginalfile', $controller->getContext());
        $process = new \mod_automultiplechoice\local\amc\process($this->quiz);

        $content = [
            'quiz' => $this->quiz,
            'ziplink' => $process->getZipLink(),
            'pdflinks' => $process->getPdfLinks(),
            'preparetime' => $process->lastlog('prepare:pdf'),
            'canrestore' => $canrestore
        ];

        return $content;
    }
}