<?php
namespace mod_automultiplechoice\output;


defined('MOODLE_INTERNAL') || die();

class view_dashboard implements \renderable, \templatable {
    /**
     * The auto multiple choice quiz.
     *
     * @var \mod_automultiplechoice\local\models\quiz
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
     * @param mod_automultiplechoice/local/models/quiz $quiz A quiz
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
        $questionscount = $this->quiz->questions ? $this->quiz->questions->count() : 0;
        $scoringset = \mod_automultiplechoice\local\models\scoring_system::read()->get_sets()[$this->quiz->amcparams->scoringset];
        $associate_process = new \mod_automultiplechoice\local\amc\associate($this->quiz);
        $associate_process->get_association();

        $helloajaxdata = [
          'id' => $this->quiz->id,
          'firstname' => 'Donald',
          'lastname' => 'Duck'
        ];

        $content = [
          'quiz' => $this->quiz,
          'hellodata' => json_encode($helloajaxdata),
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
                'data' => $this->quiz->amcparams->grademax
              ],
              2 => [
                'label' => get_string('scoringset', 'automultiplechoice'),
                'data' =>  $scoringset->name
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
            'nbcopyauto' => count($associate_process->copyauto),
            'nbcopymanual' => count($associate_process->copymanual),
            'nbcopyunknown' => count($associate_process->copyunknown),
          ],
          'grading' => [
            'title' => get_string('grading', 'automultiplechoice'),
            'data' => $this->process->statScans() && $this->process->isGraded() ? $this->process->getStats2() : []
          ]
        ];
        return $content;
    }
}
