<?php

class mod_automultiplechoice_renderer extends plugin_renderer_base {
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

        //$page->requires->jquery();
        $page->requires->css(
            new moodle_url('/mod/automultiplechoice/assets/amc.css')
        );
        $page->requires->js_call_amd('mod_automultiplechoice/async', 'init');
        parent::__construct($page, $target);
    }

    /**
     * Returns the header for the automultiplechoice module
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
            $this->cm = get_coursemodule_from_instance('automultiplechoice', $this->quiz->id, $this->quiz->course, false, MUST_EXIST);;
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
                include($CFG->dirroot . '/mod/automultiplechoice/renderer_tabs.php');
                \mod_automultiplechoice\local\helpers\flash_message_manager::displayMessages();
                $output .= ob_get_contents();
                ob_end_clean();
                unset($quiz);
                unset($cm);
                unset($currenttab);
            }
        }
        $output .= <<<EOL
        <noscript>
            <div class="box errorbox">
            <h2>Erreur : Javascript n'est pas activé</h2>
            Javascript n'est pas activé dans votre navigateur.
            Cette activité ne pourra pas fonctionner correctement sans Javascript.
            </div>
        </noscript>
EOL;
        return $output;
    }


    public function students_selector($url, $cm, $idnumber, $groupid, $exclude = null) {

        $select = amc_get_students_select($url, $cm, $idnumber, $groupid, $exclude);
        $output = html_writer::div( $this->output->render($select), 'amc_students_selector');
        $output .= html_writer::tag('p', '', array('style' => 'page-break-after: always;'));

        return $output;
    }
    /**
     * Returns the footer
     * @return string
     */
    public function footer() {
        return $this->output->footer();
    }

    public function displayErrors($errors) {
        echo $this->box_start('errorbox');
        echo '<p>' . get_string('someerrorswerefound') . '</p>';
        echo '<dl>';
        foreach ($errors as $field => $error) {
            $field = preg_replace('/^(.+)\[(.+)\]$/', '${1}_${2}', $field);
            echo "<dt>" . get_string($field, 'automultiplechoice') . "</dt>\n"
                    . "<dd>" . get_string($error, 'automultiplechoice') . "</dd>\n";
        }
        echo "</dl>\n";
        echo $this->box_end();
    }
}
