<?php

namespace mod_automultiplechoice\output;

defined('MOODLE_INTERNAL') || die();

class view_student implements \renderable, \templatable {
    /**
     * The auto multiple choice quiz.
     *
     * @var \mod_automultiplechoice\local\models\quiz
     */
    protected $quiz;

    /**
     * Different process form AMC lib.
     *
     * @var mod_automultiplechoice/local/amc/process
     */
    protected $process;
    /**
     * Moodle User.
     *
     * @var moodle_user
     */
    protected $user;

    /**
     * Contruct
     *
     * @param mod_automultiplechoice/local/models/quiz $quiz A quiz
     * @param mod_automultiplechoice/local/amc/process $process amc process
     * @param moodle_user $quiz A quiz
     */
    public function __construct($quiz, $process, $user) {
        $this->process = $process;
        $this->user = $user;
        $this->quiz = $quiz;
    }
    /**
     * Prepare data for use in a template
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        $anotatedfile = $this->process->getUserAnotatedSheet($this->user->idnumber);
        $content = ['title' => $this->quiz->name];
        if ($this->quiz->studentaccess && $anotatedfile) {
            $content['corrected'] = $this->process->getFileActionUrl($anotatedfile);
            if ($this->quiz->corrigeaccess) {
                $corrige = $this->process->normalizeFilename('corrige');
                $content['correction'] = $this->process->getFileActionUrl($corrige);
            }
        }
        return $content;
    }
}
