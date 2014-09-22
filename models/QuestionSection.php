<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

require_once __DIR__ . '/QuestionListItem.php';

class QuestionSection extends QuestionListItem
{
    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @param string $name
     * @param string $description
     */
    public function __construct($name = '', $description = '') {
        $this->name = $name;
        $this->description = $description;
    }

    /**
     * Build an instance from a serialized array.
     *
     * @param array $array
     */
    static public function fromArray($array) {
        if (!empty($array['questionid']) || !empty($array['id'])) {
            throw new \Exception("Data error: extra section id");
        }
        if (empty($array['name'])) {
            throw new \Exception("Data error: missing section name");
        }

        if (isset($array['description'])) {
            $description = $array['description'];
        } else {
            $description = '';
        }
        return new self($array['name'], $description);
    }

    /**
     * @return string
     */
    public function getType() {
        return 'section';
    }

    /**
     * @param boolean $model (opt, False) If true, add a ("model" => "QuestionSection") key.
     * @return array Assoc array
     */
    public function toArray($model = false) {
        $a = array(
            'name' => $this->name,
            'description' => $this->description,
        );
        if ($model) {
            $a["model"] = "QuestionSection";
        }
        return $a;
    }

    /**
     * serialize in JSON.
     *
     * @return string
     */
    public function jsonSerialize() {
        return $this->toArray(true);
    }

    /**
     * @param boolean $displayScore Ignored
     * @return string
     */
    public function toHtml($displayScore = false) {
        return '
        <li class="ui-state-default">
            <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
            <label>[section]</label>
            <input name="question[type][]" value="section" type="hidden" />
            <input name="question[id][]" value="' . htmlspecialchars($this->name) . '" type="text" size="50" />
            <input name="question[score][]" type="hidden" />
            <button type="button" title="' . format_string(get_string('remove')) .'">&lt;&lt;</button>
        </li>
';

    }
}
