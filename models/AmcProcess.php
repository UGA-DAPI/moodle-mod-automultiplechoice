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

    protected $relworkdir;

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

    /**
     * Shell-executes 'amc prepare' for creating pdf files
     * @return bool
     */
    public function createPdf() {
        $pre = $this->workdir;
        $res = $this->shellExec('auto-multiple-choice prepare', array(
            '--n-copies', (string) $this->quizz->amcparams->copies,
            '--with', 'xelatex',
            '--filter', 'plain',
            '--mode', 's[sc]',
            '--prefix', $pre,
            '--out-corrige', $pre . '/prepare-corrige.pdf',
            '--out-sujet', $pre . '/prepare-sujet.pdf',
            '--out-catalog', $pre . '/prepare-catalog.pdf',
            '--out-calage', $pre . '/prepare-calage.xy',
            '--latex-stdout',
            $pre . '/prepare-source.txt'
            ));
        if ($res) {
            $this->log('prepare:pdf', 'prepare-catalog.pdf prepare-corrige.pdf prepare-sujet.pdf');
        }
        return $res;
    }

    /**
     * Shell-executes 'amc imprime'
     * @param bool $split if true, put answer sheets in separate files
     * @return bool
     */
    public function amcImprime($split) {
        $pre = $this->workdir;
        $params = array(
                    '--data', $pre . '/data',
                    '--sujet', $pre . '/prepare-sujet.pdf',
                    '--methode', 'file',
                    '--output', $pre . '/imprime/sujet-%e.pdf'
                );
        if ($split) {
            $params[] = '--split';
        }
        $res = $this->shellExec('auto-multiple-choice imprime', $params, true);
        if ($res) {
            $this->log('imprime', '');
        }
        return $res;
    }

    /**
     * exectuces "amc imprime" then zip the resulting files
     * @param bool $split if true, put answer sheets in separate files
     * @return bool
     */
    public function printAndZip($split) {
        $pre = $this->workdir;
        $mask = $pre . "/imprime/*.pdf";
        array_map('unlink', glob( $mask ));
        $this->amcImprime($split);

        $zip = new \ZipArchive();
        $ret = $zip->open($pre . '/sujets.zip', \ZipArchive::OVERWRITE);
        if ( ! $ret ) {
            printf("Echec lors de l'ouverture de l'archive %d", $ret);
        } else {
            $options = array('add_path' => 'sujets_amc/', 'remove_all_path' => true);
            $zip->addGlob($mask, GLOB_BRACE, $options);
            // echo "Zip status: [" . $zip->status . "]<br />\n";
            // echo "Zip statusSys: [" . $zip->statusSys . "]<br />\n";
            echo "Zipped [" . $zip->numFiles . "] files into [" . basename($zip->filename) . "]<br />\n";
            $zip->close();
        }
        return $ret;
    }

    /**
     * Shell-executes 'amc meptex'
     * @return bool
     */
    public function amcMeptex() {
        $pre = $this->workdir;
        $res = $this->shellExec(
                'auto-multiple-choice meptex',
                array(
                    '--data', $pre . '/data',
                    '--progression-id', 'MEP',
                    '--progression', '1',
                    '--src', $pre . '/prepare-calage.xy',
                )
        );
        if ($res) {
            $this->log('meptex', '');
        }
        return $res;
    }

    /**
     * Shell-executes 'amc getimages'
     * @param string $scanfile name, uploaded by the user
     * @return bool
     */
    public function amcGetimages($scanfile) {
        $pre = $this->workdir;
        $scanlist = $pre . '/scanlist';
        if (file_exists($scanlist)) {
            unlink($scanlist);
        }
        $mask = $pre . "/scans/*.ppm"; // delete all previous ppm files
        array_map('unlink', glob( $mask ));

        $res = $this->shellExec('auto-multiple-choice getimages', array(
            '--progression-id', 'analyse',
            '--vector-density', '250',
            '--orientation', 'portrait',
            '--list', $scanlist,
            '--copy-to', $pre . '/scans/',
            $scanfile
            ), true);
        if ($res) {
            $nscans = count(file($scanlist));
            $this->log('getimages', $nscans . ' pages');
            return $nscans;
        }
        return $res;
    }

    /**
     * Shell-executes 'amc analyse'
     * @param bool $multiple (see AMC) if multiple copies of the same sheet are possible
     * @return bool
     */
    public function amcAnalyse($multiple = true) {
        $pre = $this->workdir;
        $scanlist = $pre . '/scanlist';
        $parammultiple = '--' . ($multiple ? '' : 'no-') . 'multiple';
        $parameters = array(
            $parammultiple,
            '--tol-marque', '0.2,0.2',
            '--prop', '0.8',
            '--bw-threshold', '0.6',
            '--progression-id' , 'analyse',
            '--progression', '1',
            '--n-procs', '0',
            '--data', $pre . '/data',
            '--projet', $pre,
            '--cr', $pre . '/cr',
            '--liste-fichiers', $scanlist,
            '--no-ignore-red',
            );
        //echo "\n<br> auto-multiple-choice analyse " . join (' ', $parameters) . "\n<br>";
        $res = $this->shellExec('auto-multiple-choice analyse', $parameters, true);
        if ($res) {
            $this->log('analyse', 'OK.');
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
        $sql = 'SELECT FROM_UNIXTIME(time) FROM {log} WHERE action=? AND cmid=? ORDER BY time DESC LIMIT 1';
        $res = $DB->get_field_sql($sql, array($action, $cm->id), IGNORE_MISSING);
        return $res;
    }

    /**
     * Gets the moodle_url that points to a file produced by this instance.
     *
     * @global moodle_page $PAGE
     * @param string $filename Local path and file name.
     * @param boolean $forcedld (opt, false)
     * @param integer $contextid (opt)
     * @return \moodle_url
     */
    public function getFileUrl($filename, $forcedld=false, $contextid=null) {
        global $PAGE;
        if (!$contextid) {
            $contextid = $PAGE->context->id;
        }
        return \moodle_url::make_pluginfile_url(
                $contextid,
                'mod_automultiplechoice',
                '',
                NULL,
                '',
                $this->relworkdir . '/' . ltrim($filename, '/'),
                $forcedld
        );
    }

    protected function initWorkdir() {
        global $CFG;

        if ( ! file_exists($this->workdir) || ! is_dir($this->workdir)) {
            $templatedir = get_config('mod_automultiplechoice', 'amctemplate');
            $diag = $this->shellExec('cp', array('-r', $templatedir, $this->workdir));
        }
    }

    /**
     *
     * @param string $cmd
     * @param array $params List of strings.
     * @return boolean Success?
     */
    protected function shellExec($cmd, $params, $output=false) {
        $escapedCmd = escapeshellcmd($cmd);
        $escapedParams = array_map('escapeshellarg', $params);
        $shellCmd = $escapedCmd . " " . join(" ", $escapedParams);
        $lines = array();
        $returnVal = 0;
        exec($shellCmd, $lines, $returnVal);
            if ($output) {
                $this->shellOutput($shellCmd, $returnVal, $lines, DEBUG_DEVELOPER);
            }
        if ($returnVal === 0) {
            return true;
        } else {
            /**
             * @todo Fill $this->errors
             */
            $this->shellOutput($shellCmd, $returnVal, $lines, DEBUG_NORMAL);
            return false;
        }
    }

    /**
     * Displays a block containing the shell output
     *
     * @param string $cmd
     * @param integer $returnVal shell return value
     * @param array $lines output lines to be displayed
     */
    protected function shellOutput($cmd, $returnVal, $lines, $debuglevel) {
        if (get_config('core', 'debugdisplay') == 0) {
            return false;
        }
        $html = '<pre style="margin:2px; padding:2px; border:1px solid grey;">' . " \n";
        $html .= $cmd . " \n";
        $i=0;
        foreach ($lines as $line) {
            $i++;
            $html .= sprintf("%03d.", $i) . " " . $line . "\n";
        }
        $html .= "Return value = <b>" . $returnVal. "</b\n";
        $html .= "</pre> \n";
        debugging($html, $debuglevel);
    }

    /**
     * Compute the whole source file content, by merging header and questions blocks
     * @return string file content
     */
    protected function getSourceAmctxt() {
        $res = $this->getHeaderAmctxt();
        foreach ($this->quizz->questions->getRecords($this->quizz->amcparams->scoringset) as $question) {
            $res .= $this->questionToFileAmctxt($question);

        }
        return $res;
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
        $dp = $this->quizz->amcparams->displaypoints;
        $points = ($question->score == round($question->score) ? $question->score :
                (abs(round(10*$question->score) - 10*$question->score) < 1 ? sprintf('%.1f', $question->score)
                    : sprintf('%.2f', $question->score)));
        $points = $points ? '(' . $points . ' pt' . ($question->score > 1 ? 's' : '') . ')' : '';
        $options = ($this->quizz->amcparams->shufflea ? '' : '[ordered]');
        $questiontext = ($trueanswers == 1 ? '*' : '**')
                . $options
                . ($question->scoring ? '[' . $question->scoring . ']' : '')
                . ' ' . ($dp == AmcParams::DISPLAY_POINTS_BEGIN ? $points . ' ' : '')
                . $question->name . "\n" . strip_tags($question->questiontext)
                . ($dp == AmcParams::DISPLAY_POINTS_END ? ' ' . $points : '')
                . "\n";

        return $questiontext . $answerstext . "\n";
    }

    /**
     * Computes the header block of the source file
     * @return string header block of the AMC-TXT file
     */
    protected function getHeaderAmctxt() {
        $descr = preg_replace('/\n\s*\n/', "\n", $this->quizz->description);
        $params = $this->quizz->amcparams;

        return "# AMC-TXT source
PaperSize: A4
Lang: FR
Code: {$this->codelength}
ShuffleQuestions: {$params->shuffleq}
SeparateAnswerSheet: {$params->separatesheet}
Title: {$this->quizz->name}
Presentation: {$descr}
L-Name: {$params->lname}
L-Student: {$params->lstudent}
LaTeX-BeginDocument: \def\multiSymbole{}

";
    }
}
