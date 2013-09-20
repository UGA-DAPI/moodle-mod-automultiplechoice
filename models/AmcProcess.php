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
	protected $workdir;

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
		global $CFG;
        $this->quizz = $quizz;

		$this->workdir = $CFG->dataroot . '/local/automultiplechoice/' .
			sprintf('automultiplechoice_%05d', $this->quizz->id);
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

	public function initWorkdir() {
		global $CFG;
		
		if ( ! file_exists($this->workdir) || ! is_dir($this->workdir)) {
			// mkdir($this->workdir, 0770);
			$templatedir = $CFG->dataroot . '/local/automultiplechoice/'
				. get_config('moodle', 'amctemplate');
			$diag = $this->shellExec('cp', array('-a', $templatedir, $this->workdir));
		}
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
		//echo "CMD = " . $escapedCmd . " " . join(" ", $escapedParams), $lines, $returnVal . "<br /><br />\n\n";
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

	/**
	 * Computes the header block of the source file
	 * @return string header block of the AMC-TXT file
	 */
	protected function getHeaderAmctxt() {

		$res  = "# AMC-TXT source\n";
		$res .= "PaperSize: A4\n";
		$res .= "Lang: FR\n";
		$res .= "Code: " . get_config('moodle', 'amccodelength') . "\n";
		$res .= "Title: " . $this->quizz->name . "\n";
		$res .= "Presentation: " . $this->quizz->description . "\n\n";

		return $res;
	}

	/**
	 * Compute the whole source file content, by merging header and questions blocks
	 * @return string file content
	 */
	public function getSourceAmctxt() {
		$res = $this->getHeaderAmctxt();

		foreach ($questions = $this->quizz->questions->questions as $question) {
			$res .= $this->questionToFileAmctxt($question['questionid']);

		}
		return $res;
	}

	/**
	 * Save the source file
	 * @param type $filename
	 */
	public function saveAmctxt() {

		$this->initWorkdir();
		$filename = $this->workdir . "/prepare-source.txt";
		$res = file_put_contents($filename, $this->getSourceAmctxt());
		return $res;
	}


	/**
	 * log processed action
	 * @param string $action ('prepare'...)
	 * @param string $msg
	 */
	public function log($action, $msg) {
		$url = '/mod/automultiplechoice/view.php?a='. $this->quizz->id;
		$cm = get_coursemodule_from_instance('automultiplechoice', $this->quizz->id, $this->quizz->course, false, MUST_EXIST);
		add_to_log($this->quizz->course, 'automultiplechoice', $action, $url, $msg, $cm->id, 0);
		return true;
	}


	public function lastlog($action) {
		global $DB;

		$cm = get_coursemodule_from_instance('automultiplechoice', $this->quizz->id, $this->quizz->course, false, MUST_EXIST);
		$sql = 'SELECT FROM_UNIXTIME(time) FROM log WHERE action=? AND cmid=? ORDER BY time DESC LIMIT 1';
		$res = $DB->get_field_sql($sql, array($action, $cm->id), IGNORE_MISSING);
		return $res;
	}
}
