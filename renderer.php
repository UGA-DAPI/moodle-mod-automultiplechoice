<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2014 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__ . '/components/FlashMessageManager.php';

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
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        $page->requires->jquery();
        $page->requires->js(new moodle_url('assets/async.js'));
        parent::__construct($page, $target);
    }

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
        if (has_capability('mod/automultiplechoice:update', $context)) {
            if (!empty($this->currenttab)) {
                $quizz = $this->quizz;
                $cm = $this->cm;
                $currenttab = $this->currenttab;
                ob_start();
                include($CFG->dirroot . '/mod/automultiplechoice/renderer_tabs.php');
                mod\automultiplechoice\FlashMessageManager::displayMessages();
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
