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
require_once __DIR__ . '/locallib.php';
require_once __DIR__ . '/models/ScoringSystem.php';

use \mod\automultiplechoice as amc;

/* @var $PAGE moodle_page */

/**
 * Module instance settings form
 */
class mod_automultiplechoice_mod_form extends moodleform_mod {
    private $ajaxScoringSet = '
(function(){
    function updateDescription() {
        var xhr = new XMLHttpRequest();
        var id = document.getElementById("id_amc_scoringset").value;
        console.log(id);
        xhr.onload = function() {
            console.log(this.responseText);
            document.getElementById("scoringset_desc").innerHTML = this.responseText;
        }
        xhr.open("GET", "../mod/automultiplechoice/ajax/scoring.php?scoringsetid=" + id);
        xhr.responseType = "text";
        xhr.send();
    }
    document.getElementById("id_amc_scoringset").addEventListener("click", updateDescription);
    updateDescription();
})();
';
    /**
     * @var Quizz
     */
    protected $current;

    /**
     * Defines forms elements
     */
    public function definition() {
        global $PAGE;

        $mform = $this->_form;
        $strrequired = get_string('required');

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

        $mform->addElement('text', 'qnumber', get_string('qnumber', 'automultiplechoice'));
        $mform->setType('qnumber', PARAM_INTEGER);
        $mform->addHelpButton('qnumber', 'qnumber', 'automultiplechoice');

        $mform->addElement('text', 'score', get_string('score', 'automultiplechoice'));
        $mform->setType('score', PARAM_INTEGER);
        $mform->setDefault('score', 20);
        $mform->addHelpButton('score', 'score', 'automultiplechoice');

        $mform->addElement('select', 'amc[scoringset]', get_string('scoringset', 'automultiplechoice'), amc\ScoringSystem::read()->getSetsNames());
        $mform->setType('amc[scoringset]', PARAM_INTEGER);
        $mform->addElement('static', 'scoringset_desc', get_string('scoringset', 'automultiplechoice'), '<div id="scoringset_desc"></div>');
        $PAGE->requires->js_init_code($this->ajaxScoringSet, true);

        $mform->addElement('textarea', 'comment', get_string('comment', 'automultiplechoice'), array('rows'=>'10', 'cols'=>'64'));
        $mform->setType('comment', PARAM_TEXT);
        $mform->addHelpButton('comment', 'comment', 'automultiplechoice');

        if (empty($this->current->id)) { // only when creating an instance
            // hack because Moodle gets the priorities wrong with data_preprocessing()
            $mform->setDefault('amc[lstudent]', get_string('amc_lstudent_default', 'automultiplechoice'));
            $mform->setDefault('amc[lname]', get_string('amc_lname_default', 'automultiplechoice'));
        }


        // Instructions
        $mform->addElement('header', 'general', get_string('instructionsheader', 'automultiplechoice'));

        if (empty($this->current->id)) { // only when creating an instance
            $mform->addElement('select', 'instructions', get_string('instructions', 'automultiplechoice'), parse_default_instructions());
            $mform->setType('instructions', PARAM_TEXT);
            $mform->addHelpButton('instructions', 'instructions', 'automultiplechoice');
        }

        $mform->addElement('textarea', 'description', get_string('description', 'automultiplechoice'), array('rows'=>'15', 'cols'=>'64'));
        $mform->setType('description', PARAM_TEXT);
        $mform->addHelpButton('description', 'description', 'automultiplechoice');

        $mform->addElement('text', 'amc[lstudent]', get_string('amc_lstudent', 'automultiplechoice'), array('size' => 64));
        $mform->setType('amc[lstudent]', PARAM_TEXT);

        $mform->addElement('text', 'amc[lname]', get_string('amc_lname', 'automultiplechoice'));
        $mform->setType('amc[lname]', PARAM_TEXT);


        // AMC settings
        //-------------------------------------------------------------------------------
        // Adding the "amcparams" fieldset, parameters specific to printed output
        $mform->addElement('header', 'amcparameters', get_string('amcparams', 'automultiplechoice'));

        $mform->addElement('text', 'amc[copies]', get_string('amc_copies', 'automultiplechoice'));
        $mform->setType('amc[copies]', PARAM_INTEGER);
        $mform->addRule('amc[copies]', null, 'required', null, 'client');

        $mform->addElement('text', 'amc[minscore]', get_string('amc_minscore', 'automultiplechoice'));
        $mform->setType('amc[minscore]', PARAM_INTEGER);
        $mform->addRule('amc[minscore]', null, 'required', null, 'client');

        $mform->addElement('advcheckbox', 'amc[shuffleq]', get_string('amc_shuffleq', 'automultiplechoice'));
        $mform->setType('amc[shuffleq]', PARAM_BOOL);

        $mform->addElement('advcheckbox', 'amc[shufflea]', get_string('amc_shufflea', 'automultiplechoice'));
        $mform->setType('amc[shufflea]', PARAM_BOOL);

        $mform->addElement('advcheckbox', 'amc[separatesheet]', get_string('amc_separatesheet', 'automultiplechoice'));
        $mform->setType('amc[separatesheet]', PARAM_BOOL);

        $mform->addElement('select', 'amc[displaypoints]', get_string('amc_displaypoints', 'automultiplechoice'),
                array("Ne pas afficher", "En début de question", "En fin de question")
        );
        $mform->setType('amc[displaypoints]', PARAM_INTEGER);

        $mform->addElement('advcheckbox', 'amc[markmulti]', get_string('amc_markmulti', 'automultiplechoice'));
        $mform->setType('amc[markmulti]', PARAM_BOOL);

        //-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons(true, null, false);
    }

    /**
     * Only available on moodleform_mod.
     *
     * @param array $default_values passed by reference
     */
    function data_preprocessing(&$default_values){
        // Convert from JSON to array
        if (!empty($default_values['amcparams'])) {
            $params = amc\AmcParams::fromJson($default_values['amcparams']);
            $default_values['amc'] = (array) $params;
            if (!empty($this->current->id) && !empty($params->locked)) {
                $this->_form->freeze(
                        array(
                            'qnumber', 'score', 'amc[scoringset]', 'amc[copies]', 'amc[shuffleq]', 'amc[shufflea]',
                            'amc[separatesheet]', 'amc[displaypoints]', 'amc[markmulti]', 'amc[minscore]',
                        )
                );
            }
        }
    }

    /**
     * Called by Moodle on form reception.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    /*
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $quizz = \mod\automultiplechoice\Quizz::fromForm($data);
        if (!$quizz->validate()) {
            $errors = array_merge($errors, $quizz->errors);
        }
        return $errors;
    }
     */
}
