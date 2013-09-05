<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require __DIR__ . '/AmcParams.php';
require __DIR__ . '/QuestionList.php';

namespace \mod\automultiplechoice;

global $DB;
/* @var $DB \moodle_database */

class Quizz
{
    /** @var integer */
    public $id;

    /** @var string */
    public $name = '';

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

    const TABLENAME = 'automultiplechoice';

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
        foreach (array('id', 'qnumber', 'score', 'author', 'timecreated', 'timemodified') as $key) {
            if (isset($record->$key)) {
                $quizz->$key = (int) $record->$key;
            }
        }
        foreach (array('name', 'description', 'comment') as $key) {
            if (isset($record->$key)) {
                $quizz->$key = $record->$key;
            }
        }
        if (isset($record->amcparams)) {
            $quizz->amcparams = AmcParams::fromJson($record->amcparams);
        }
        if (isset($record->questions)) {
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
        foreach (array('qnumber', 'score', 'author', 'timecreated') as $key) {
            if (isset($this->$key)) {
                $record->$key = (int) $this->$key;
            }
        }
        foreach (array('name', 'description', 'comment') as $key) {
            if (isset($this->$key)) {
                $record->$key = $this->$key;
            }
        }
        if (isset($this->amcparams)) {
            $record->amcparams = AmcParams::toJson($this->amcparams);
        }
        if (isset($record->questions)) {
            $record->questions = QuestionList::fromJson($this->questions);
        }
        return $record;
    }
}
