<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

require_once __DIR__ . '/ScoringSystem.php';
require_once __DIR__ . '/Question.php';
require_once __DIR__ . '/QuestionSection.php';

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
class QuestionList implements \Countable, \ArrayAccess, \Iterator
{
    /**
     * @var array of QuestionListItem instances.
     */
    public $questions = array();

    /**
     * @var array List of values among: "qnumber", "score", "qscore".
     */
    public $errors = array();

    /**
     * @todo Use this!
     * @var array
     */
    public $warnings = array();

    private $position = 0;

    /**
     * Update the items with the DB records and added score/scoring fields.
     *
     * @global \moodle_database $DB
     * @param integer $scoringSetId (opt) If given, question will have a 'scoring' field.
     * @param boolean $includeSections (opt)
     * @return array of "question+multichoice" records (objects from the DB) with an additional "score", "scoring" fields.
     */
    public function updateList($scoringSetId=null) {
        if (!$this->questions) {
            return array();
        }
        if (isset($scoringSetId)) {
            $scoringSet = ScoringSystem::read()->getScoringSet($scoringSetId);
        } else {
            $scoringSet = null;
        }
        $records = $this->getRawRecords();
        foreach ($this->questions as $k => $q) {
            if ($q->getType() === 'question') {
                if (isset($records[$q->id])) {
                    $q->updateFromRecord($records[$q->id], $scoringSet);
                } else {
                    $this->warnings[] = ("La question ID={$q->id} est introuvable, elle a été retirée.");
                    unset($this->questions[$k]);
                }
            }
        }
        return $this;
    }

    /**
     * Validate the question against the quizz parameters.
     *
     * @param \mod\automultiplechoice\Quizz $quizz
     * @return boolean
     */
    public function validate(Quizz $quizz) {
        $this->errors = array();
        if (count($this->getIds()) != $quizz->qnumber) {
            $this->errors['qnumber'] = 'validateql_wrong_number';
        }
        $scores = $this->getScores();
        if (abs(array_sum($scores) - $quizz->score) > 0.01) {
            $this->errors['score'] = 'validateql_wrong_sum';
        }
        if (in_array(0, $scores)) {
            $this->errors['qscore'] = 'validateql_wrong_score';
        }
        if ($this->errors) {
            return false;
        }

        // deleted questions?
        $validIds = array();
        foreach ($this->getRawRecords() as $r) {
            $validIds[] = $this->getById($r->id);
        }
        if (count($validIds) != count($this->getIds())) {
            $this->questions = $validIds;
            $this->errors['qnumber'] = 'validateql_deletedquestions';
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
        return json_encode($this->questions);
    }

    /**
     * Return a new instance from a serialized JSON instance.
     *
     * @param string $json
     * @return QuestionList
     */
    public static function fromJson($json)
    {
        $qlist = new self();
        $decoded = json_decode($json);
        if (!empty($decoded) && is_array($decoded)) {
            foreach ($decoded as $q) {
                $new = null;
                if (is_string($q)) {
                    $new = new QuestionSection($q);
                } else if (is_array($q)) {
                    if (isset($q[0])) {
                        $new = Question::fromArray(
                            array(
                                'id' => (int) $q[0],
                                'score' => (double) $q[1],
                                'scoring' => (isset($q[2]) ? $q[2] : ''),
                            )
                        );
                    }
                } else if (is_object($q) && isset($q->model)) {
                    if ($q->model === 'QuestionSection') {
                        $new = new QuestionSection($q->name, $q->description);
                    } else if ($q->model === 'Question') {
                        $new = Question::fromArray((array) $q);
                    }
                }
                if ($new) {
                    $qlist->questions[] = $new;
                } else {
                    throw new \Exception("Unknown question format in the DB: " . print_r($q, true));
                }
            }
        }
        return $qlist;
    }

    /**
     * Read $_POST[$fieldname] and return a new instance.
     *
     * @param string $fieldname
     * @return QuestionList
     */
    public static function fromForm($fieldname) {
        if (!isset($_POST[$fieldname]) || empty($_POST[$fieldname]['id'])) {
            return null;
        }
        $post = $_POST[$fieldname];
        $new = new self();
        for ($i = 0; $i < count($_POST[$fieldname]['id']); $i++) {
            if ($post['type'][$i] === 'section') {
                $item = new QuestionSection($post['id'][$i], $post['description'][$i]);
            } else {
                $item = Question::fromArray(
                    array(
                        'id' => (int) $post['id'][$i],
                        'score' => isset($post['score']) ? (double) str_replace(',', '.', $post['score'][$i]) : '',
                        'scoring' => isset($post['scoring']) ? $post['scoring'][$i] : '',
                    )
                );
            }
            if ($item) {
                $new->questions[] = $item;
            }
        }
        return $new;
    }

    /**
     * Checks if the list contains a given question (or one of the given list).
     *
     * @param integer|array $questionids
     * @return boolean
     */
    public function contains($questionids) {
        if (is_array($questionids)) {
            $lookup = array_flip($questionids);
        } else {
            $lookup = array($questionids => true);
        }
        foreach ($this->questions as $q) {
            if (isset($q->id) && isset($lookup[$q->id])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Find a question by its id.
     *
     * @param type $id
     * @return array
     */
    public function getById($id) {
        foreach ($this->questions as $q) {
            if (isset($q->id) && $q->id == $id) {
                return $q;
            }
        }
        return null;
    }

    /**
     * Get the records from the DB, with get_records_sql().
     *
     * @global \moodle_database $DB
     * @return array
     */
    private function getRawRecords() {
        global $DB, $CFG;
        $ids = $this->getIds();
	if (empty($ids)){
		return NULL;
	}
	list ($cond, $params) = $DB->get_in_or_equal($ids);
        if ($CFG->version >= 2013111800) {
            $qtable = 'qtype_multichoice_options';
            $qfield = 'questionid';
        } else {
            $qtable = 'question_multichoice';
            $qfield = 'question';
        }
        return $DB->get_records_sql(
                'SELECT q.*, qc.single '
                . 'FROM {question} q INNER JOIN {' . $qtable . "} qc ON qc.{$qfield}=q.id "
                . 'WHERE q.id ' . $cond,
                $params
	);
	
    }

    /**
     * Return the list of question.id
     *
     * @return array of integers
     */
    private function getIds() {
        $ids = array();
        foreach ($this->questions as $q) {
            if (isset($q->id)) {
                $ids[] = (int) $q->id;
            }
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
            if ($q->getType() === 'question') {
                $scores[] = $q->score;
            }
        }
        return $scores;
    }

    // Implement Countable
    /**
     * Number of questions (not counting the sections).
     *
     * @return int Count
     */
    public function count() {
        return count($this->getIds());
    }

    // Implement Iterator
    public function rewind() {
        $this->position = 0;
    }

    public function current() {
        return $this->questions[$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        $this->position++;
    }

    public function valid() {
        return isset($this->questions[$this->position]);
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
        return $this->offsetExists($offset) ? $this->questions[$offset] : null;
    }
}
