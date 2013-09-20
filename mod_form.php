<?php
/**
 * The main automultiplechoice configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_automultiplechoice
 * @copyright  2013 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once __DIR__ . '/models/Quizz.php';

/**
 * Module instance settings form
 */
class mod_automultiplechoice_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {

        $mform = $this->_form;

        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('automultiplechoicename', 'automultiplechoice'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'automultiplechoicename', 'automultiplechoice');

        $mform->addElement('textarea', 'description', get_string('description', 'automultiplechoice'), array('rows'=>'15', 'cols'=>'64'));
        $mform->setType('description', PARAM_TEXT);
        $mform->addHelpButton('description', 'description', 'automultiplechoice');

        $mform->addElement('textarea', 'comment', get_string('comment', 'automultiplechoice'), array('rows'=>'10', 'cols'=>'64'));
        $mform->setType('comment', PARAM_TEXT);
        $mform->addHelpButton('comment', 'comment', 'automultiplechoice');

        $mform->addElement('text', 'qnumber', get_string('qnumber', 'automultiplechoice'));
        $mform->setType('qnumber', PARAM_INTEGER);
        $mform->addHelpButton('qnumber', 'qnumber', 'automultiplechoice');

        $mform->addElement('text', 'score', get_string('score', 'automultiplechoice'));
        $mform->setType('score', PARAM_INTEGER);
        $mform->addHelpButton('score', 'score', 'automultiplechoice');

        // AMC settings
        $mform->addElement('text', 'amc[copies]', get_string('amc_copies', 'automultiplechoice'));
        $mform->setType('amc[copies]', PARAM_INTEGER);

        //-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons(true, null, false);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $quizz = \mod\automultiplechoice\Quizz::fromForm($data);
        if (!$quizz->validate()) {
            $errors = array_merge($errors, $quizz->errors);
        }
        return $errors;
    }
}
