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
    const DISPLAY_POINTS_NO = 0;
    const DISPLAY_POINTS_BEGIN = 1;
    const DISPLAY_POINTS_END = 2;

    /** @var integer Display the number of points for each question */
    public $displaypoints;

    /**
     * @var array Keys are field names in the form.
     */
    public $errors = array();

    /** @var integer Number of copies  */
    public $copies;

    /** @var boolean Shuffle questions  */
    public $shuffleq;

    /** @var boolean Shuffle answers  */
    public $shufflea;

    /** @var boolean Separate answer sheet  */
    public $separatesheet;

    /** @var string Instructions for the student number */
    public $lstudent;

    /** @var string Instructions for the student name */
    public $lname;

    /** @var integer */
    public $scoringset;

    /** @var boolean */
    public $markmulti = false;

    /** @var boolean */
    public $locked = false;

    /** @var integer */
    public $minscore = 0;

    /**
     * @var string
     */
    public $instructionsprefix = '';

    /**
     * Validate the instance and update $this->errors.
     *
     * @return boolean
     */
    public function validate($maxscore) {
        $this->errors = array();
        if ($this->copies <= 0) {
            $this->errors['amc[copies]'] = 'validate_positive_int';
        }
        if ($this->minscore < 0) {
            $this->errors['amc[minscore]'] = 'validate_poszero_int';
        }
        if ($this->minscore > $maxscore) {
            $this->errors['amc[minscore]'] = 'validate_under_maxscore';
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
        $new->displaypoints = (int) $input['displaypoints'];
        $new->copies = (int) $input['copies'];
        $new->shuffleq = (bool) $input['shuffleq'];
        $new->shufflea = (bool) $input['shufflea'];
        $new->separatesheet = (bool) $input['separatesheet'];
        $new->lstudent = $input['lstudent'];
        $new->lname = $input['lname'];
        $new->markmulti = (bool) $input['markmulti'];
        $new->instructionsprefix = $input['instructionsprefix'];
        if (isset($input['scoringset'])) {
            $new->scoringset = $input['scoringset'];
            $new->minscore = (int) $input['minscore'];
        }
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
