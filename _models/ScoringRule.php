<?php

/* 
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

namespace mod\automultiplechoice;

/**
 * Scoring rule
 *
 * @author François Gannaz <francois.gannaz@silecs.info>
 */
class ScoringRule
{
    /** @var boolean */
    public $multiple = false;

    /**
     * @var float If empty, the score will be given by the question when the rule is applied.
     */
    public $score = 0;

    /**
     * @var string For AMC, e.g. "e=-1,v=0,m=-1,b=SCORE".
     */
    private $expression = '';

    /**
     * @var array
     */
    public $errors = array();

    /**
     * Creates a new instance of ScoringRule from the parameters of the rule.
     *
     * @param string $text Like "S|M ; score ; expression"
     * @return \mod\automultiplechoice\ScoringRule
     */
    public static function buildFromConfig($text) {
        $new = new self;
        $a = explode(';', $text);
        if (count($a) !== 3) {
            $new->errors[] = "The line for each rule should have exactly 3 columns.";
            return $new;
        }
        switch (strtoupper(trim($a[0]))) {
            case 'S':
                $new->multiple = false;
                break;
            case 'M':
                $new->multiple = true;
                break;
            default:
                $new->errors[] = "Invalid first column: 'S' or 'M' expected.";
                return $new;
        }
        $new->score = (double) $a[1]; // 0.0 if not numeric
        $new->setExpression($a[2]);
        return $new;
    }

    /**
     * Setter.
     *
     * @param string $txt
     */
    public function setExpression($txt) {
        $this->expression = trim($txt);
    }

    /**
     * Gets the scoring expression to include in AMC for a given question.
     *
     * @throws \Exception
     * @param array|object $question
     * @return string
     */
    public function getExpression($question) {
        if (is_array($question)) {
            $question = (object) $question;
        }
        if (!$this->match($question)) {
            throw new \Exception(join("\n", $this->errors));
        }
        if (strpos($this->expression, 'SCORE') !== false) {
            if ($this->score) {
                $expression = str_replace('SCORE', $this->score, $this->expression);
            } else {
                $expression = str_replace('SCORE', $question->score, $this->expression);
            }
        } else {
            $expression = $this->expression;
        }
        return $expression;
    }

    /**
     * Returns true if the scoring rule can be applied to this question.
     *
     * @param Question $question
     * @return boolean
     */
    public function match(Question $question) {
        $this->errors = array();
        if (!isset($question->single)) {
            $this->errors[] = "Cannot apply a rule to a question that has no type single/multiple.";
        }
        if (empty($question->score)) {
            $this->errors[] = "Cannot apply a rule to a question that has no score.";
        }
        if ($question->single == $this->multiple) {
            $this->errors[] = "The scoring rule type and the question type do not match.";
        }
        if ($this->score && $question->score != $this->score) {
            $this->errors[] = "The scoring rule score and the question score do not match.";
        }
        return empty($this->errors);
    }
}
