<?php

/**
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

require_once __DIR__ . '/Quizz.php';

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
        echo '<thead><tr><th>' . get_string('qscore', 'automultiplechoice')
                . '</th><th>' . get_string('qtitle', 'automultiplechoice') . '</th></tr></thead>';
        echo '<tbody>';

        foreach ($quizz->questions->getRecords() as $q) {
            echo '<tr>
                <td class="q-score">
                    <input name="question[id][]" value="' . $q->id . '" type="hidden" />
                    <label class="qscore">' . get_string('qscore', 'automultiplechoice') . ' :
                        <input name="question[score][]" type="text" class="qscore" value="' . $q->score . '" />
                    </label>
                </td>
                <td><div><b>' . format_string($q->name) . '</b></div>'. format_string($q->questiontext) . '</td>
            </tr>';
        }
        echo '<tr>'
            . '<th><span id="computed-total-score">' . $quizz->score . '</span> / ' . $quizz->score . '</th>'
            . '<td><button type="submit">' . get_string('savechanges') . '</button></td></tr>';
        echo '</tbody></table>';
        echo "</form>\n";
    }

    public static function printTableQuizz(\mod\automultiplechoice\Quizz $quizz)
    {
        $realQNumber = $quizz->questions->count();
        $scoringSet = mod\automultiplechoice\ScoringSystem::read()->getScoringSet($quizz->amcparams->scoringset);
        echo '<table class="flexible boxaligncenter generaltable">';
        echo '<tbody>';
        echo '<tr><th>' . get_string('description', 'automultiplechoice') . '</th><td>' . nl2br(format_string($quizz->description)) . '</td></tr>';
        echo '<tr><th>' . get_string('comment', 'automultiplechoice') . '</th><td>' . format_string($quizz->comment) . '</td></tr>';
        echo '<tr><th>' . get_string('qnumber', 'automultiplechoice') . '</th><td>'
                . ($quizz->qnumber == $realQNumber ? $quizz->qnumber : $realQNumber . " / " . $quizz->qnumber)
                . '</td></tr>';
        echo '<tr><th>' . get_string('score', 'automultiplechoice') . '</th><td id="expected-total-score">' . $quizz->score . '</td></tr>';
        echo '<tr><th>' . get_string('scoringset', 'automultiplechoice') . '</th><td>'
                . '<div><strong>' . format_string($scoringSet->name) . '</strong></div>'
                . '<div>' . nl2br(format_string($scoringSet->description)) . '</div>'
                . '</td></tr>';
        echo '</tbody></table>';
    }
}
