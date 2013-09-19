<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

class AmcProcess
{
    /**
     * @var Quizz Contains notably an 'amcparams' attribute.
     */
    protected $quizz;

    /**
     * @var array
     */
    protected $errors = array();

    /**
     * Constructor
     *
     * @param Quizz $quizz
     */
    public function __construct($quizz) {
        $this->quizz = $quizz;
    }

    /**
     * Return the path to a PDF file.
     *
     * @return string Path to a PDF file.
     */
    public function publish()
    {
        /**
         * @todo Fill publish()... and rename func?
         */
    }

    /**
     * Return the errors of the last command.
     * @return array
     */
    public function getLastErrors() {
        return $this->errors;
    }

    /**
     *
     * @param string $cmd
     * @param array $params List of strings.
     * @return boolean Success?
     */
    protected function shellExec($cmd, $params) {
        $escapedCmd = escapeshellcmd($cmd);
        $escapedParams = array_map('escapeshellarg', $params);
        $lines = array();
        $returnVal = 0;
        exec($escapedCmd . " " . join(" ", $escapedParams), $lines, $returnVal);
        /**
         * @todo return $lines? or put them in a attr like errors?
         */
        if ($returnVal === 0) {
            return true;
        } else {
            /**
             * @todo Fill $this->errors
             */
            return false;
        }
    }


	/**
	 * Turns a question into a formatted string, in the AMC-txt (aka plain) format
	 * @param $questionid questionid from the 'question' table
	 * @return string
	 */
	protected function questionToFileAmctxt($questionid) {
		global $DB;

		$answerstext = '';
		$trueanswers = 0;
		$dbquestion = $DB->get_record('question', array('id' => $questionid), '*', MUST_EXIST);		
		$answers = $DB->get_records('question_answers', array('question' => $questionid));
		foreach ($answers as $answer) {
			$trueanswer = (bool) ((int)$answer->fraction > 0.0);
			$bullet = ($trueanswer ? '+' : '-');
			$answerstext .= $bullet . " " . $answer->answer . "\n";
			$trueanswers += (int)($trueanswer);
		}
		$questiontext = ($trueanswers == 1 ? '*' : '**') . ' ';
		$questiontext .= $dbquestion->name . "\n" . $dbquestion->questiontext . "\n";

		return $questiontext . $answerstext . "\n";
	}

	protected function getHeaderAmctxt() {

		$res  = "# AMC-TXT source\n";
		$res .= "PaperSize: A4\n";
		$res .= "Lang: FR\n";
		$res .= "Title: " . $this->quizz->name . "\n\n";
		$res .= $this->quizz->description . "\n\n";

		return $res;
	}

	public function getSourceAmctxt() {
		$res = $this->getHeaderAmctxt();

		foreach ($questions = $this->quizz->questions->questions as $question) {
			$res .= $this->questionToFileAmctxt($question['questionid']);

		}
		return $res;
	}

	public function saveAmctxt($filename) {
		$file = fopen($filename, 'w');
		fwrite($file, $this->getSourceAmctxt());
		fclose($file);
	}
}
