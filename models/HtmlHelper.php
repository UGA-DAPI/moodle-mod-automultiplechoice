<?php

/**
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

require_once __DIR__ . '/Quizz.php';
require_once __DIR__ . '/Scoring.php';

/**
 * Description of HtmlHelper
 *
 * @author François Gannaz <francois.gannaz@silecs.info>
 */
class HtmlHelper {
    public static function printFormFullQuestions(\mod\automultiplechoice\Quizz $quizz) {
        $scoringSystem = mod\automultiplechoice\ScoringSystem::createFromConfig();

        echo '<form action="qselect.php" method="post" name="qselect">
        <input name="a" value="' . $quizz->id . '" type="hidden" />';
        echo '<table class="flexible boxaligncenter generaltable" id="questions-selected">';
        echo '<thead><tr><th>' . get_string('qscore', 'automultiplechoice')
                . '</th><th>' . get_string('qtitle', 'automultiplechoice') . '</th></tr></thead>';
        echo '<tbody>';
        foreach ($quizz->questions->getRecords() as $q) {
            echo '<tr>
                <td>
                    <input name="question[id][]" value="' . $q->id . '" type="hidden" />'
                    . $scoringSystem->buildHtmlSelect('question[scoring][]', empty($q->single), $q->scoring) . '
                    <label class="qscore">' . get_string('qscore', 'automultiplechoice') . ' :
                        <input name="question[score][]" type="text" class="qscore" value="' . $q->score . '"'
                        . ($q->scoring ? 'readonly="readonly"' : '') . ' />
                    </label>
                </td>
                <td>' . format_string($q->questiontext) . '</td>
            </tr>';
        }
        echo '<tr><th>' . $quizz->score . '</th><td><button type="submit">OK</button></td></tr>';
        echo '</tbody></table>';
        echo "</form>\n";
    }

    public static function printTableQuizz(\mod\automultiplechoice\Quizz $quizz)
    {
        echo '<table class="flexible boxaligncenter generaltable">';
        echo '<tbody>';
        echo '<tr><th>' . get_string('description', 'automultiplechoice') . '</th><td>' . format_string($quizz->description) . '</td></tr>';
        echo '<tr><th>' . get_string('comment', 'automultiplechoice') . '</th><td>' . format_string($quizz->comment) . '</td></tr>';
        echo '<tr><th>' . get_string('qnumber', 'automultiplechoice') . '</th><td>' . $quizz->qnumber . '</td></tr>';
        echo '<tr><th>' . get_string('score', 'automultiplechoice') . '</th><td>' . $quizz->score . '</td></tr>';
        echo '</tbody></table>';
    }
}
