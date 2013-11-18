<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

require_once __DIR__ . '/AmcProcess.php';

class AmcProcessPrepare extends AmcProcess
{
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
     * Initialize the data directory $this->workdir with the template structure.
     */
    protected function initWorkdir() {
        if ( ! file_exists($this->workdir) || ! is_dir($this->workdir)) {
            $parent = dirname($this->workdir);
            if (!is_dir($parent)) {
                if (!mkdir($parent, 0777, true)) {
                    error("Could not create directory. Please contact the administrator.");
                }
            }
            if (!is_writeable($parent)) {
                error("Could not write in directory. Please contact the administrator.");
            } else {
                $templatedir = get_config('mod_automultiplechoice', 'amctemplate');
                $this->shellExec('cp', array('-r', $templatedir, $this->workdir));
            }
        }
    }

    /**
     * Shell-executes 'amc imprime'
     * @param bool $split if true, put answer sheets in separate files
     * @return bool
     */
    protected function amcImprime($split) {
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
        $answers = $DB->get_records('question_answers', array('question' => $question->id));
        foreach ($answers as $answer) {
            $answerstext .= ($answer->fraction > 0 ? '+' : '-') . " " . strip_tags($answer->answer) . "\n";
        }
        $dp = $this->quizz->amcparams->displaypoints;
        $points = ($question->score == round($question->score) ? $question->score :
                (abs(round(10*$question->score) - 10*$question->score) < 1 ? sprintf('%.1f', $question->score)
                    : sprintf('%.2f', $question->score)));
        $pointsTxt = $points ? '(' . $points . ' pt' . ($question->score > 1 ? 's' : '') . ')' : '';
        $options = ($this->quizz->amcparams->shufflea ? '' : '[ordered]');
        $questiontext = ($question->single ? '*' : '**')
                . $options
                . ($question->scoring ? '{' . $question->scoring . '}' : '') . ' '
                . ($dp == AmcParams::DISPLAY_POINTS_BEGIN ? $pointsTxt . ' ' : '')
                . $question->name . "\n" . strip_tags($question->questiontext)
                . ($dp == AmcParams::DISPLAY_POINTS_END ? ' ' . $pointsTxt : '')
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
        $markMulti = $params->markmulti ? '' : "LaTeX-BeginDocument: \def\multiSymbole{}\n";

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
$markMulti
";
    }
}
