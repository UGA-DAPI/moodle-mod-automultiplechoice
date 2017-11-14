<?php

class mod_automultiplechoice_renderer extends \plugin_renderer_base {
    /**
     * @var mod_automultiplechoice\local\models\quizz $quiz an automultiplechoice object.
     */
    public $quiz;

    /**
     * @var stdClass A record of the module..
     */
    public $cm;

    /**
     * @var string Name of the current tab.
     */
    public $currenttab = '';

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        $page->requires->jquery_plugin('ui-css');
        $page->requires->css(
            new moodle_url('/mod/automultiplechoice/style/jquery.dataTables.css')
        );
        $page->requires->css(
            new moodle_url('/mod/automultiplechoice/style/styles.css')
        );
        $page->requires->js_call_amd('mod_automultiplechoice/async', 'init');
        parent::__construct($page, $target);
    }

    /**
     * Returns the header for all contents of the automultiplechoice module
     *
     * @return string
     */
    public function header() {
        global $CFG;

        if (empty($this->quiz)) {
            throw new Exception("Coding error: no quiz set in renderer.");
        }

        $activityname = format_string($this->quiz->name, true, $this->quiz->course);
        $title = $this->page->course->shortname . " — " . $activityname;

        if (!$this->cm) {
            $this->cm = get_coursemodule_from_instance('automultiplechoice', $this->quiz->id, $this->quiz->course, false, MUST_EXIST);
        }
        $context = context_module::instance($this->cm->id);

        $this->page->set_title($title);
        $this->page->set_heading($this->page->course->fullname);
        $this->page->set_context($context);

        $output = $this->output->header();

        $output .= $this->output->heading($activityname);
        if (has_capability('mod/automultiplechoice:update', $context)) {
            if (!empty($this->currenttab)) {
                $quiz = $this->quiz;
                $cm = $this->cm;
                $currenttab = $this->currenttab;
                ob_start();
                include($CFG->dirroot . '/mod/automultiplechoice/tabs.php');
                \mod_automultiplechoice\local\helpers\flash_message_manager::displayMessages();
                $output .= ob_get_contents();
                ob_end_clean();
                unset($quiz);
                unset($cm);
                unset($currenttab);
            }
        }

        $noscript = '<noscript>';
        $noscript .= '<div class="box errorbox">';
        $noscript .= '<h2>Erreur : Javascript n\'est pas activé. Cette activité ne pourra pas fonctionner correctement sans Javascript.</h2>';
        $noscript .= '</div>';
        $noscript = '</noscript>';
        $output .= $noscript;
        return $output;
    }


    public function students_selector($url, $cm, $idnumber, $groupid, $exclude = null) {

        $select = amc_get_students_select($url, $cm, $idnumber, $groupid, $exclude);
        $output = html_writer::div( $this->output->render($select), 'amc_students_selector');
        $output .= html_writer::tag('p', '', array('style' => 'page-break-after: always;'));

        return $output;
    }

    public function display_errors($errors) {
        echo $this->box_start('errorbox');
        echo '<p>' . get_string('someerrorswerefound') . '</p>';
        echo '<dl>';
        foreach ($errors as $field => $error) {
            $field = preg_replace('/^(.+)\[(.+)\]$/', '${1}_${2}', $field);
            echo "<dt>" . get_string($field, 'automultiplechoice') . "</dt>";
            echo "<dd>" . get_string($error, 'automultiplechoice') . "</dd>";
        }
        echo "</dl>";
        echo $this->box_end();
    }

    public function render_dashboard(\templatable $page) {
        $data = $page->export_for_template($this);
        return $this->render_from_template('mod_automultiplechoice/dashboard', $data);
    }

    public function render_student_view(\templatable $page) {
        $data = $page->export_for_template($this);
        return $this->render_from_template('mod_automultiplechoice/studentview', $data);
    }

    public function render_scoring_form(\templatable $page) {
        $data = $page->export_for_template($this);
        return $this->render_from_template('mod_automultiplechoice/scoringform', $data);
    }

    public function render_documents_view(\templatable $page) {
        $data = $page->export_for_template($this);
        return $this->render_from_template('mod_automultiplechoice/documents', $data);
    }

    public function render_scansupload_view(\templatable $page) {
        $data = $page->export_for_template($this);
        return $this->render_from_template('mod_automultiplechoice/scansupload', $data);
    }

    public function render_association_view(\templatable $page) {
        $data = $page->export_for_template($this);
        return $this->render_from_template('mod_automultiplechoice/association', $data);
    }

    public function render_annotation_view(\templatable $page) {
        $data = $page->export_for_template($this);
        return $this->render_from_template('mod_automultiplechoice/annotation', $data);
    }
}
