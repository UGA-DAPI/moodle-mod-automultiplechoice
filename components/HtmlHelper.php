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
    public static function printFormFullQuestions(\mod\automultiplechoice\Quizz $quizz) {
        echo '<form action="qselect.php" method="post" name="qselect">
        <input name="a" value="' . $quizz->id . '" type="hidden" />';
        echo '<table class="flexible boxaligncenter generaltable" id="questions-selected">';
        echo '<thead><tr><th>#</th>'
                . '<th>' . get_string('qscore', 'automultiplechoice')
                . '</th><th>' . get_string('qtitle', 'automultiplechoice')
                . '<div><button type="button" id="toggle-answers">Afficher/masquer les réponses</button></div>'
                . '</th></tr></thead>';
        echo '<tbody>';

        $k = 1;
        $disabled = $quizz->isLocked() ? ' disabled="disabled"' : '';
        foreach ($quizz->questions->getRecords(null, true) as $q) {
            if (is_string($q)) {
                echo '<tr>
                    <td colspan="3">' . htmlspecialchars($q) . '</td>
                </tr>';
            } else {
                echo '<tr>
                    <td>' . $k . '</td>
                    <td class="q-score">
                        <input name="question[id][]" value="' . $q->id . '" type="hidden" />
                        <input name="question[score][]" type="text" class="qscore" value="' . $q->score . '" '
                        . $disabled . ' />
                    </td>
                    <td><div><b>' . format_string($q->name) . '</b></div><div>'. format_string($q->questiontext) . '</div>'
                        . HtmlHelper::listAnswers($q)
                        .'</td>
                </tr>';
                $k++;
            }
        }
        echo '<tr>'
            . '<td></td>'
            . '<th><span id="computed-total-score">' . $quizz->score . '</span> / ' . $quizz->score . '</th>'
            . '<td>'
            . ($disabled ? '' : '<button type="submit">' . get_string('savechanges') . '</button>')
            .'</td></tr>';
        echo '</tbody></table>';
        echo "</form>\n";
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
                        . '<td>' . nl2br(format_string($quizz->amcparams->instructionsprefix)) . '</td></tr>';
                    break;
                case 'description':
                    echo $tr . get_string('description', 'automultiplechoice') . '</th>'
                        . '<td>' . nl2br(format_string($quizz->description)) . '</td></tr>';
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
                    throw new Exception("Coding error, unknown row $row.");
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
