<?php

/*
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

namespace mod_automultiplechoice\local\models;


/**
 * Group of scoring rules
 *
 * @author François Gannaz <francois.gannaz@silecs.info>
 */
class scoring_set
{
    /** @var string */
    public $name = '';

    /** @var string */
    public $description = '';

    /**
     * @var array of ScoringRule
     */
    public $rules = array();

    /**
     * Parses a block of the config into a new ScoringSet instance.
     *
     * @param string $block
     * @return \mod_automultiplechoice\local\models\scoring_set
     */
    public static function buildFromConfig($block) {
        $new = new self;
        $lines = array_filter(explode("\n", $block));
        $new->name = array_shift($lines);
        while ($lines && !preg_match('/^\s*[SM]\s*;/i', $lines[0])) {
            $new->description .= array_shift($lines) . "\n";
        }
        foreach ($lines as $line) {
            $new->rules[] = \mod_automultiplechoice\local\models\scoring_rule::buildFromConfig($line);
        }
        return $new;
    }

    /**
     * Finds a matching rule for a question.
     *
     * @param \mod_automultiplechoice\local\models\ $question
     * @return \mod_automultiplechoice\local\models\scoring_rule
     */
    public function findMatchingRule(\mod_automultiplechoice\local\models\question $question) {
        if ($question->score == 0) {
            return '';
        }
        foreach ($this->rules as $rule) {
            if ($rule->match($question)) {
                return $rule;
            }
        }
        throw new \Exception("No rule matches this question. Incomplete rules set.");
    }
}
