<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2014 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice\AmcFormat;

require_once __DIR__ . '/Api.php';

class Latex extends Api
{
    const FILENAME = 'prepare-source.tex';

    protected $columns = 1;

    /**
     * @var array List of the groups defined with \element{...}
     */
    protected $groups = array();

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
        return "latex";
    }

    /**
     * Computes the header block of the source file
     * @return string header block of the AMC-TXT file
     */
    protected function getHeader() {
        $params = $this->quizz->amcparams;
        $this->columns = (int) ceil($this->quizz->questions->count() / 28); // empirical guess, should be in config?

        $options = "lang=FR%\n"
            . ",box% puts every question in a block, so that it cannot be split by a page break\n"
            . "%,completemulti% automatically adds a 'None of these answers are correct' choice at the end of each multiple question\n"
            . ($params->shuffleq ? '%' : '')
            . ",noshuffle% stops the automatic shuffling of the answers for every question\n"
            . ($params->separatesheet ? '' : '%')
            . ",separateanswersheet";
        $header = <<<EOL
\documentclass[a4paper]{article}

\usepackage[utf8x]{inputenc}
\usepackage[T1]{fontenc}

\usepackage{amsmath,amssymb}
\usepackage{multicol}

\usepackage[%
$options
]{automultiplechoice}

\begin{document}
%Code: {$this->codelength}
%L-Name: {$params->lname}
%L-Student: {$params->lstudent}

EOL;
        return $header
            . ($params->markmulti ? '' : "\\def\\multiSymbole{}\n")
            . "\\title{" . self::htmlToLatex($this->quizz->name) . "}\n";
    }

    /**
     * Turns a question into a formatted string, in the AMC-txt (aka plain) format.
     *
     * @param object $question record from the 'question' table
     * @return string
     */
    protected function convertQuestion($question) {
        global $DB;

        $output = '';

        // group
        $group = '';
        if (is_string($question)) {
            $group = self::normalizeIntoUnique($question);
            $this->groups[$group] = $question;
            if ($this->groups) {
                $output .= "} % close group\n";
            }
        } else if (!$this->groups) {
            $group = "default";
            $this->groups["default"] = '';
        }
        if ($group) {
            $output .= sprintf("\n\\element{%s}{\n", $group);
            if (is_string($question)) {
                return $output;
            }
        }

        // question
        $dp = $this->quizz->amcparams->displaypoints;
        $points = ($question->score == round($question->score) ? $question->score :
                (abs(round(10*$question->score) - 10*$question->score) < 1 ? sprintf('%.1f', $question->score)
                    : sprintf('%.2f', $question->score)));
        $pointsTxt = $points ? '(' . $points . ' pt' . ($question->score > 1 ? 's' : '') . ')' : '';
        $questionText = ($question->scoring ? '    \\scoring{' . $question->scoring . "}\n" : '')
                . ($dp == \mod\automultiplechoice\AmcParams::DISPLAY_POINTS_BEGIN ? $pointsTxt . ' ' : '')
                . self::htmlToLatex($question->questiontext)
                . ($dp == \mod\automultiplechoice\AmcParams::DISPLAY_POINTS_END ? ' ' . $pointsTxt : '');

        // answers
        $answersText = '';
        $answers = $DB->get_records('question_answers', array('question' => $question->id));
        foreach ($answers as $answer) {
            $answersText .= "        \\" . ($answer->fraction > 0 ? 'correct' : 'wrong') . "choice{"
                    . self::htmlToLatex($answer->answer) . "}\n";
        }

        // combine all
        $output .= sprintf('
\begin{question%s}{%s}
%s
    \begin{choices}%s
%s
    \end{choices}
\end{question%s}
',
                ($question->single ? '' : 'mult'),
                self::normalizeIntoUnique($question->name),
                $questionText,
                ($this->quizz->amcparams->shufflea ? '' : '[o]'),
                $answersText,
                ($question->single ? '' : 'mult')
        );
        return $output;
    }

    /**
     * Computes the header block of the source file.
     *
     * @return string footer block
     */
    protected function getFooter() {
        $output = "} % group\n"
            . "\n% Title\n\\begin{center}\n" . self::htmlToLatex($this->quizz->name) . "\n\\end{center}\n"
            . "\n% Instructions\n\\begin{center}\n" . self::htmlToLatex($this->quizz->getInstructions()) . "\n\\end{center}\n"
            . "\\vspace{1ex}\n%%% End of header\n\n";

        foreach ($this->groups as $name => $title) {
            if ($title) {
                $output .= sprintf("\\section*{%s}\n", self::htmlToLatex($title));
            }
            $output .= ($this->columns > 1 ? "\\begin{multicols}{$this->columns}\n" : "")
            . ($this->quizz->amcparams->shuffleq ? sprintf("\\shufflegroup{%s}\n", $name) : '')
            . sprintf("\\insertgroup{%s}\n", $name)
            . ($this->columns > 1 ? "\\end{multicols}\n" : "");
        }
        $output .= ($this->quizz->amcparams->separatesheet ? "\\AMCcleardoublepage\AMCformBegin\nANSWER SHEET\n\\AMCform" : "");
        $output .= "\\clearpage\n\\end{document}\n";
        return $output;
    }

    /**
     *
     * @param string $html UTF-8 HTML string
     * @return string
     */
    static protected function htmlToLatex($html) {
        /**
         * @todo Real conversion of the HTML DOM to LaTeX.
         * @todo Keep <tex>...</tex> unchanged.
         */
        return strip_tags(
            str_replace(
                array('<em>', '</em>', '<i>', '</i>', '<b>', '</b>', '<br/>', '<br />', '<br>'),
                array('\\emph{', '}', '\textit{', '}', '\textbf{', '}', '\newline', '\newline', '\newline'),
                str_replace(
                    array('\\',                '%'  , '&amp;', '&',  '~',  '{',  '}',  '[',  ']',  '_',  '^',  '$' ),
                    array('\\textbackslash{}', '\%' , '\&',    '\&', '\~', '\{', '\}', '\[', '\]', '\_', '\^', '\$'),
                    html_entity_decode($html)
                )
            )
        );
    }

    /**
     * @param string $text
     * @return string
     */
    static protected function normalizeIntoUnique($text) {
        return preg_replace('/[\s\\\\_^\$\[\]{}%&~,!?]+/', '-',
                @iconv('UTF-8', 'ASCII//TRANSLIT',
                        substr( html_entity_decode(strip_tags($text)), 0, 30 )
        )) . "-" . rand(1000,9999);
    }
}