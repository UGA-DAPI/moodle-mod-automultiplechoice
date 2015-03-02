<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2014 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice\amcFormat;

require_once __DIR__ . '/Api.php';
require_once dirname(dirname(__DIR__)) . '/components/HtmlToTex.php';

class Latex extends Api
{
    const FILENAME = 'prepare-source.tex';

    private $lastGroup = 'default';

    /**
     * @var array List of the groups defined with \element{...}
     */
    protected $groups = array();

    protected $tmpDir = '/tmp';

    /** @var \mod\automultiplechoice\ScoringSet */
    private $scoringset;

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

    public function __construct($quizz=null, $codelength=10) {
        parent::__construct($quizz, $codelength);
        if (!empty($quizz->id)) {
            $this->tmpDir = $this->quizz->getDirName() . '/htmlimages';
            if (!is_dir($this->tmpDir)) {
                mkdir($this->tmpDir);
            }
        }
    }

    /**
     * Computes the header block of the source file
     * @return string header block of the AMC-TXT file
     */
    protected function getHeader() {
        $this->scoringset = \mod\automultiplechoice\ScoringSystem::read()->getScoringSet($this->quizz->amcparams->scoringset);

        $params = $this->quizz->amcparams;
        $quizzName = $this->htmlToLatex($this->quizz->name);
        $multi = $params->markmulti ? '' : '\def\multiSymbole{}';
        $rand = $params->randomseed;

        $options = "lang=FR%\n"
            . ",box% puts every question in a block, so that it cannot be split by a page break\n"
            . "%,completemulti% automatically adds a 'None of these answers are correct' choice at the end of each multiple question\n"
            . ($params->shuffleq ? '%' : '')
            . ",noshuffle% stops the automatic shuffling of the answers for every question\n"
            . ($params->separatesheet ? '' : '%')
	    . ",separateanswersheet";
	$customlayout=$params->customlayout;
        $shortTitles = '';
        if ($this->quizz->amcparams->answerSheetColumns > 2) {
		$shortTitles = '\def\AMCformQuestion#1{\vspace{\AMCformVSpace}\par{\bf Q.#1 :}}
\def\AMCformAnswer#1{\hspace{\AMCformHSpace}#1}';
        }
        $header = <<<EOL
\\documentclass[a4paper]{article}

\\usepackage{ifxetex}
\\ifxetex
    \\usepackage{xltxtra}
    \\usepackage{xunicode}
    % The default font does not include greek, etc
    %\\usepackage{fontspec} \\setromanfont{TeX Gyre Pagella}
\\else
    \\usepackage[T1]{fontenc}
    \\usepackage[utf8]{inputenc}
\\fi

\\usepackage{amsmath,amssymb}
\\usepackage{multicol}
\\usepackage{environ}
\\usepackage{graphicx}

\\usepackage[%
$options
]{automultiplechoice}

\\date{}
\\author{}
\\title{{$quizzName}}
\\makeatletter
\\let\\mytitle\\@title
\\let\\myauthor\\@author
\\let\\mydate\\@date
\\makeatother

$customlayout
$shortTitles
$multi
\\AMCrandomseed{{$rand}}

\scoringDefaultS{}
\scoringDefaultM{}

\\newenvironment{instructions}{
}{
\\vspace{1ex}
\\hrule
\\vspace{2ex}
}
\\newcommand{\\answersheet}{
    \\begin{center}\\Large\\bf\\mytitle{} --- Feuille de réponse\\end{center}
}

\\begin{document}
%Code: {$this->codelength}

EOL;
        return $header;
    }

