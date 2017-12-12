<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot.'/course/moodleform_mod.php';

require_once __DIR__ . '/locallib.php';

/* @var $PAGE moodle_page */

/**
 * Module instance settings form
 */
class mod_automultiplechoice_mod_form extends moodleform_mod {
    /**
     * @var mod_automultiplechoice\local\models\quiz
     */
    protected $current;

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $PAGE;
        $PAGE->requires->js_call_amd('mod_automultiplechoice/mod_form', 'init');
        $mform = $this->_form;
        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('automultiplechoicename', 'automultiplechoice'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'automultiplechoicename', 'automultiplechoice');


        // Use a latex file to define the MCQ
        $mform->addElement(
            'advcheckbox',
            'uselatexfile',
            get_string('modform_uselatexfile', 'mod_automultiplechoice'),
            get_string('modform_uselatexfilelabel', 'mod_automultiplechoice'),
            null,
            [0,1]
        );
        $mform->setType('uselatexfile', PARAM_BOOL);
        // latex file upload
        $mform->addElement('filepicker', 'latexfile', get_string('modform_latexfile', 'mod_automultiplechoice'), null, ['accepted_types' => 'tex']);
        $mform->disabledIf('latexfile', 'uselatexfile', 'eq', 0);


        $mform->addElement('text', 'qnumber', get_string('qnumber', 'automultiplechoice'));
        $mform->setType('qnumber', PARAM_INTEGER);
        $mform->addHelpButton('qnumber', 'qnumber', 'automultiplechoice');
        $mform->disabledIf('qnumber', 'uselatexfile', 'eq', 1);

        $mform->addElement('textarea', 'comment', get_string('comment', 'automultiplechoice'), array('rows'=>'3', 'cols'=>'64'));
        $mform->setType('comment', PARAM_TEXT);
        $mform->addHelpButton('comment', 'comment', 'automultiplechoice');

        if (empty($this->current->id)) { // only when creating an instance
            // hack because Moodle gets the priorities wrong with data_preprocessing()
            $mform->setDefault('amc[lstudent]', get_config('mod_automultiplechoice', 'instructionslstudent'));
            $mform->setDefault('amc[lname]', get_config('mod_automultiplechoice', 'instructionslnamestd'));
        }

        // Instructions
        $mform->addElement('header', 'general', get_string('instructionsheader', 'automultiplechoice'));
        $mform->addElement('select', 'instructions', get_string('instructions', 'automultiplechoice'), parse_default_instructions());
        $mform->setType('instructions', PARAM_TEXT);
        $mform->addHelpButton('instructions', 'instructions', 'automultiplechoice');
        $mform->disabledIf('instructions', 'uselatexfile', 'eq', 1);

        $mform->addElement(
            'editor',
            'amc[instructionsprefix]',
            get_string('instructions', 'automultiplechoice'),
            array(
                'rows' => '4',
                'cols' => '64'
            )
        );
        $mform->setType('amc[instructionsprefix]', PARAM_RAW);
        //$mform->disabledIf('amc[instructionsprefix]', 'uselatexfile', 'eq', 1);

        $mform->addElement('editor', 'description', get_string('description', 'automultiplechoice'), array('rows'=>'6', 'cols'=>'64'));
        $mform->setType('description', PARAM_RAW);
        $mform->addHelpButton('description', 'description', 'automultiplechoice');
        // $mform->disabledIf('description', 'uselatexfile', 'eq', 1);


        $mform->addElement('advcheckbox', 'anonymous', get_string('anonymous', 'automultiplechoice'));

        $mform->addElement('text', 'amc[lstudent]', get_string('amc_lstudent', 'automultiplechoice'), array('size' => 64));
        $mform->setType('amc[lstudent]', PARAM_TEXT);
        $mform->disabledIf('amc[lstudent]', 'uselatexfile', 'eq', 1);

        $mform->addElement(
            'text',
            'amc[lname]',
            get_string('amc_lname', 'automultiplechoice'),
            array(
                'data-std' => get_config('mod_automultiplechoice', 'instructionslnamestd'),
                'data-anon' => get_config('mod_automultiplechoice', 'instructionslnameanon'),
            )
        );
        $mform->setType('amc[lname]', PARAM_TEXT);
        $mform->disabledIf('amc[lname]', 'uselatexfile', 'eq', 1);

        // AMC settings
        //-------------------------------------------------------------------------------
        // Adding the "amcparams" fieldset, parameters specific to printed output
        $mform->addElement('header', 'amcparameters', get_string('amcparams', 'automultiplechoice'));

        $mform->addElement('text', 'amc[copies]', get_string('amc_copies', 'automultiplechoice'));
        $mform->setType('amc[copies]', PARAM_INTEGER);
        $mform->setDefault('amc[copies]', 1);
        $mform->disabledIf('amc[copies]', 'uselatexfile', 'eq', 1);

