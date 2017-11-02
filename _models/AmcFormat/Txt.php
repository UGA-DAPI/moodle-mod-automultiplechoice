<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice\amcFormat;

require_once __DIR__ . '/Api.php';

class Txt extends Api
{
    const FILENAME = 'prepare-source.txt';

    /**
     * @return string
     */
    public function getFilename() {
        return self::FILENAME;
    }

    /**
     * @return string
     */
    public function getFilterName() {
        return "plain";
    }

    /**
     * Turns a question into a formatted string, in the AMC-txt (aka plain) format.
     *
     * @param \mod\automultiplechoice\QuestionListItem $question record from the 'question' table
     * @return string
     */
    protected function convertQuestion($question) {
        global $DB;

        if ($question->getType() !== 'question') {
            /**
               * @todo Output sections in AMC-TXT, if possible.
               */
            return '';
        }

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
                . ($dp == \mod\automultiplechoice\AmcParams::DISPLAY_POINTS_BEGIN ? $pointsTxt . ' ' : '')
                . str_replace("\n", " ", strip_tags($question->questiontext))
                . ($dp == \mod\automultiplechoice\AmcParams::DISPLAY_POINTS_END ? ' ' . $pointsTxt : '')
                . "\n";

        return $questiontext . $answerstext . "\n";
    }

    /**
     * Computes the header block of the source file.
     *
     * @return string header block of the AMC-TXT file
     */
    protected function getHeader() {
        $descr = strip_tags($this->quizz->getInstructions());
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

    protected function getFooter() {
        return '';
    }
}
