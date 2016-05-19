<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

abstract class QuestionListItem implements \JsonSerializable
{
    /**
     * Build an instance from a serialized array.
     *
     * @param StdClass $array
     */
    static public function fromArray($array) {
    }

    /**
     * @return string
     */
    abstract public function getType();

    /**
     * @param boolean $model (opt, False) If true, add a ("model" => "...") key.
     * @return array Assoc array
     */
    abstract public function toArray($model = false);

    abstract public function toHtml($displayScore = false);
}
