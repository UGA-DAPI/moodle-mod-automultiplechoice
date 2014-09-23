<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

require_once __DIR__ . '/QuestionListItem.php';

/**
 * @property integer $id
 * @property string $name
 * @property string $questiontext
 * @property integer $questiontextformat
 * @property float $defaultmark
 * @property float $penalty
 * @property boolean $hidden
 * @property integer $timecreated
 * @property integer $timemodified
 * @property integer $single
 */
class Question extends QuestionListItem
{
    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var float
     */
    public $score;

    /**
     * @var string
     */
    public $scoring;

    /**
     * Build an instance from a serialized array.
     *
     * @param array $array
     */
    static public function fromArray($array) {
        if (!isset($array['questionid']) && !isset($array['id'])) {
            throw new \Exception("Data error: no id/questionid");
        }
        $new = new self;
        foreach ($array as $k => $v) {
            if ($k === 'questionid') {
                $new->id = (int) $v;
            } else if ($k !== 'model') {
                $new->$k = $v;
            }
        }
        if ($new->id > 0) {
            return $new;
        } else {
            return null;
        }
    }

    /**
     * Update the instance from a DB record (table "question") and an optional scoringset.
     *
     * @param \StdClass $record
     * @param ScoringSet $scoringSet (opt)
     */
    public function updateFromRecord($record, $scoringSet = null) {
        foreach ($record as $k => $v) {
            if ($k === 'score') {
                $this->score = (double) $record->score;
            } else {
                $this->$k = $v;
            }
        }
        if ($scoringSet) {
            $rule = $scoringSet->findMatchingRule($this);
            if ($rule) {
                $record->scoring = $rule->getExpression($this);
            } else {
                $record->scoring = ''; // default AMC scoring (incomplete set of rules)
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getType() {
        return 'question';
    }

    /**
     * @param boolean $model (opt, False) If true, add a ("model" => "Question") key.
     * @return array Assoc array
     */
    public function toArray($model = false) {
        $a = array(
            'id' => (int) $this->id,
            'score' => $this->score,
            'scoring' => $this->scoring,
        );
        if ($model) {
            $a["model"] = "Question";
        }
        return $a;
    }

    /**
     * serialize in JSON.
     *
     * @return string
     */
    public function jsonSerialize() {
        if (!$this->id) {
            return null;
        }
        return $this->toArray(true);
    }

    private function getScoreDisplayed() {
        if ($this->score > 0) {
            return $this->score;
        } else {
            return (empty($this->defaultmark) ? '' : sprintf('%.2f', $this->defaultmark));
        }
    }

    /**
     * @param boolean $hideScore
     * @return string
     */
    public function toHtml($hideScore = true) {
        if ($this->id) {
                return '
        <li class="ui-state-default" id="qsel-' . $this->id . '">
            <button type="button" title="' . format_string(get_string('remove')) .'">&#x2A2F;</button>
            <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
            ' . ($hideScore ? $this->htmlScoreField($hideScore) : '
            <label class="qscore">
                ' . get_string('qscore', 'automultiplechoice') . ' : ' . $this->htmlScoreField($hideScore) . '
            </label>' ) . '
            <label>' . format_string($this->name) . '</label>' . $this->htmlHiddenFields() . '
        </li>
';
        } else {
            return '
        <li style="display: none;" class="ui-state-default" id="template-question">
            <button type="button" title="Enlever cette question">&#x2A2F;</button>
            <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
            ' . ($hideScore ? $this->htmlScoreField($hideScore) : '
            <label class="qscore">
                ' . get_string('qscore', 'automultiplechoice') . ' : ' . $this->htmlScoreField($hideScore) . '
            </label>' ) . '
            <label></label>' . $this->htmlHiddenFields() . '
        </li>
';
        }
    }

    public function htmlHiddenFields() {
        $suffix = ($this->id ? '' : ' disabled="disabled"');
        return '
        <input name="question[type][]" value="question" type="hidden"' . $suffix . ' />
        <input name="question[id][]" value="' . ($this->id > 0 ? $this->id : '') . '" type="hidden" class="qid"' . $suffix . ' />
        <input name="question[description][]" type="hidden"' . $suffix . ' />';

    }

    public function htmlScoreField($hidden = true) {
        $suffix = ($this->id ? '' : ' disabled="disabled"');
        $type = ($hidden ? '" type="hidden"' : '" type="text"');
        return '<input name="question[score][]" value="' . $this->getScoreDisplayed() . $type . $suffix . ' />';

    }
}
