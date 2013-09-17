<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

global $DB;
/* @var $DB \moodle_database */

class QuestionList
{
	/**
     * @var array array of array('questionid' => (integer), 'score' => (integer)
     */
    public $questions = array();

    /**
     * Return the JSON serialization of this instance.
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode(array_map('array_values', $this->questions));
    }

    /**
     * Return a new instance from a serialized JSON instance.
     *
     * @param string $json
     * @return QuetionList
     */
    public static function fromJson($json)
    {
        $new = new self();
        $decoded = json_decode($json);
        if (!empty($decoded) && is_array($decoded)) {
            foreach ($decoded as $q) {
                $new->questions[] = array(
                    'questionid' => (int) $q[0],
                    'score' => (int) $q[1],
                );
            }
        }
        return $new;
    }

    /**
     * Read $_POST[$fieldname] and return a new instance.
     *
     * @return QuetionList
     */
    public static function fromForm($fieldname) {
        $new = new self();
        if (!isset($_POST[$fieldname]) || empty($_POST[$fieldname]['id'])) {
            return $new;
        }
        for ($i = 0; $i < count($_POST[$fieldname]['id']); $i++) {
            $new->questions[] = array(
                'questionid' => (int) $_POST[$fieldname]['id'][$i],
                'score' => (int) $_POST[$fieldname]['score'][$i],
            );
        }
        return $new;
    }
}
