<?php

/*
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

namespace mod\automultiplechoice;
require_once __DIR__ . '/ScoringRule.php';

/**
 * Group of scoring rules
 *
 * @author François Gannaz <francois.gannaz@silecs.info>
 */
class ScoringSet
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
     * @return \mod\automultiplechoice\ScoringSet
     */
    public static function buildFromConfig($block) {
        $new = new self;
        $lines = array_filter(explode("\n", $block));
        $new->name = array_shift($lines);
        while ($lines && !preg_match('/^\s*[SM]\s*;/i', $lines[0])) {
            $new->description .= array_shift($lines) . "\n";
        }
        foreach ($lines as $line) {
            $new->rules[] = ScoringRule::buildFromConfig($line);
        }
        return $new;
    }

    /**
     * Finds a matching rule for a question.
     *
     * @param type $question
     * @return \mod\automultiplechoice\ScoringRule
     */
    public function findMatchingRule($question) {
        foreach ($this->rules as $rule) {
            if ($rule->match($question)) {
                return $rule;
            }
        }
        throw new \Exception("No rule matches this question. Incomplete rules set.");
    }
}
