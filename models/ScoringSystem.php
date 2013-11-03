<?php

/**
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

namespace mod\automultiplechoice;

require_once __DIR__ . '/ScoringGroup.php';

/**
 * Scoring system
 *
 * @author François Gannaz <francois.gannaz@silecs.info>
 */
class ScoringSystem
{
    /**
     * @var array of ScoringGroup.
     */
    protected static $groups = array();

    /**
     * @var boolean
     */
    protected static $parsedConfig = false;

    /**
     * Creates a new Scoring System from the module configuration in the DB.
     *
     * @return \mod\automultiplechoice\ScoringSystem
     */
    public static function read($forceRead=false) {
        $system = new self;
        if (!self::$parsedConfig || $forceRead) {
            $text = get_config('mod_automultiplechoice', 'scorings');
            self::$groups = $system->parseConfig($text);
        }
        return $system;
    }

    /**
     * Return the HTML for a select element.
     *
     * @param string $name HTML name.
     * @param string $value Current value (selected).
     * @return string HTML
     */
    public function toHtmlSelect($name, $value) {
        $html = '<select name="' . $name . '">'
                . '<option value=""></option>';
        foreach($this->groups as $rank => $scoringGroup) {
            /* @var $scoringGroup ScoringGroup */
            $html .= '<option value="' . $rank . '">'
                . htmlspecialchars($scoringGroup->name)
                . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Parses the config text.
     *
     * @return array of ScoringGroup instances.
     */
    protected function parseConfig($rawText) {
        $blocks = preg_split('/\n-{3,}\s*\n/', $rawText);
        $scoringGroups = array();
        foreach ($blocks as $block) {
            $scoringGroups[] = ScoringGroup::buildFromConfig($block);
        }
        return $scoringGroups;
    }
}
