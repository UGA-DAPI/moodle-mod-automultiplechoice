<?php

/**
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

namespace mod\automultiplechoice;

require_once __DIR__ . '/ScoringSet.php';

/**
 * Scoring system
 *
 * @author François Gannaz <francois.gannaz@silecs.info>
 */
class ScoringSystem
{
    /**
     * @var array of ScoringSet.
     */
    protected static $sets = array();

    /**
     * @var boolean
     */
    protected static $parsedConfig = false;

    /**
     * Constructor. Reads Moodle config if necessary.
     */
    public function __construct() {
        if (!self::$parsedConfig) {
            $text = get_config('mod_automultiplechoice', 'scoringrules');
            self::$sets = $this->parseConfig($text);
        }
    }

    /**
     * Creates a new Scoring System from the module configuration in the DB.
     *
     * @return \mod\automultiplechoice\ScoringSystem
     */
    public static function read($forceRead=false) {
        $system = new self;
        if (!self::$parsedConfig || $forceRead) {
            $text = get_config('mod_automultiplechoice', 'scoringrules');
            self::$sets = $system->parseConfig($text);
        }
        return $system;
    }

    /**
     * Returns the list of defined rule sets.
     *
     * @return array
     */
    public function getSetsNames() {
        $select = array();
        foreach (self::$sets as $s) {
            $select[] = $s->name;
        }
        return $select;
    }

    /**
     * Return the HTML for a select element.
     *
     * @param string $name HTML name.
     * @param string $value Current value (rank selected).
     * @return string HTML
     */
    public function toHtmlSelect($name, $value) {
        $html = '<select name="' . $name . '">'
                . '<option value=""></option>';
        foreach(self::$sets as $rank => $scoringSet) {
            /* @var $scoringSet ScoringSet */
            $html .= '<option value="' . $rank . '"'
                . ($value !== '' && $value == $rank ? ' selected="selected">' : '>')
                . htmlspecialchars($scoringSet->name)
                . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Gets a ScoringSet by its rank in the config.
     *
     * @param integer $rank
     * @return \mod\automultiplechoice\ScoringSet
     * @throws Exception
     */
    public function getScoringSet($rank) {
        $rank = (int) $rank;
        if (!isset(self::$sets[$rank])) {
            throw new \Exception("This scoring group $rank does not exist.");
        }
        return self::$sets[$rank];
    }

    /**
     * Parses the config text.
     *
     * @return array of ScoringSet instances.
     */
    protected function parseConfig($rawText) {
        $blocks = preg_split('/\n-{3,}\s*\n/', $rawText);
        $scoringSets = array();
        foreach ($blocks as $block) {
            $scoringSets[] = ScoringSet::buildFromConfig($block);
        }
        return $scoringSets;
    }
}
