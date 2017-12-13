<?php

namespace mod_automultiplechoice\local\models;

global $DB;
/* @var $DB \moodle_database */
class quiz
{
    /** @var integer */
    public $id;
    /** @var string */
    public $name = '';
    /** @var integer */
    public $course = '';
    /** @var string */
    public $description = '';
    /** @var int */
    public $descriptionformat = 2; // FORMAT_PLAIN
    /** @var string */
    public $comment = '';
    /** @var integer */
    public $qnumber;
    /** @var integer */
    public $score;
    /** @var mod_automultiplechoice\local\amc\params */
    public $amcparams;
    /** @var \mod_automultiplechoice\local\models\question_list */
    public $questions;
    /** @var integer */
    public $author;
    /** @var boolean */
    public $studentaccess = false;
    /** @var boolean */
    public $corrigeaccess = false;
    /** @var integer */
    public $timecreated;
    /** @var integer */
    public $timemodified;

    /**
     * Use a latex file to describe the questionnaire
     * @var boolean
     */
    public $uselatexfile;

    /**
     * Name of the latex file to use
     * @var string
     */
    public $latexfile;

    /**
     * @var array Keys are field names in the form.
     */
    public $errors = array();
    const TABLENAME = 'automultiplechoice';
    /**
     * Name of the dir where files will be put.
     *
     * @param bool $absolute absolute (default) or relative
     * @return string
     */
    public function getDirName($absolute=true) {
        global $CFG;
        $dir = sprintf('automultiplechoice_%05d', $this->id);
        if ($absolute) {
            return $CFG->dataroot . '/local/automultiplechoice/' . $dir;
        } else {
            return 'local/automultiplechoice/' . $dir;
        }
    }
    /**
     * List the uploaded scans.
     *
     * @return array
     */
    public function findScannedFiles() {
        return glob($this->getDirName() . "/scans/*.{png,jpg,pbm,ppm,tif,tiff}", GLOB_BRACE);
    }
    /**
     * Return true if some scans were uploaded.
     *
     * @return bool
     */
    public function hasScans() {
        return count($this->findScannedFiles()) > 0;
    }
    /**
     * Return true if PDF documents were prepared.
     *
     * @return bool
     */
    public function hasDocuments() {
        return (bool) glob($this->getDirName() . '/sujet*.pdf');
    }
    /**
     * Returns true if the Quizz is locked.
     *
     * @todo Add conditions for this to happen (file existence, log existence, explicit locking in DB, etc).
     *
     * @return boolean
     */
    public function isLocked() {
        return $this->amcparams->locked;
    }
    /**
     * Concat the 3 instructions fields.
     *
     * @param boolean $filter Apply the moodle filters.
     * @return string
     */
    public function getInstructions($filter = true) {
        $scoringset = \mod_automultiplechoice\local\models\scoring_system::read()->getScoringSet($this->amcparams->scoringset);
        if (($this->amcparams->score)&&($scoringset)) {
            $suffix = "\n\n" . $scoringset->description;
        } else {
            $suffix = '';
        }
        return format_text($this->amcparams->instructionsprefix, $this->amcparams->instructionsprefixformat, ['filter' => $filter])
            . "<div>" . format_text($this->description, $this->descriptionformat, ['filter' => $filter]) . "\n" . $suffix . "</div>";
    }
    /**
     * Saves the instance into the DB.
     *
     * @global \moodle_database $DB
     * @return boolean Success?
     */
    public function save() {
        global $DB;
        $record = $this->convertToDbRecord();

        if ($record->id) {
            $DB->update_record(self::TABLENAME, $record);
        } else {
            $this->id = $DB->insert_record(self::TABLENAME, $record);
        }

        $amclog = \mod_automultiplechoice\local\helpers\log::build($this->id);
        $amclog->write('saving');
        /** @TODO
         * vérifier si la modification impacte le barème (scoringsystem)
         */
        return (boolean) $this->id;
    }
    /**
     * Validate the instance and update $this->errors.
     *
     * @return boolean
     */
    public function validate() {

        $this->errors = array();
        if (empty($this->uselatexfile) || (!empty($this->uselatexfile) && !$this->uselatexfile)) {
            if ($this->qnumber <= 0) {
                $this->errors['qnumber'] = 'validate_positive_int';
            }
            if ($this->score <= 0) {
                $this->errors['score'] = 'validate_positive_int';
            }
            if (isset($this->amcparams) && $this->amcparams instanceof \mod_automultiplechoice\local\amc\params) {
                if (!$this->amcparams->validate($this->score)) {
                    $this->errors = array_merge($this->errors, $this->amcparams->errors);
                }
            }
            if (isset($this->questions) && $this->questions instanceof \mod_automultiplechoice\local\models\question_list) {
                if (!$this->questions->validate($this)) {
                    $this->errors = array_merge($this->errors, $this->questions->errors);
                }
            }
        }

        return empty($this->errors);
    }
    /**
     * Return a new instance based on form data
     *
     * @param array $input
     * @return \mod_automultiplechoice\local\models\quiz
     */
    public static function fromForm($input) {
        $new = new self;
        return $new->readFromForm($input);
    }
    /**
     * Update using the form data..
     *
     * @param StdClass $input data from form
     * @return \mod_automultiplechoice\local\models\quiz
     */
    public function readFromForm($input) {

        if (empty($input)) {
            return $this;
        }
        $this->readFromRecord($input);
        if (empty($this->amcparams)) {
            $this->amcparams = new \mod_automultiplechoice\local\amc\params();
        }
        $this->amcparams->readFromForm($input->amc);
        return $this;
    }
    /**
     * Returns a DB object.
     *
     * @global \moodle_database $DB
     * @param integer $id
     * @return stdClass
     */
    public static function findById($id) {
        global $DB;
        if ($id <= 0) {
            return null;
        }
        $record = $DB->get_record(self::TABLENAME, array('id' => (int) $id));
        return $record;
    }
    /**
     * Returns an instance built from a record object.
     *
     * @param object $record
     * @return \mod_automultiplechoice\local\models\quiz
     */
    public static function buildFromRecord(\stdClass $record) {
        $quiz = new self();

        return $quiz->readFromRecord($record);
    }
    /**
     * Get a quiz object from a record stdClass object (object from moddle DB).
     *
     * @param object $record
     * @return \mod_automultiplechoice\local\models\quiz
     */
    public function readFromRecord(\stdClass $record) {

        $simplevalues = [
          'id',
          'course',
          'descriptionformat',
          'qnumber',
          'score',
          'author',
          'timecreated',
          'timemodified'
        ];

        foreach ($simplevalues as $key) {
            if (isset($record->$key)) {
                $this->$key = (int) $record->$key;
            }
        }

        if (isset($record->uselatexfile)) {
            $this->uselatexfile = (boolean) $record->uselatexfile;
        } else {
            $this->uselatexfile = false;
        }

        if (isset($record->latexfile)) {
            $this->latexfile =  $this->uselatexfile ? $record->latexfile : null;
        }

        if (isset($record->studentaccess)) {
            $this->studentaccess = (boolean) $record->studentaccess;
        }
        if (isset($record->corrigeaccess)) {
            $this->corrigeaccess = (boolean) $record->corrigeaccess;
        }
        foreach (array('name', 'comment', 'description') as $key) {
            if (isset($record->$key) && is_string($record->$key)) {
                $this->$key = $record->$key;
            }
        }
        if (isset($record->description) && is_array($record->description)) {
            $this->description = (string) $record->description['text'];
            $this->descriptionformat = (int) $record->description['format'];
        }

        if (!$this->uselatexfile) {
            if (isset($record->amcparams) && is_string($record->amcparams)) {
                $this->amcparams = \mod_automultiplechoice\local\amc\params::fromJson($record->amcparams);
                if ($this->amcparams->grademax == 0) {
                    $this->amcparams->grademax = $this->score;
                }
            }
            if (isset($record->questions) && is_string($record->questions)) {
                $this->questions = \mod_automultiplechoice\local\models\question_list::fromJson($record->questions);
                $this->questions->updateList($this->amcparams->scoringset);
            }
        }

        return $this;
    }
    /**
     * Convert an instance to a stdClass suitable for the DB table.
     *
     * @return stdClass
     */
    protected function convertToDbRecord() {
        $record = (object) array(
            'id' => empty($this->id) ? null : $this->id,
            'timecreated' => $_SERVER['REQUEST_TIME'],
            'timemodified' => $_SERVER['REQUEST_TIME'],
        );
        foreach (array('course', 'qnumber', 'score', 'author', 'studentaccess', 'corrigeaccess', 'timecreated', 'uselatexfile') as $key) {
            if (isset($this->$key) && !empty($this->$key)) {
                $record->$key = (int) $this->$key;
            }
        }
        foreach (array('name', 'description', 'comment') as $key) {
            if (isset($this->$key)) {
                $record->$key = $this->$key;
            }
        }

        if (isset($this->latexfile) && !empty($this->latexfile)) {
            $record->latexfile = $this->latexfile;
        } else {
            $record->latexfile = null;
        }

        if (isset($this->amcparams) && $this->amcparams instanceof \mod_automultiplechoice\local\amc\params) {
            $record->amcparams = $this->amcparams->toJson();
        } else {
            $record->amcparams = "";
        }
        if (isset($this->questions) && $this->questions instanceof  \mod_automultiplechoice\local\models\question_list) {
            $record->questions = $this->questions->toJson();
        } else {
            $record->questions = "";
        }
        return $record;
    }

