<?php
/**
 * @package    mod_automultiplechoice
 * @copyright  2013-2014 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Description of HtmlHelper
 *
 * @author François Gannaz <francois.gannaz@silecs.info>
 */
class HtmlHelper {
    /**
     *
     * @param string $buttonText
     * @param integer $quizzid
     * @param string $targetpage
     * @param string $action
     * @param string $checks (opt)
     * @return string HTML
     */
    public static function buttonWithAjaxCheck($buttonText, $quizzid, $targetpage, $action, $checks = "") {
        $checklock = json_encode(array('a' => $quizzid, 'actions' => $checks));
        $button = '<form action="' . htmlspecialchars(new moodle_url("/mod/automultiplechoice/$targetpage.php", array('a' => $quizzid)))
            . '" method="post" '
            . ($checks ? 'class="checklock" data-checklock="' . htmlspecialchars($checklock) . '">' : '>') . '
        <p>
            <input type="hidden" name="action" value="%s" />
            <button type="submit">%s</button>
        </p>
        </form>';
        return sprintf($button, htmlspecialchars($action), $buttonText);
    }

    public static function printFormFullQuestions(\mod\automultiplechoice\Quizz $quizz) {
        //$scoringSet = mod\automultiplechoice\ScoringSystem::read()->getScoringSet($quizz->amcparams->scoringset);
        require __DIR__ . '/_scoring_form.php';
    }

    public static function printTableQuizz(\mod\automultiplechoice\Quizz $quizz, $rows = array('instructions', 'description', 'comment', 'qnumber', 'score', 'scoringset'))
    {
        $realQNumber = $quizz->questions->count();
        $scoringSet = mod\automultiplechoice\ScoringSystem::read()->getScoringSet($quizz->amcparams->scoringset);
        echo '<table class="flexible generaltable quizz-summary">';
        echo '<tbody>';
        $rowCount = 0;
        foreach ($rows as $row) {
            $rowCount++;
            $tr = '<tr class="r' . ($rowCount % 2) . '"><th>';
            switch ($row) {
                case 'instructions':
                    echo $tr . get_string('instructions', 'automultiplechoice') . '</th>'
                        . '<td>' . format_text($quizz->amcparams->instructionsprefix, $quizz->amcparams->instructionsprefixformat) . '</td></tr>';
                    break;
                case 'description':
                    echo $tr . get_string('description', 'automultiplechoice') . '</th>'
                        . '<td>' . format_text($quizz->description, $quizz->descriptionformat) . '</td></tr>';
                    break;
                case 'comment':
                    if ($quizz->comment) {
                        echo $tr . get_string('comment', 'automultiplechoice') . '</th><td>' . format_string($quizz->comment) . '</td></tr>';
                    } else {
                        $rowCount--;
                    }
                    break;
                case 'qnumber':
                    echo $tr . get_string('qnumber', 'automultiplechoice') . '</th><td>'
                            . ($quizz->qnumber == $realQNumber ? $quizz->qnumber : "<span class=\"score-mismatch\">$realQNumber / {$quizz->qnumber}</span>")
                            . '</td></tr>';
                    break;
                case 'score':
                    echo $tr . get_string('score', 'automultiplechoice') . '</th><td id="expected-total-score">' . $quizz->score . '</td></tr>';
                    break;
                case 'scoringset':
                    echo $tr . get_string('scoringset', 'automultiplechoice') . '</th><td>'
                            . '<div><strong>' . format_string($scoringSet->name) . '</strong></div>'
                            . '<div>' . nl2br(format_string($scoringSet->description)) . '</div>'
                            . '</td></tr>';
                    break;
                default:
                    if (property_exists($quizz, $row)) {
                        echo $tr . get_string($row, 'automultiplechoice') . '</th><td>' . $quizz->$row. '</td></tr>';
                    } else if (property_exists($quizz->amcparams, $row)) {
                        echo $tr . get_string('amc_' . $row, 'automultiplechoice') . '</th><td>' . $quizz->amcparams->$row. '</td></tr>';
                    } else {
                        throw new Exception("Coding error, unknown row $row.");
                    }
            }
        }
        echo '</tbody></table>';
    }

    protected static function listAnswers($question) {
        global $DB;
        $answers = $DB->get_recordset('question_answers', array('question' => $question->id));
        $html = '<div class="question-answers"><ul>';
        foreach ($answers as $answer) {
            $html .= '<li class="answer-' . ($answer->fraction > 0 ? 'right' : 'wrong') . '">'
                    . format_string($answer->answer) . "</li>\n";
        }
        $html .= "</ul></div>\n";
        return $html;
    }
}