        $mform->addElement(
            'select',
            'amc[questionsColumns]',
            get_string('amc_questionsColumns', 'automultiplechoice'),
            array("Auto", 1, 2)
        );
        $mform->addHelpButton('amc[questionsColumns]', 'amc_questionsColumns', 'automultiplechoice');
        $mform->disabledIf('amc[questionsColumns]', 'uselatexfile', 'eq', 1);

        $mform->addElement('advcheckbox', 'amc[shuffleq]', get_string('amc_shuffleq', 'automultiplechoice'));
        $mform->setType('amc[shuffleq]', PARAM_BOOL);
        $mform->disabledIf('amc[shuffleq]', 'uselatexfile', 'eq', 1);

        $mform->addElement('advcheckbox', 'amc[shufflea]', get_string('amc_shufflea', 'automultiplechoice'));
        $mform->setType('amc[shufflea]', PARAM_BOOL);
        $mform->disabledIf('amc[shufflea]', 'uselatexfile', 'eq', 1);

        $mform->addElement('advcheckbox', 'amc[separatesheet]', get_string('amc_separatesheet', 'automultiplechoice'));
        $mform->setType('amc[separatesheet]', PARAM_BOOL);
        $mform->disabledIf('amc[separatesheet]', 'uselatexfile', 'eq', 1);

        $mform->addElement(
            'select',
            'amc[answerSheetColumns]',
            get_string('amc_answerSheetColumns', 'automultiplechoice'),
            array("Auto", 1, 2, 3, 4)
        );
        $mform->disabledIf('amc[answerSheetColumns]', 'amc[separatesheet]', 'eq', 0);
        $mform->disabledIf('amc[answerSheetColumns]', 'uselatexfile', 'eq', 1);

        $mform->addElement(
            'select',
            'amc[displaypoints]',
            get_string('amc_displaypoints', 'automultiplechoice'),
            array("Ne pas afficher", "En début de question", "En fin de question")
        );
        $mform->setType('amc[displaypoints]', PARAM_INTEGER);
        $mform->disabledIf('amc[displaypoints]', 'uselatexfile', 'eq', 1);

        $mform->addElement('advcheckbox', 'amc[markmulti]', get_string('amc_markmulti', 'automultiplechoice'));
        $mform->setType('amc[markmulti]', PARAM_BOOL);
        $mform->disabledIf('amc[markmulti]', 'uselatexfile', 'eq', 1);

        $mform->addElement('advcheckbox', 'amc[score]', get_string('amc_score', 'automultiplechoice'));
        $mform->setType('amc[score]', PARAM_BOOL);
        $mform->disabledIf('amc[score]', 'uselatexfile', 'eq', 1);

        $mform->addElement('textarea', 'amc[customlayout]', get_string('amc_customlayout', 'automultiplechoice'), array('rows'=>'3', 'cols'=>'64'));
        $mform->setType('amc[customlayout]', PARAM_TEXT);
        $mform->addHelpButton('amc[customlayout]', 'amc_customlayout', 'automultiplechoice');
        $mform->disabledIf('amc[customlayout]', 'uselatexfile', 'eq', 1);
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
    function data_preprocessing(&$default_values)
    {

        if (isset($default_values['description'])) {
            $default_values['description'] = array('text' => $default_values['description']);
        }

        // Convert from JSON to array
        if (!empty($default_values['amcparams'])) {

            $params = \mod_automultiplechoice\local\amc\params::fromJson($default_values['amcparams']);
            $default_values['amc'] = (array) $params;
            $default_values['amc']['instructionsprefix'] = array(
                'text' => $params->instructionsprefix,
            );
            $this->_form->setDefault(
                'amc[instructionsprefix]',
                array(
                    'text' => $params->instructionsprefix,
                )
            );

            if (!empty($this->current->id) && !empty($params->locked)) {
                $this->_form->freeze(
                    array(
                        'qnumber',
                        'amc[copies]',
                        'amc[shuffleq]',
                        'amc[shufflea]',
                        'amc[separatesheet]',
                        'amc[displaypoints]',
                        'amc[markmulti]',
                        'amc[customlayout]',
                        'amc[score]',
                    )
                );
            } else if (!empty($this->current->uselatexfile) && !$this->current->uselatexfile) {
                // Only add the required rule if the field is not disabled
                $this->_form->addRule('amc[copies]', null, 'required', null, 'client');
            }




            $this->_form->setDefault('instructions', '');
            foreach (parse_default_instructions() as $v) {
                if ($params->instructionsprefix === $v) {
                    $this->_form->setDefault('instructions', $v);
                }
            }

        }

        // Hideous hack to insert a tab bar at the top of the page
        if (!empty($this->current->id)) {
            global $PAGE, $OUTPUT;
            $output = $PAGE->get_renderer('mod_automultiplechoice');
            $output->quiz =  \mod_automultiplechoice\local\models\quiz::buildFromRecord($this->current);
            $OUTPUT = $output;
        }
    }
}
