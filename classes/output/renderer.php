<?php

namespace mod_automultiplechoice\output;

defined('MOODLE_INTERNAL') || die();

class renderer extends \plugin_renderer_base
{

    //private $currenttab = 'dashboard';

    /*public function render_indexpage(\templatable $indexpage, $quiz) {
        $data = $indexpage->export_for_template($this);
        $data['tabs'] = $this->build_tabs($quiz);
        return $this->render_from_template('mod_automultiplechoice/indexpage', $data);
    }*/

    /*public function render_tabs(\templatable $page, $tabs) {
        $data = $page->export_for_template($this);
        //$data['tabs'] = $this->build_tabs($quiz);
        return $this->render_from_template('mod_automultiplechoice/tabs', $data);
    }*/

    public function render_dashboard(\templatable $page, $tabs) {
        $data = $page->export_for_template($this);
        $data['tabs'] = $tabs;
        return $this->render_from_template('mod_automultiplechoice/dashboard', $data);
    }

    public function render_questions(\templatable $page, $tabs) {
        $data = $page->export_for_template($this);
        $data['tabs'] = $tabs;
        return $this->render_from_template('mod_automultiplechoice/questions', $data);
    }

    public function render_student_view(\templatable $page) {
        $data = $page->export_for_template($this);
        return $this->render_from_template('mod_automultiplechoice/student_view', $data);
    }
/*
    public function build_tabs($quiz) {
        $tabsdata = $this->get_tabs_data($quiz);
        $tabs = [];
        $dashboard = [
            'active' => $tabsdata['currenttab'] === 'dashboard',
            'inactive' => in_array('dashboard', $tabsdata['disabled']),
            'link' => [
                'link' => new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/view.php?a={$quiz->id}"),
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
                'link' => new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/questions.php?a={$quiz->id}"),
            ],
            'title' => get_string('questions', 'question'),
            'text' => '2. ' . get_string('questions', 'question'),
        ];

        $scoringsystem = [
            'active' => $tabsdata['currenttab'] === 'scoringsystem',
            'inactive' => in_array('scoringsystem', $tabsdata['disabled']),
            'link' => [
                'link' => new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/scoringsystem.php?a={$quiz->id}"),
            ],
            'title' => get_string('scoringsystem', 'automultiplechoice'),
            'text' => '3. ' . get_string('scoringsystem', 'automultiplechoice'),
        ];

        $documents = [
            'active' => $tabsdata['currenttab'] === 'documents',
            'inactive' => in_array('documents', $tabsdata['disabled']),
            'link' => [
                'link' => new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/documents.php?a={$quiz->id}"),
            ],
            'title' => get_string('documents', 'automultiplechoice'),
            'text' => '4. ' . get_string('documents', 'automultiplechoice'),
        ];

        $uploadscans = [
            'active' => $tabsdata['currenttab'] === 'uploadscans',
            'inactive' => in_array('uploadscans', $tabsdata['disabled']),
            'link' => [
                'link' => new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/uploadscans.php?a={$quiz->id}"),
            ],
            'title' => get_string('uploadscans', 'automultiplechoice'),
            'text' => '5. ' . get_string('uploadscans', 'automultiplechoice'),
        ];

        $associating = [
            'active' => $tabsdata['currenttab'] === 'associating',
            'inactive' => in_array('associating', $tabsdata['disabled']),
            'link' => [
                'link' => new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/associating.php?a={$quiz->id}"),
            ],
            'title' => get_string('associating', 'automultiplechoice'),
            'text' => '6. ' . get_string('associating', 'automultiplechoice'),
        ];

        $grading = [
            'active' => $tabsdata['currenttab'] === 'grading',
            'inactive' => in_array('grading', $tabsdata['disabled']),
            'link' => [
                'link' => new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/grading.php?a={$quiz->id}"),
            ],
            'title' => get_string('grading', 'automultiplechoice'),
            'text' => '7. ' . get_string('grading', 'automultiplechoice'),
        ];

        $annotating = [
            'active' => $tabsdata['currenttab'] === 'annotating',
            'inactive' => in_array('annotating', $tabsdata['disabled']),
            'link' => [
                'link' => new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/annotating.php?a={$quiz->id}"),
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
    }*/
/*
    public function get_tabs($quiz) {
        $tabs = array(
            new \tabobject(
                'dashboard',
                new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/view.php?a={$quiz->id}"),
                get_string('dashboard', 'automultiplechoice')
            ),
            new \tabobject(
                'settings',
                new \moodle_url("{$CFG->wwwroot}/course/modedit.php?update={$cm->id}"),
                "1. " . get_string('settings')
            ),
            new \tabobject(
                'questions',
                new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/questions.php?a={$quiz->id}"),
                "2. " . get_string('questions', 'question')
            ),
            new \tabobject(
                'scoringsystem',
                new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/scoringsystem.php?a={$quiz->id}"),
                "3. " . get_string('scoringsystem', 'automultiplechoice')
            ),
            new \tabobject(
                'documents',
                new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/documents.php?a={$quiz->id}"),
                "4. " . get_string('documents', 'automultiplechoice')
            ),
            new \tabobject(
                'uploadscans',
                new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/uploadscans.php?a={$quiz->id}"),
                "5. " . get_string('uploadscans', 'automultiplechoice')
            ),
            new \tabobject(
                'associating',
                new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/associating.php?a={$quiz->id}"),
                "6. " . get_string('associating', 'automultiplechoice')
            ),
            new \tabobject(
                'grading',
                new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/grading.php?a={$quiz->id}"),
                "7. " . get_string('grading', 'automultiplechoice')
            ),
            new \tabobject(
                'annotating',
                new \moodle_url("{$CFG->wwwroot}/mod/automultiplechoice/annotating.php?a={$quiz->id}"),
                "8. " . get_string('annotating', 'automultiplechoice')
            ),
        );

        return $tabs;
    }*/

    /**
     *
     */
  /*  public function get_tabs_data($quiz) {
        $disabled = array();
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
        if (has_students($this->context) == 0) {
            $inactive = array('associating');
        }
        if (!isset($currenttab)) {
            $currenttab = 'dashboard';
        }

        return [
          'currenttab' => $currenttab,
          'disabled' => $disabled,
        ];
    }*/

}
