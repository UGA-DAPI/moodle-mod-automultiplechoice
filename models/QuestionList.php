<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

require_once __DIR__ . '/ScoringSystem.php';

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
     * @var array array of array('questionid' => (integer), 'score' => (float), 'scoring' => "b=1,e=0..."
     */
    public $questions = array();

    /**
     * @var array List of values among: "qnumber", "score", "qscore".
     */
    public $errors = array();

    /**
     * Get the DB records with added score/scoring fields.
     *
     * @global \moodle_database $DB
     * @param integer $scoringSetId (opt) If given, question will have a 'scoring' field.
     * @return array of "question+question_multichoice" records (objects from the DB) with an additional "score", "scoring" fields.
     */
    public function getRecords($scoringSetId=null) {
        global $DB;
        if (!$this->questions) {
            return array();
        }
        $ids = $this->getIds();
        list ($cond, $params) = $DB->get_in_or_equal($ids);
        $records = $DB->get_records_sql(
                'SELECT q.*, qc.single '
                . 'FROM {question} q INNER JOIN {question_multichoice} qc ON q.id = qc.question '
                . 'WHERE q.id ' . $cond,
                $params
        );
        if (isset($scoringSetId)) {
            $scoringSet = ScoringSystem::read()->getScoringSet($scoringSetId);
        } else {
            $scoringSet = null;
        }
        $callback = function ($q) use ($records, $scoringSet) {
            $r = $records[$q['questionid']];
            $r->score = (double) $q['score'];
            if ($scoringSet) {
                $rule = $scoringSet->findMatchingRule($r);
                if ($rule) {
                    $r->scoring = $rule->getExpression($r);
                } else {
                    $r->scoring = ''; // default AMC scoring (incomplete set of rules)
                }
            }
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
            $this->errors['score'] = 'validateql_wrong_sum';
        }
        if (in_array(0, $scores)) {
            $this->errors['qscore'] = 'validateql_wrong_score';
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
                    'score' => (double) $q[1],
                    'scoring' => (isset($q[2]) ? $q[2] : ''),
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
                'score' => (double) $_POST[$fieldname]['score'][$i],
                'scoring' => isset($_POST[$fieldname]['scoring']) ? $_POST[$fieldname]['scoring'][$i] : '',
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
