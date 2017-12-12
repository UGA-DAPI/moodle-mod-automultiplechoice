<?php

namespace mod_automultiplechoice\local\amc;

class params {
    const DISPLAY_POINTS_NO = 0;
    const DISPLAY_POINTS_BEGIN = 1;
    const DISPLAY_POINTS_END = 2;
    const RAND_MINI = 1000;
    const RAND_MAXI = 100000;

    public static $scoreRcoundingValues = [
        'n' => "Au plus proche",
        'i' => "Inférieur",
        's' => "Supérieur",
    ];
    /**
     * Keys are field names in the form.
     *
     * @var array
     */
    public $errors = array();
    /**
     * Display the number of points for each question
     *
     * @var integer
     */
    public $displaypoints;
    /**
     * Number of copies
     *
     * @var integer
     */
    public $copies;
    /**
     * Shuffle questions
     *
     * @var boolean
     */
    public $shuffleq;
    /**
     * Shuffle answers
     *
     * @var boolean
     */
    public $shufflea;
    /** @var integer random seed for AMC*/
    public $randomseed;
    /** @var integer 0=auto */
    public $questionsColumns = 1;
    /** @var boolean Separate answer sheet  */
    public $separatesheet;
    /** @var integer 0=auto */
    public $answerSheetColumns = 0;
    /** @var string Instructions for the student number */
    public $lstudent;
    /** @var string Instructions for the student name */
    public $lname;
    /** @var integer */
    public $scoringset;
    /** @var boolean */
    public $markmulti = false;
    /** @var boolean */
    public $score = false;
    /** @var boolean */
    public $locked = false;
    /** @var integer */
    public $minscore = 0;
    /** @var integer */
    public $grademax = 0;
    /** @var float */
    public $gradegranularity = 0.25;
    /** @var string within AmcParams::$scoreRoundingValues */
    public $graderounding = 'n'; // nearest
    /**
     * @var string
     */
    public $instructionsprefix = '';
    /**
     * Prefix for the instruction
     *
     * @var integer
     */
    public $instructionsprefixformat = 2; // FORMAT_PLAIN
    //public $customlayout;
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
        foreach (['displaypoints', 'copies', 'questionsColumns', 'answerSheetColumns', 'minscore', 'grademax'] as $col) {
            if (isset($input[$col])) {
                $this->$col = (int) $input[$col];
            }
        }
        foreach (['shuffleq', 'shufflea', 'separatesheet', 'markmulti','score'] as $col) {
            if (isset($input[$col])) {
                $this->$col = (bool) $input[$col];
            }
        }
        foreach (['lstudent', 'lname', 'separatesheet', 'scoringset', 'customlayout'] as $col) {
            if (isset($input[$col])) {
                $this->$col = (string) $input[$col];
            }
        }
        foreach (['gradegranularity'] as $col) {
            if (isset($input[$col])) {
                $this->$col = (double) $input[$col];
            }
        }
        if (isset($input['graderounding']) && isset(self::$scoreRcoundingValues[$input['graderounding']])) {
            $this->graderounding = $input['graderounding'];
        }
        if (isset($input['instructionsprefix'])) {
            if (is_array($input['instructionsprefix'])) {
                $this->instructionsprefix = (string) $input['instructionsprefix']['text'];
                $this->instructionsprefixformat = (int) $input['instructionsprefix']['format'];
            } else {
                $this->instructionsprefix = (string) $input['instructionsprefix'];
                $this->instructionsprefixformat = (int) $input['instructionsprefixformat'];
            }
        }
        if (!isset($this->randomseed)) {
            $this->randomize();
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
     * @param string $json the json to deserialize
     *
     * @return a new instance of amc_params
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
