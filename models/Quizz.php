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
     * @return string
     */
    public function getInstructions() {
        $scoringset = ScoringSystem::read()->getScoringSet($this->amcparams->scoringset);
        if ($scoringset) {
            $suffix =  "\n\n" . $scoringset->description;
        } else {
            $suffix = '';
        }
        return rtrim($this->amcparams->instructionsprefix, "\n") . "\n\n"
                . trim($this->description, "\n")
                . "\n\n" . ltrim($suffix, "\n");
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
     * @return Quizz
     */
    public static function fromForm($input) {
        if (empty($input)) {
            return null;
        }
        $new = self::buildFromRecord((object) $input);
        $new->amcparams = AmcParams::fromForm($input["amc"]);
        return $new;
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
        foreach (array('id', 'course', 'qnumber', 'score', 'author', 'timecreated', 'timemodified') as $key) {
            if (isset($record->$key)) {
                $quizz->$key = (int) $record->$key;
            }
        }
        foreach (array('name', 'description', 'comment') as $key) {
            if (isset($record->$key)) {
                $quizz->$key = $record->$key;
            }
        }
        if (isset($record->amcparams) && is_string($record->amcparams)) {
            $quizz->amcparams = AmcParams::fromJson($record->amcparams);
        }
        if (isset($record->questions) && is_string($record->questions)) {
            $quizz->questions = QuestionList::fromJson($record->questions);
        }
        return $quizz;
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
        foreach (array('course', 'qnumber', 'score', 'author', 'timecreated') as $key) {
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
