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
    const RAND_MINI = 1000;
    const RAND_MAXI = 100000;

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

    /** @var integer random seed for AMC*/
    public $randomseed;

    /** @var integer 0=auto */
    public $questionsColumns = 1;

    /** @var boolean Separate answer sheet  */
    public $separatesheet;

    /** @var integer 0=auto */
    public $answerSheetColumns;

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

    public $instructionsprefixformat = 2; // FORMAT_PLAIN

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
        if ($this->copies > 1 && !$this->shufflea && !$this->shuffleq) {
            $this->errors['amc[copies]'] = 'validate_copies_without_shuffle';
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
     * @param array $input
     * @return AmcParams
     */
    public static function fromForm($input)
    {
        $new = new self;
        return $new->readFromForm($input);
    }

    /**
     * reset the random seed
     */
    public function randomize()
    {
        $this->randomseed = rand(self::RAND_MINI, self::RAND_MAXI);
        return true;
    }

    /**
     * Update using the form data..
     *
     * @param array $input
     * @return AmcParams
     */
    public function readFromForm($input)
    {
        $this->displaypoints = (int) $input['displaypoints'];
        $this->copies = (int) $input['copies'];
        $this->questionsColumns = (int) $input['questionsColumns'];
        $this->shuffleq = (bool) $input['shuffleq'];
        $this->shufflea = (bool) $input['shufflea'];
        $this->separatesheet = (bool) $input['separatesheet'];
        if (isset($input['answerSheetColumns'])) {
            $this->answerSheetColumns = (int) $input['answerSheetColumns'];
        } else {
            $this->answerSheetColumns = 0;
        }
        if ( ! isset($this->randomseed) ) {
            $this->randomize();
        }
        $this->lstudent = (string) $input['lstudent'];
        $this->lname = (string) $input['lname'];
        $this->markmulti = (bool) $input['markmulti'];
        if (is_array($input['instructionsprefix'])) {
            $this->instructionsprefix = (string) $input['instructionsprefix']['text'];
            $this->instructionsprefixformat = (int) $input['instructionsprefix']['format'];
        } else {
            $this->instructionsprefix = (string) $input['instructionsprefix'];
            $this->instructionsprefixformat = (int) $input['instructionsprefixformat'];
        }
        if (isset($input['scoringset'])) {
            $this->scoringset = (string) $input['scoringset'];
            $this->minscore = (int) $input['minscore'];
        }
        return $this;
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
        if (empty($new->randomseed)) {
            $new->randomize();
        }
        return $new;
    }


}
