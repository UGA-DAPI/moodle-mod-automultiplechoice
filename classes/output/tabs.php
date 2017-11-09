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
     * Selected tab.
     *
     * @var string
     */
    protected $selected;
    /**
     * Contruct
     *
     */
    public function __construct($quiz, $context, $cm, $selected) {
        $this->quiz = $quiz;
        $this->context = $context;
        $this->cm = $cm;
        $this->selected = $selected;
    }
    /**
     * Prepare data for use in a template
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        $tabsdata = $this->get_tabs_data($this->quiz, $this->context, $this->selected);
        $tabs = [];
        $dashboard = [
            'active' => $tabsdata['currenttab'] === 'dashboard',
            'inactive' => in_array('dashboard', $tabsdata['disabled']),
            'link' => [
                'link' => new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/view.php?a={$this->quiz->id}&page=dashboard"),
            ],
            'title' => get_string('dashboard', 'automultiplechoice'),
            'text' => get_string('dashboard', 'automultiplechoice'),
        ];
        $settings = [
            'active' => $tabsdata['currenttab'] === 'settings',
            'inactive' => in_array('settings', $tabsdata['disabled']),
            'link' => [
                'link' => new \moodle_url("{$CFG->wwwroot}/course/modedit.php?update={$this->cm->id}"),
            ],
            'title' => get_string('settings'),
            'text' => '1. ' . get_string('settings'),
        ];
        $questions = [
            'active' => $tabsdata['currenttab'] === 'questions',
            'inactive' => in_array('questions', $tabsdata['disabled']),
            'link' => [
                'link' => new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/questions.php?a={$this->quiz->id}&page=questions"),
            ],
            'title' => get_string('questions', 'question'),
            'text' => '2. ' . get_string('questions', 'question'),
        ];
        $scoringsystem = [
            'active' => $tabsdata['currenttab'] === 'scoringsystem',
            'inactive' => in_array('scoringsystem', $tabsdata['disabled']),
            'link' => [
                'link' => new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/view.php?a={$this->quiz->id}&page=scoring"),
            ],
            'title' => get_string('scoringsystem', 'automultiplechoice'),
            'text' => '3. ' . get_string('scoringsystem', 'automultiplechoice'),
        ];
        $documents = [
            'active' => $tabsdata['currenttab'] === 'documents',
            'inactive' => in_array('documents', $tabsdata['disabled']),
            'link' => [
                'link' => new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/view.php?a={$this->quiz->id}&page=documents"),
            ],
            'title' => get_string('documents', 'automultiplechoice'),
            'text' => '4. ' . get_string('documents', 'automultiplechoice'),
        ];
        $uploadscans = [
            'active' => $tabsdata['currenttab'] === 'uploadscans',
            'inactive' => in_array('uploadscans', $tabsdata['disabled']),
            'link' => [
                'link' => new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/view.php?a={$this->quiz->id}&page=scans"),
            ],
            'title' => get_string('uploadscans', 'automultiplechoice'),
            'text' => '5. ' . get_string('uploadscans', 'automultiplechoice'),
        ];
        $associating = [
            'active' => $tabsdata['currenttab'] === 'associating',
            'inactive' => in_array('associating', $tabsdata['disabled']),
            'link' => [
                'link' => new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/view.php?a={$this->quiz->id}&page=associate"),
            ],
            'title' => get_string('associating', 'automultiplechoice'),
            'text' => '6. ' . get_string('associating', 'automultiplechoice'),
        ];
        $grading = [
            'active' => $tabsdata['currenttab'] === 'grading',
            'inactive' => in_array('grading', $tabsdata['disabled']),
            'link' => [
                'link' => new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/view.php?a={$this->quiz->id}&page=grade"),
            ],
            'title' => get_string('grading', 'automultiplechoice'),
            'text' => '7. ' . get_string('grading', 'automultiplechoice'),
        ];
        $annotating = [
            'active' => $tabsdata['currenttab'] === 'annotating',
            'inactive' => in_array('annotating', $tabsdata['disabled']),
            'link' => [
                'link' => new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/view.php?a={$this->quiz->id}&page=annotate"),
            ],
            'title' => get_string('annotating', 'automultiplechoice'),
            'text' => '8. ' . get_string('annotating', 'automultiplechoice'),
        ];
        array_push(
          $tabs,
          $dashboard,
          $settings,
          $questions,
          $scoringsystem,
          $documents,
          $uploadscans,
          $associating,
          $grading,
          $annotating
        );
        return $tabs;
    }
    public function get_tabs_data($quiz, $context, $selected) {
        $disabled = array();
        $currenttab = $selected;
        if (empty($quiz->name)) {
            $currenttab = 'dashboard';
            $disabled = array(
              'dashboard',
              'questions',
              'scoringsystem',
              'documents',
              'uploadscans',
              'associating',
              'grading',
              'annotating'
            );
        } else if (empty($quiz->questions)) {
            $currenttab = 'questions';
            $disabled = array('dashboard', 'scoringsystem', 'documents', 'uploadscans', 'associating', 'grading', 'annotating');
        } else if (!$quiz->validate()) {
            $disabled = array('documents', 'uploadscans', 'associating', 'grading', 'annotating');
        } else if (!empty($quiz->errors) || !$quiz->isLocked()) {
            $disabled = array('uploadscans', 'associating', 'grading', 'annotating');
        } else if (!$quiz->hasScans()) {
            $disabled = array('associating', 'grading', 'annotating');
        }
        if ($quiz->isLocked()) {
            $disabled[] = 'questions';
        }
        if (has_students($context) === 0) {
            $inactive = array('associating');
        }
        if (!isset($currenttab)) {
            $currenttab = 'dashboard';
        }
        return [
          'currenttab' => $currenttab,
          'disabled' => $disabled,
        ];
    }
}