    /**
     * Turns a question into a formatted string, in the LaTeX format.
     *
     * @param \mod\automultiplechoice\QuestionListItem $question record from the 'question' table
     * @return string
     */
    protected function convertQuestion($question) {
        global $DB;

        $output = '';

        // group
        $group = '';
        if ($question->getType() === 'section') {
            $group = self::normalizeIntoUnique($question->name, true);
            $this->groups[$group] = $question;
            $this->lastGroup = $group;
            return '';
        } else if (!$this->groups) {
            $group = 'default';
            $this->groups[$group] = '';
            $this->lastGroup = $group;
        } else {
            $group = $this->lastGroup;
        }

        // question
        $dp = $this->quizz->amcparams->displaypoints;
        $points = ($question->score == round($question->score) ? $question->score :
                (abs(round(10*$question->score) - 10*$question->score) < 1 ? sprintf('%.1f', $question->score)
                    : sprintf('%.2f', $question->score)));
        $pointsTxt = $points ? '(' . $points . ' pt' . ($question->score > 1 ? 's' : '') . ')' : '';
        if ($question->scoring) {
            $scoring = $question->scoring;
        } else {
            $scoring = $this->scoringset->findMatchingRule($question)->getExpression($question);
        }
        if (!$scoring) {
            $scoring = 'b=' . $question->score;
        } 
        
        $questionText = ($scoring ? '    \\scoring{' . $scoring . "}\n" : '')
                . ($dp == \mod\automultiplechoice\AmcParams::DISPLAY_POINTS_BEGIN ? $pointsTxt . ' ' : '')
                . $this->htmlToLatex(format_text($question->questiontext, $question->questiontextformat, ['filter' => false]))
                . ($dp == \mod\automultiplechoice\AmcParams::DISPLAY_POINTS_END ? ' ' . $pointsTxt : '');

        // answers
        $answersText = '';
        $answers = $DB->get_records('question_answers', array('question' => $question->id));
        foreach ($answers as $answer) {
            $answersText .= "        \\" . ($answer->fraction > 0 ? 'correct' : 'wrong') . "choice{"
                    . $this->htmlToLatex($answer->answer) . "}\n";
        }

        // combine all
        $output .= sprintf('
\element{%s}{
    \begin{question%s}{%s}
    %s
        \begin{choices}%s
    %s    \end{choices}
    \end{question%s}
}
',
                $group,
                ($question->single ? '' : 'mult'),
                self::normalizeIntoUnique($question->name, false),
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
         // colums: empirical guess, should be in config?
        $columns = $this->quizz->amcparams->questionsColumns;
        if ($columns == 0) {
            $columns = $this->quizz->questions->count() > 5 ? 2 : 0;
        }

        $output = "\n\n"
            . "\\begin{examcopy}[{$this->quizz->amcparams->copies}]\n"
            . "\n% Title without \\maketitle\n\\begin{center}\\Large\\bf\\mytitle\\end{center}\n"
            . ($this->quizz->amcparams->separatesheet ? "" : $this->getStudentBlock())
            . "\n\\begin{instructions}\n" . $this->htmlToLatex($this->quizz->getInstructions(false)) . "\n\\end{instructions}\n"
            . "%%% End of header\n\n";

        foreach ($this->groups as $name => $section) {
            /* @var $section \mod\automultiplechoice\QuestionSection */
            if ($section) {
                $output .= sprintf("\\section*{%s}\n", $this->htmlToLatex($section->name));
                if ($section->description) {
                    $output .= $this->htmlToLatex($section->description) . "\n\n\medskip";
                }
            }
            $output .= ($columns > 1 ? "\\begin{multicols}{"."$columns}\n" : "")
                . ($this->quizz->amcparams->shuffleq ? sprintf("\\shufflegroup{%s}\n", $name) : '')
                . sprintf("\\insertgroup{%s}\n", $name)
                . ($columns > 1 ? "\\end{multicols}\n" : "");
        }
        if ($this->quizz->amcparams->separatesheet) {
            // colums: empirical guess, should be in config?
            if (empty($this->quizz->amcparams->answerSheetColumns)) {
                $columns = $this->quizz->questions->count() > 22 ? 2 : 0;
            } else {
                $count = $this->quizz->amcparams->answerSheetColumns;
                if ($count == 1) {
                    $columns = 0;
                } else {
                    $columns = $count;
                }
            }
            $output .= "\\AMCcleardoublepage\n\AMCformBegin\n"
                . "\\answersheet\n"
                . $this->getStudentBlock()
                . ($columns > 1 ? "\\begin{multicols}{"."$columns}\\raggedcolumns\n" : "")
                . "\\AMCform\n"
                . ($columns > 1 ? "\\end{multicols}\n" : "")
                . "\\clearpage\n";
        }
        $output .= "\\end{examcopy}\n"
                . "\\end{document}\n";
        return $output;
    }

    protected function getStudentBlock() {
        $namefield = '
\namefield{
    \fbox{
        \begin{minipage}{.9\linewidth}
            '. ($this->quizz->amcparams->lname ? $this->htmlToLatex($this->quizz->amcparams->lname) . '\\\\[3ex]' : '') . '
            \null\dotfill\\\\[2.5ex]
            \null\dotfill\vspace*{3mm}
        \end{minipage}
    }
}
';
        if ($this->codelength) {
            $tex = '
{
    \setlength{\parindent}{0pt}
    \begin{multicols}{2}
        \raggedcolumns
        \AMCcode{student.number}{' . $this->codelength . '}

        \columnbreak
        $\longleftarrow{}$\hspace{0pt plus 1cm}' . $this->htmlToLatex($this->quizz->amcparams->lstudent) . '\\\\[3ex]
        \hfill{}' . $namefield . '\hfill\\\\
    \end{multicols}
}
';
        } else {
            $tex = '
\begin{minipage}{.47\linewidth}
' . $namefield . '
\end{minipage}
';
        }
        return $tex . "\n\\hrule\n\n";
    }

    /**
     *
     * @param string $html UTF-8 HTML string
     * @return string
     */
    protected function htmlToLatex($html) {
        $converter = new \HtmlToTex();
        $converter->setTmpDir($this->tmpDir);
        if (strpos($html, '<tex') !== false) {
            $filtered = preg_replace('#<tex\b(.*)>(.+?)</tex>#', '<code class="tex"\1>\(\2\)</code>', $html);
        } else if (strpos($html, '[[') !== false) {
            $filtered = str_replace(['[[', ']]'], ['<code class="tex">', '</code>'], $html);
        } else if (strpos($html, '$$') !== false) {
            $filtered = preg_replace('/\$\$(.+?)\$\$/', '<code class="tex">\(\1\)</code>', $html);
        } else {
            $filtered = $html;
        }
        return $converter->loadFragment($filtered)->toTex();
    }

    static private $questionCounter = 0;
    static private $sectionCounter = 0;

    /**
     * @param string $text
     * @param boolean $isSection
     * @return string
     */
    static protected function normalizeIntoUnique($text, $isSection=false) {
        if ($isSection) {
            self::$sectionCounter++;
            $append = "P" . self::$sectionCounter;
        } else {
            self::$questionCounter++;
            $append = "Q" . self::$questionCounter;
        }
        return preg_replace('/[^a-zA-Z]+/', '',
                @iconv('UTF-8', 'ASCII//TRANSLIT',
                        substr( html_entity_decode(strip_tags($text)), 0, 30 )
        )) . "-" . $append;
    }
}
