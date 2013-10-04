<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

global $DB;
/* @var $DB \moodle_database */

/**
 * QuestionList behaves as an array.
 *
 * <code>
 * $ql = \mod\automultiplechoice\QuestionList::fromJson($json);
 * if ($ql) {
 *     echo $ql[0]['score'];
 * }
 * </code>
 */
class QuestionList implements \Countable, \ArrayAccess
{
    /**
     * @var array array of array('questionid' => (integer), 'score' => (integer)
     */
    public $questions = array();

    /**
     * @var array List of values among: "qnumber", "score", "sum".
     */
    public $errors = array();

    /**
     *
     * @global \moodle_database $DB
     * @return array of "question" records (objects from the DB) with an additional "score" field
     */
    public function getRecords() {
        global $DB;
        if (!$this->questions) {
            return array();
        }
        $ids = $this->getIds();
        $records = $DB->get_records_list('question', 'id', $ids);
        $callback = function ($q) use ($records) {
            $r = $records[$q['questionid']];
            $r->score = $q['score'];
            return $r;
        };
        return array_map($callback, $this->questions);
    }

    /**
     * Validate the question against the quizz parameters.
     *
     * @param \mod\automultiplechoice\Quizz $quizz
     * @return boolean
     */
    public function validate(Quizz $quizz) {
        $this->errors = array();
        if (count($this->questions) != $quizz->qnumber) {
            $this->errors['qnumber'] = 'validateql_wrong_number';
        }
        $scores = $this->getScores();
        if (array_sum($scores) != $quizz->score) {
            $this->errors['sum'] = 'validateql_wrong_sum';
        }
        if (in_array(0, $scores)) {
            $this->errors['score'] = 'validateql_wrong_score';
        }
        if ($this->errors) {
            return false;
        }
        return true;
    }

    /**
     * Return the JSON serialization of this instance.
     *
     * @return string
     */
    public function toJson()
    {
        if (empty($this->questions)) {
            return '';
        }
        return json_encode(array_map('array_values', $this->questions));
    }

    /**
     * Return a new instance from a serialized JSON instance.
     *
     * @param string $json
     * @return QuestionList
     */
    public static function fromJson($json)
    {
        $new = new self();
        $decoded = json_decode($json);
        if (!empty($decoded) && is_array($decoded)) {
            foreach ($decoded as $q) {
                $new->questions[] = array(
                    'questionid' => (int) $q[0],
                    'score' => (int) $q[1],
                );
            }
        }
        return $new;
    }

    /**
     * Read $_POST[$fieldname] and return a new instance.
     *
     * @return QuestionList
     */
    public static function fromForm($fieldname) {
        if (!isset($_POST[$fieldname]) || empty($_POST[$fieldname]['id'])) {
            return null;
        }
        $new = new self();
        for ($i = 0; $i < count($_POST[$fieldname]['id']); $i++) {
            $new->questions[] = array(
                'questionid' => (int) $_POST[$fieldname]['id'][$i],
                'score' => (int) $_POST[$fieldname]['score'][$i],
            );
        }
        return $new;
    }

    /**
     * Return the list of question.id
     *
     * @return array of integers
     */
    private function getIds() {
        $ids = array();
        foreach ($this->questions as $q) {
            $ids[] = $q['questionid'];
        }
        return $ids;
    }

    /**
     * Return the list of scores
     *
     * @return array of integers
     */
    private function getScores() {
        $scores = array();
        foreach ($this->questions as $q) {
            $scores[] = $q['score'];
        }
        return $scores;
    }

    // Implement Countable
    /**
     * Number of questions.
     *
     * @return int Count
     */
    public function count() {
        return count($this->questions);
    }

    // Implement ArrayAccess
    public function offsetSet($offset, $value) {
    }
    public function offsetUnset($offset) {
    }
    public function offsetExists($offset) {
        return isset($this->questions[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->questions[$offset]) ? $this->questions[$offset] : null;
    }
}
