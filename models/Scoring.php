<?php

/**
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

namespace mod\automultiplechoice;

/**
 * Scoring system
 *
 * @author François Gannaz <francois.gannaz@silecs.info>
 */
class ScoringSystem
{
    public $scorings = array(
        'single' => array(),
        'multiple' => array(),
    );

    /**
     * Creates a new Scoring System from the module configuration in the DB.
     *
     * @return \mod\automultiplechoice\ScoringSystem
     */
    public static function createFromConfig()
    {
        $system = new self;
        $raw = get_config('mod_automultiplechoice', 'scorings');
        $lines = explode("\n", $raw);
        foreach ($lines as $line) {
            $cells = array_map('trim', explode("|", trim($line, " |")));
            if (count($cells) === 4) {
                $new = new Scoring();
                $new->name = $cells[0];
                $new->score = (double) $cells[2];
                $new->multiple = (strcasecmp($cells[1], "multiple") === 0);
                $new->formula = $cells[3];
                $system->scorings[$new->multiple ? 'multiple' : 'single'][] = $new;
            }
        }
        return $system;
    }

    /**
     * Return the HTML for a select element.
     *
     * @param string $name HTML name.
     * @param boolean $multiple
     * @param string $value Current value (selected).
     * @return string HTML
     */
    public function buildHtmlSelect($name, $multiple, $value)
    {
        $html = '<select name="' . $name . '">'
                . '<option value=""></option>';
        foreach($this->scorings[$multiple ? 'multiple' : 'single'] as $scoring) {
            /* @var $scoring Scoring */
            $html .= '<option value="' . htmlspecialchars($scoring->formula) . '"'
                    . ($scoring->formula === $value ? ' selected="selected"' : '')
                    . ' data-score="' . $scoring->score . '">'
                    . htmlspecialchars($scoring->name)
                    . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
}

class Scoring
{
    public $name = '';
    public $score = 0;
    public $multiple = false;
    public $formula = '';
}
