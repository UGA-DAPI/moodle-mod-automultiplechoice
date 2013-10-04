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

    protected $codelength = 0;

    public $workdir;
    public $relworkdir;

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

        $dir = sprintf('automultiplechoice_%05d', $this->quizz->id);
        $this->workdir = $CFG->dataroot . '/local/automultiplechoice/' . $dir;
        $this->relworkdir = '/local/automultiplechoice/' . $dir;

        $this->codelength = (int) get_config('mod_automultiplechoice', 'amccodelength');
        /**
         * @todo error if codelength == 0
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
     * Compute the whole source file content, by merging header and questions blocks
     * @return string file content
     */
    public function getSourceAmctxt() {
        $res = $this->getHeaderAmctxt();

        foreach ($questions = $this->quizz->questions->getRecords() as $question) {
            $res .= $this->questionToFileAmctxt($question);

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
        if ($res) {
            $this->log('prepare:source', 'prepare-source.txt');
        }
        return $res;
    }


    public function createPdf() {
        $pre = $this->workdir;
        $res = $this->shellExec('auto-multiple-choice prepare', array(
            '--n-copies', (string) $this->quizz->amcparams->copies,
            '--filter', 'plain',
            '--mode', 's',
            '--prefix', $pre,
            '--out-corrige', $pre . '/prepare-corrige.pdf',
            '--out-sujet', $pre . '/prepare-sujet.pdf',
            '--out-calage', $pre . '/prepare-calage.xy',
            $pre . '/prepare-source.txt'
            ));
        if ($res) {
            $this->log('prepare:pdf', 'prepare-corrige.pdf prepare-sujet.pdf');
        }
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


    protected function initWorkdir() {
        global $CFG;

        if ( ! file_exists($this->workdir) || ! is_dir($this->workdir)) {
            // mkdir($this->workdir, 0770);
            $templatedir = $CFG->dataroot . '/local/automultiplechoice/'
                . get_config('mod_automultiplechoice', 'amctemplate');
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
     * @param object $question record from the 'question' table
     * @return string
     */
    protected function questionToFileAmctxt($question) {
        global $DB;

        $answerstext = '';
        $trueanswers = 0;
        $answers = $DB->get_records('question_answers', array('question' => $question->id));
        foreach ($answers as $answer) {
            $trueanswer = ($answer->fraction > 0);
            $answerstext .= ($trueanswer ? '+' : '-') . " " . strip_tags($answer->answer) . "\n";
            $trueanswers += (int) $trueanswer;
        }
		$options = ($this->quizz->amcparams->shufflea ? '' : '[ordered]');
        $questiontext = ($trueanswers == 1 ? '*' : '**')
                . $options
                . ($question->scoring ? '[' . $question->scoring . ']' : '')
                . ' ' . $question->name . "\n" . strip_tags($question->questiontext) . "\n";

        return $questiontext . $answerstext . "\n";
    }

    /**
     * Computes the header block of the source file
     * @return string header block of the AMC-TXT file
     */
    protected function getHeaderAmctxt() {
        $descr = preg_replace('/\n\s*\n/', "\n", $this->quizz->description);
		$shuffleq = (int) $this->quizz->amcparams->shuffleq;
		$separatesheet = (int) $this->quizz->amcparams->separatesheet;

        return "
# AMC-TXT source
PaperSize: A4
Lang: FR
Code: {$this->codelength}
ShuffleQuestions: {$shuffleq}
SeparateAnswerSheet: {$separatesheet}
Title: {$this->quizz->name}
Presentation: {$descr}

";
    }
}
