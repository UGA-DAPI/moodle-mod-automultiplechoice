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
        $scoringSet = mod\automultiplechoice\ScoringSystem::read()->getScoringSet($quizz->amcparams->scoringset);
        $select = mod\automultiplechoice\ScoringSystem::read()->toHtmlSelect('amc[scoringset]', $quizz->amcparams->scoringset);
        echo '<form action="questions.php" method="post" name="qselect">
        <input name="a" value="' . $quizz->id . '" type="hidden" />';

        echo '<table class="flexible generaltable quizz-summary" id="params-quizz"><tbody>';
        echo '<tr><th>' . get_string('score', 'automultiplechoice') . '</th>'
            . '<td><input type="text" id="expected-total-score" class="qscore" name="score" value='
            . $quizz->score . ' /></td></tr>';
        echo '<tr><th>' . get_string('scoringset', 'automultiplechoice') . '</th>';
        echo '<td>' . $select . '<div id="scoringset_desc"></div></td></tr>';
        echo '</tbody></table>';
        echo '<table class="flexible boxaligncenter generaltable" id="questions-selected">';
        echo '<thead><tr><th>#</th>'
                . '<th>' . get_string('qscore', 'automultiplechoice')
                . '</th><th>' . get_string('qtitle', 'automultiplechoice')
                . '<div><button type="button" id="toggle-answers">Afficher/masquer les réponses</button></div>'
                . '</th></tr></thead>';
        echo '<tbody>';

        $k = 1;
        $disabled = $quizz->isLocked() ? ' disabled="disabled"' : '';
        foreach ($quizz->questions as $q) {
            echo '<tr>';
            if ($q->getType() === 'section') {
                echo '<td colspan="3">' . htmlspecialchars($q->name)
                    . '<div class="question-answers">' . format_text($q->description, FORMAT_HTML) . '</div>'
                    . '<input name="question[id][]" value="' . htmlspecialchars($q->name) . '" type="hidden" />'
                    . '<input name="question[description][]" value="' . htmlspecialchars($q->description) . '" type="hidden" />';
            } else {
                echo '<td>' . $k . '</td>
                    <td class="q-score">
                        <input name="question[score][]" type="text" class="qscore" value="' . $q->score . '" '
                        . $disabled . ' />
                    </td>
                    <td><div><b>' . format_string($q->name) . '</b></div><div>'. format_string($q->questiontext) . '</div>'
                        . HtmlHelper::listAnswers($q);
                $k++;
            }
            echo $q->htmlHiddenFields((boolean) $disabled);
            echo "</td>\n</tr>\n";
        }
        echo '<tr>'
            . '<td></td>'
            . '<th><span id="computed-total-score">' . $quizz->score . '</span> / '
            . '<span id="total-score">' . $quizz->score . '</span></th>'
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
