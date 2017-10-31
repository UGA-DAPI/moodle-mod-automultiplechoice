<?php
namespace mod_automultiplechoice\local\models;
abstract class question_list_item implements \JsonSerializable
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
