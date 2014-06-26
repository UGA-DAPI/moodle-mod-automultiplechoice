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
     * Return the HTML that lists links to the PDF files.
     *
     * @return string
     */
    public function htmlPdfLinks() {
        $opts = array('target' => '_blank');
        $links = array(
            \html_writer::link($this->getFileUrl($this->normalizeFilename('sujet')), $this->normalizeFilename('sujet'), $opts),
            \html_writer::link($this->getFileUrl($this->normalizeFilename('catalog')), $this->normalizeFilename('catalog'), $opts),
        );
        return <<<EOL
        <ul class="amc-files">
            <li>
                $links[0]
                <div>Ce fichier contient tous les énoncés regroupés. <span class="warning">Ne pas utiliser ce fichier pour distribuer aux étudiants.</span></div>
            </li>
            <li>
                $links[1]
                <div>Le corrigé de référence.</div>
            </li>
        </ul>
EOL;
    }

    /**
     * Return the HTML that for the link to the ZIP file.
     *
     * @return string
     */
    public function htmlZipLink() {
        $links = array(
            \html_writer::link($this->getFileUrl($this->normalizeFilename('sujets')), $this->normalizeFilename('sujets')),
        );
        return <<<EOL
        <ul class="amc-files">
            <li>
                $links[0]
                <div>Cette archive contient un PDF par variante de l'énoncé.</div>
            </li>
        </ul>
EOL;
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
            '--out-corrige', $pre . '/' . $this->normalizeFilename('corrige'),
            '--out-sujet', $pre . '/' . $this->normalizeFilename('sujet'),
            '--out-catalog', $pre . '/' . $this->normalizeFilename('catalog'),
            '--out-calage', $pre . '/prepare-calage.xy',
            '--latex-stdout',
            $pre . '/prepare-source.txt'
            ));
        if ($res) {
            $this->log('prepare:pdf', 'catalog corrige sujet');
        }
        return $res;
    }

    /**
     * exectuces "amc imprime" then zip the resulting files
     * @return bool
     */
    public function printAndZip() {
        $pre = $this->workdir;
        if (!is_dir($pre . '/imprime')) {
            mkdir($pre . '/imprime');
        }

        $mask = $pre . "/imprime/*.pdf";
        array_map('unlink', glob($mask));
        $this->amcImprime();

        $zip = new \ZipArchive();
        $ret = $zip->open($pre . '/' . $this->normalizeFilename('sujets'), \ZipArchive::CREATE);
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
        if (!file_exists($pre . '/' . $this->normalizeFilename('sujets'))) {
            echo "<strong>Erreur lors de la création de l'archive Zip : le fichier n'a pas été créé.</strong> $mask\n";
        }
        return $ret;
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
     * @return bool
     */
    protected function amcImprime() {
        $pre = $this->workdir;
        $params = array(
                    '--data', $pre . '/data',
                    '--sujet', $pre . '/' . $this->normalizeFilename('sujet'),
                    '--methode', 'file',
                    '--output', $pre . '/imprime/sujet-%e.pdf'
                );
        // $params[] = '--split'; // M#2076 a priori jamais nécessaire
        $res = $this->shellExec('auto-multiple-choice imprime', $params);
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
                . str_replace("\n", " ", strip_tags($question->questiontext))
                . ($dp == AmcParams::DISPLAY_POINTS_END ? ' ' . $pointsTxt : '')
                . "\n";

        return $questiontext . $answerstext . "\n";
    }

    /**
     * Computes the header block of the source file
     * @return string header block of the AMC-TXT file
     */
    protected function getHeaderAmctxt() {
        $descr = $this->quizz->getInstructions();
        $params = $this->quizz->amcparams;
        $markMulti = $params->markmulti ? '' : "LaTeX-BeginDocument: \def\multiSymbole{}\n";
        $columns = (int) ceil($this->quizz->questions->count() / 28); // empirical guess, should be in config?

        return "# AMC-TXT source
PaperSize: A4
Lang: FR
Code: {$this->codelength}
CompleteMulti: 0
LaTeX-Preambule: \usepackage{amsmath,amssymb}
ShuffleQuestions: {$params->shuffleq}
SeparateAnswerSheet: {$params->separatesheet}
AnswerSheetColumns: {$columns}
Title: {$this->quizz->name}
Presentation: {$descr}
L-Name: {$params->lname}
L-Student: {$params->lstudent}
$markMulti
";
    }
}
