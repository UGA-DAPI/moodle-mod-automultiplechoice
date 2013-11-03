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
     * @return ScoringRule
     */
    public static function fillFromArray($text) {
        $new = new self;
        $a = explode(';', $text);
        if (count($a) !== 3) {
            $new->errors[] = "The line for each rule should have exactly 3 columns.";
            return $new;
        }
        switch (strtoupper(trim($a[0]))) {
            case 'S':
                $this->multiple = false;
                break;
            case 'M':
                $this->multiple = true;
                break;
            default:
                $this->errors[] = "Invalid first column: 'S' or 'M' expected.";
                return $new;
        }
        $this->score = (double) $a[1]; // 0.0 if not numeric
        $this->setExpression($a[2]);
        return $new;
    }

    /**
     * Setter.
     *
     * @param string $txt
     */
    public function setExpression($txt) {
        $this->expression = $txt;
    }

    /**
     * Gets the scoring expression to include in AMC for a given question.
     *
     * @throws \Exception
     * @param type $question
     * @return string
     */
    public function getExpression($question) {
        if (!$this->match($question)) {
            throw new \Exception(join("\n", $this->errors));
        }
        if (strpos($this->expression, 'SCORE') !== false) {
            if ($this->score) {
                $expression = str_replace('SCORE', $question->score, $this->expression);
            } else {
                $expression = str_replace('SCORE', $this->score, $this->expression);
            }
        } else {
            $expression = $this->expression;
        }
        return $expression;
    }
}
