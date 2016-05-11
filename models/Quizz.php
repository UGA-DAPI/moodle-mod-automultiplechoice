<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

require __DIR__ . '/AmcParams.php';
require __DIR__ . '/QuestionList.php';
require_once __DIR__ . '/Log.php';

global $DB;
/* @var $DB \moodle_database */

class Quizz
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

    /** @var AmcParams */
    public $amcparams;

    /** @var QuestionList */
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
            return '/local/automultiplechoice/' . $dir;
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
        $scoringset = ScoringSystem::read()->getScoringSet($this->amcparams->scoringset);
        if (($this->amcparams->score)&&($scoringset)) {
            $suffix =  "\n\n" . $scoringset->description;
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
        $amclog = Log::build($this->id);
        $amclog->write('scoringsystem');

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
        if ($this->qnumber <= 0) {
            $this->errors['qnumber'] = 'validate_positive_int';
        }
        if ($this->score <= 0) {
            $this->errors['score'] = 'validate_positive_int';
        }
        if (isset($this->amcparams) && $this->amcparams instanceof AmcParams) {
            if (!$this->amcparams->validate($this->score)) {
                $this->errors = array_merge($this->errors, $this->amcparams->errors);
            }
        }
        if (isset($this->questions) && $this->questions instanceof QuestionList) {
            if (!$this->questions->validate($this)) {
                $this->errors = array_merge($this->errors, $this->questions->errors);
            }
        }
        return empty($this->errors);
    }

    /**
     * Return a new instance.
     *
     * @param array $input
     * @return Quizz
     */
    public static function fromForm($input)
    {
        $new = new self;
        return $new->readFromForm($input);
    }

    /**
     * Update using the form data..
     *
     * @param StdClass $input
     * @return Quizz
     */
    public function readFromForm($input) {
        if (empty($input)) {
            return $this;
        }
        $this->readFromRecord($input);
        if (empty($this->amcparams)) {
            $this->amcparams = new AmcParams();
        }
        $this->amcparams->readFromForm($input->amc);
        return $this;
    }

    /**
     * Returns a new instance if the ID was found in the DB.
     *
     * @global \moodle_database $DB
     * @param integer $id
     * @return Quizz
     */
    public static function findById($id) {
        global $DB;
        if ($id <= 0) {
            return null;
        }
        $record = $DB->get_record(self::TABLENAME, array('id' => (int) $id));
        return self::buildFromRecord($record);
    }

    /**
     * Returns an instance built from a record object.
     *
     * @param object $record
     * @return Quizz
     */
    public static function buildFromRecord(\stdClass $record)
    {
        $quizz = new self();
        return $quizz->readFromRecord($record);
    }

    /**
     * Update from a record object.
     *
     * @param object $record
     * @return Quizz
     */
    public function readFromRecord(\stdClass $record)
    {
        foreach (array('id', 'course', 'descriptionformat', 'qnumber', 'score', 'author', 'timecreated', 'timemodified') as $key) {
            if (isset($record->$key)) {
                $this->$key = (int) $record->$key;
            }
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
        if (isset($record->amcparams) && is_string($record->amcparams)) {
            $this->amcparams = AmcParams::fromJson($record->amcparams);
            if ($this->amcparams->grademax == 0) {
                $this->amcparams->grademax = $this->score;
            }
        }
        if (isset($record->questions) && is_string($record->questions)) {
            $this->questions = QuestionList::fromJson($record->questions);
            $this->questions->updateList($this->amcparams->scoringset);
        }
        return $this;
    }

    /**
     * Convert an instance to a stdClass suitable for the DB table.
     *
     * @return stdClass
     */
    protected function convertToDbRecord()
    {
        $record = (object) array(
            'id' => empty($this->id) ? null : $this->id,
            'timecreated' => $_SERVER['REQUEST_TIME'],
            'timemodified' => $_SERVER['REQUEST_TIME'],
        );
        foreach (array('course', 'qnumber', 'score', 'author', 'studentaccess', 'corrigeaccess', 'timecreated') as $key) {
            if (isset($this->$key)) {
                $record->$key = (int) $this->$key;
            }
        }
        foreach (array('name', 'description', 'comment') as $key) {
            if (isset($this->$key)) {
                $record->$key = $this->$key;
            }
        }
        if (isset($this->amcparams) && $this->amcparams instanceof AmcParams) {
            $record->amcparams = $this->amcparams->toJson();
        } else {
            $record->amcparams = "";
        }
        if (isset($this->questions) && $this->questions instanceof QuestionList) {
            $record->questions = $this->questions->toJson();
        } else {
            $record->questions = "";
        }
        return $record;
    }
}