    /**
     * Build an instance based on form values.
     * Should also upload file if needed.
     *
     * @param  stdClass                        $formdata [description]
     * @param  mod_automultiplechoice_mod_form $mform    [description]
     * @return \mod_automultiplechoice\local\models\quiz updated instance
     */
    public function build_from_form(\stdClass $formdata, $mform) {

        $intvalues = [
          'course',
          'descriptionformat',
          'qnumber',
          'score',
          'author',
          'timecreated',
          'timemodified'
        ];

        foreach ($intvalues as $key) {
            if (isset($formdata->$key)) {
                $this->$key = (int) $formdata->$key;
            }
        }

        if (isset($formdata->instance) && !empty($formdata->instance)) {
            $this->id = intval($formdata->instance);
        }

        if (isset($formdata->uselatexfile)) {
            $this->uselatexfile = (boolean) $formdata->uselatexfile;
        } else {
            $this->uselatexfile = false;
            $this->latexfile = null;
        }

        if (isset($formdata->latexfile) && !empty($formdata->latexfile)) {
            $filename = $mform->get_new_filename('latexfile');
            $uploadsuccess = $mform->save_file(
                'latexfile',
                $this->getDirName(true).'/'.$mform->get_new_filename('latexfile'),
                true
            );
            $this->latexfile = $filename;
        } else {
            $this->latexfile = null;
        }

        if (isset($formdata->studentaccess)) {
            $this->studentaccess = (boolean) $formdata->studentaccess;
        }

        if (isset($formdata->corrigeaccess)) {
            $this->corrigeaccess = (boolean) $formdata->corrigeaccess;
        }

        foreach (array('name', 'comment', 'description') as $key) {
            if (isset($formdata->$key) && is_string($formdata->$key)) {
                $this->$key = $formdata->$key;
            }
        }

        if (isset($formdata->description) && is_array($formdata->description)) {
            $this->description = (string) $formdata->description['text'];
            $this->descriptionformat = (int) $formdata->description['format'];
        }

        return $this;
    }

    /**
     * [build_amc_params description]
     * @param  array $formdata amc parameters data from form
     */
    public function build_amc_params(array $formdata)
    {
        $params = new \mod_automultiplechoice\local\amc\params();
        $this->amcparams = $params->readFromForm($formdata);
    }
}
