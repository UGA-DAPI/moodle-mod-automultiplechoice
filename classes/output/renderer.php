<?php

class mod_automultiplechoice_renderer extends \plugin_renderer_base {
    /**
     * @var mod_automultiplechoice\local\models\quiz $quiz an automultiplechoice object.
     */
    public $quiz;

    /**
     * @var stdClass A record of the module..
     */
    public $cm;

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        $page->requires->jquery();
        $page->requires->jquery_plugin('ui-css');
        $page->requires->jquery_plugin('bootstrap');
        $page->requires->jquery_plugin('bootstrap-css');
        $page->requires->css(
            new moodle_url('/mod/automultiplechoice/style/jquery.dataTables.css')
        );
        $page->requires->css(
            new moodle_url('/mod/automultiplechoice/style/styles.css')
        );
        $page->requires->js_call_amd('mod_automultiplechoice/async', 'init');
        $page->requires->js_call_amd('mod_automultiplechoice/common', 'init');

        parent::__construct($page, $target);
    }

    /**
     * Returns the header for all contents of the automultiplechoice module
     *
     * Default tab is set to settings since mod_form.php is using a hack to display tabs that does not allow to set the selected tab...
     *
     * @param string $currenttab the tab to set as selected
     * @return string
     */
    public function header($currenttab = 'settings') {
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
            $tabs = new \mod_automultiplechoice\output\tabs($this->quiz, $context, $this->cm, $currenttab);
            $data['tabs'] = $tabs->export_for_template($this);
            $output .= $this->render_from_template('mod_automultiplechoice/tabs', $data);
        }

        $output .= $this->render_from_template('mod_automultiplechoice/noscript', []);
        return $output;
    }

    // used in annotating and was used in associating
    public function students_selector($url, $cm, $idnumber, $groupid, $exclude = null) {

        $select = amc_get_students_select($url, $cm, $idnumber, $groupid, $exclude);
        $output = html_writer::div( $this->output->render($select), 'amc_students_selector');
        $output .= html_writer::tag('p', '', array('style' => 'page-break-after: always;'));

        return $output;
    }

    /**
     * Display quiz errors only (special format ?)
     */
    public function display_errors($errors) {
        echo $this->box_start('errorbox row');
        echo '<div class="col-md-12">';
        echo '<h6><em>' . get_string('someerrorswerefound') . '</em></h6>';
        echo '<div class="alert alert-danger">';
        echo '<dl>';
        foreach ($errors as $field => $error) {
            $field = preg_replace('/^(.+)\[(.+)\]$/', '${1}_${2}', $field);
            echo "<dt>" . get_string($field, 'automultiplechoice') . "</dt>";
            echo "<dd>" . get_string($error, 'automultiplechoice') . "</dd>";
        }
        echo '</dl>';
        echo '</div>';
        echo '</div>';
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

    public function render_grading_view(\templatable $page) {
        $data = $page->export_for_template($this);
        return $this->render_from_template('mod_automultiplechoice/grading', $data);
    }

    public function render_questions_view(\templatable $page) {
        $data = $page->export_for_template($this);
        return $this->render_from_template('mod_automultiplechoice/questions', $data);
    }

    public function render_question_chooser(renderable $chooser) {
        return $this->render_from_template('mod_quiz/question_chooser', $chooser->export_for_template($this));
    }

    public function render_qbank_chooser(renderable $qbankchooser) {
        return $this->render_from_template('core_question/qbank_chooser', $qbankchooser->export_for_template($this));
    }

    /**
     * Return the contents of the question bank, to be displayed in the question-bank pop-up.
     *
     * @param \mod_automultiplechoice\question\bank\custom_view $questionbank the question bank view object.
     * @param array $pagevars the variables from {@link \question_edit_setup()}.
     * @return string HTML to output / send back in response to an AJAX request.
     */
    public function question_bank_contents(\mod_automultiplechoice\question\bank\custom_view $questionbank, array $pagevars) {

        $qbank = $questionbank->render('editq', $pagevars['qpage'], $pagevars['qperpage'],
                $pagevars['cat'], $pagevars['recurse'], $pagevars['showhidden'], $pagevars['qbshowtext']);
        return html_writer::div(html_writer::div($qbank, 'bd'), 'questionbankformforpopup');
    }
}
