<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2014 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class mod_automultiplechoice_renderer extends plugin_renderer_base
{
    /**
     * @var mod\automultiplechoice\Quizz $quizz an automultiplechoice object.
     */
    public $quizz;

    /**
     * @var stdClass A record of the module..
     */
    public $cm;

    /**
     * @var string Name of the current tab.
     */
    public $currenttab = '';

    /**
      * Returns the header for the automultiplechoice module
      *
      * @return string
      */
    public function header() {
        global $CFG;

        if (empty($this->quizz)) {
            throw new Exception("Coding error: no quizz set in renderer.");
        }

        $activityname = format_string($this->quizz->name, true, $this->quizz->course);
        $title = $this->page->course->shortname . " — " . $activityname;

        if (!$this->cm) {
            $this->cm = get_coursemodule_from_instance('automultiplechoice', $this->quizz->id, $this->quizz->course, false, MUST_EXIST);;
        }
        $context = context_module::instance($this->cm->id);

        $this->page->set_title($title);
        $this->page->set_heading($this->page->course->fullname);
        $this->page->set_context($context);

        $output = $this->output->header();

        $output .= $this->output->heading($activityname);
        if (has_capability('mod/automultiplechoice:view', $context)) {
            if (!empty($this->currenttab)) {
                $quizz = $this->quizz;
                $cm = $this->cm;
                $currenttab = $this->currenttab;
                ob_start();
                include($CFG->dirroot . '/mod/automultiplechoice/renderer_tabs.php');
                $output .= ob_get_contents();
                ob_end_clean();
                unset($quizz);
                unset($cm);
                unset($currenttab);
            }
        }

        /*
         foreach ($quizz->messages as $message) {
             $output .= $this->output->notification($message[0], $message[1], $message[2]);
         }
         */
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

    /**
      * Returns the footer
      * @return string
      */
    public function footer() {
        return $this->output->footer();
    }
}
