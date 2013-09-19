<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

class AmcParams
{
    /**
     * @var array Keys are field names in the form.
     */
    public $errors = array();

    /**
     * @var integer Number of copies.
     */
    public $copies;

    /**
     * @todo Add other fields
     */


    /**
     * Validate the instance and update $this->errors.
     *
     * @return boolean
     */
    public function validate() {
        $this->errors = array();
        if ($this->copies <= 0) {
            $this->errors['amc[copies]'] = '> 0 !';
        }
        return empty($this->errors);
    }

    /**
     * Return a new instance.
     *
     * @return AmcParams
     */
    public static function fromForm($input)
    {
        $new = new self;
        $new->copies = (int) $input['copies'];
        /**
         * @todo Add other fields
         */
        return $new;
    }

    /**
     * Return the JSON serialization of this instance.
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this);
    }

    /**
     * Return a new instance from a serialized JSON instance.
     *
     * @param string $json
     * @return AmcParams
     */
    public static function fromJson($json)
    {
        $new = new self();
        $decoded = json_decode($json);
        if (!empty($decoded)) {
            foreach ($decoded as $attr => $v) {
                $new->$attr = $v;
            }
        }
        return $new;
    }
}
