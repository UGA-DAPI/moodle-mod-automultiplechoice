<?php
namespace mod_automultiplechoice\output;


defined('MOODLE_INTERNAL') || die();

class dashboard implements \renderable, \templatable {
    /**
     * An array containing page data
     *
     * @var array
     */
    protected $quiz;

    /**
     * Amc main process
     *
     * @var \mod_automultiplechoice\local\amc\process
     */
    protected $process;

    /**
     * Contruct
     *
     * @param array $content An array of renderable headings
     */
    public function __construct($quiz) {
        $this->quiz = $quiz;
        $this->process = new \mod_automultiplechoice\local\amc\process($quiz);
    }
    /**
     * Prepare data for use in a template
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        $questionscount = $this->quiz->questions->count();
        $content = [
          'quizId' => $this->quiz->id,
          'title' => $this->quiz->name,
          'locked' => $this->quiz->isLocked(),
          'errors' => $this->quiz->errors,
          'settings' => [
            'title' => get_string('settings'),
            'rows' => [
              0 => [
                'label' => get_string('instructions', 'automultiplechoice'),
                'data' => format_text($this->quiz->amcparams->instructionsprefix, $this->quiz->amcparams->instructionsprefixformat)
              ],
              1 => [
                'label' => get_string('description', 'automultiplechoice'),
                'data' => format_text($this->quiz->description, $this->quiz->descriptionformat)
              ]
            ]
          ],
          'questions' => [
            'title' => get_string('questions', 'question'),
            'rows' => [
              0 => [
                'label' => get_string('qnumber', 'automultiplechoice'),
                'data' => $this->quiz->qnumber === $questionscount ? $this->quiz->qnumber : '<span class="score-mismatch">'.$questionscount.' / ' .$this->quiz->qnumber .'</span>'
              ]
            ]
          ],
          'scoringsystem' => [
            'title' => get_string('scoringsystem', 'automultiplechoice'),
            'rows' => [
              0 => [
                'label' => get_string('score', 'automultiplechoice'),
                'data' => $this->quiz->score
              ],
              1 => [
                'label' => get_string('amc_grademax', 'automultiplechoice'),
                'data' => $this->quiz->grademax
              ],
              2 => [
                'label' => get_string('scoringset', 'automultiplechoice'),
                'data' => $this->quiz->scoringset
              ]
            ]
          ],
          'documents' => [
            'title' => get_string('documents', 'automultiplechoice'),
            'data' => [
              'hasDocuments' => $this->quiz->hasDocuments(),
              'ziplink' => $this->process->getZipLink(),
              'pdflinks' => $this->process->getPdfLinks(),
              'preparetime' => $this->process->lastlog('prepare:pdf'),
              'isoPrepareTime' => $this->process->isoDate($preparetime)
            ]
          ],
          'uploadscans' => [
            'title' => get_string('uploadscans', 'automultiplechoice'),
            'data' => $this->process->statScans()
          ],
          'associating' => [
            'title' => get_string('associating', 'automultiplechoice'),
            'data' => []
          ],
          'grading' => [
            'title' => get_string('grading', 'automultiplechoice'),
            'data' => $this->process->statScans() && $this->process->isGraded() ? $this->process->getStats2() : []
          ]
        ];
        return $content;
    }
}