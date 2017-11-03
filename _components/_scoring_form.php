<?php

/* @var $quiz mod_automultiplechoice\local\models\quiz */

?>
<form action="" method="post" name="qselect">
    <input name="a" value="<?= $quiz->id ?>" type="hidden" />
    <input name="qnumber" value="<?= $quiz->qnumber ?>" type="hidden" id="quizz-qnumber"/>

    <table class="flexible generaltable quizz-summary" id="params-quizz">
        <tbody>
            <tr>
                <th><?= get_string('score', 'automultiplechoice') ?></th>
                <td><input id="expected-total-score" type="number" class="form-control qscore" name="score" value="<?= $quiz->score ?>" /></td>
            </tr>
            <tr>
                <th><?= get_string('amc_grademax', 'automultiplechoice') ?></th>
                <td><input type="number" class="form-control" id="amc-grademax" name="amc[grademax]" value="<?= $quiz->amcparams->grademax ?>" /></td>
            </tr>
            <tr>
                <th><?= get_string('amc_gradegranularity', 'automultiplechoice') ?></th>
                <td>
                    <select name="amc[gradegranularity]">
                        <?php
                        foreach (['0.25', '0.5', '1.0'] as $v) {
                            echo '<option ' . ($quiz->amcparams->gradegranularity == $v ? 'selected="selected" ' : '') . '>' . $v . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?= get_string('amc_graderounding', 'automultiplechoice') ?></th>
                <td>
                    <select name="amc[graderounding]">
                        <?php
                        foreach (\mod_automultiplechoice\local\amc\params::$scoreRcoundingValues as $k => $v) {
                            echo '<option value="' . $k . '" ' . ($quiz->amcparams->graderounding == $k ? 'selected="selected" ' : '') . '>' . $v . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?= get_string('scoringset', 'automultiplechoice') ?></th>
                <td>
                    <?= mod\automultiplechoice\ScoringSystem::read()->toHtmlSelect('amc[scoringset]', $quiz->amcparams->scoringset) ?>
                    <div id="scoringset_desc"></div>
                </td>
            </tr>
        </tbody>
    </table>
    <table class="flexible boxaligncenter generaltable" id="questions-selected">
        <thead>
            <tr>
                <th>#</th>
                <th><?= get_string('qscore', 'automultiplechoice') ?></th>
                <th>
                    <?= get_string('qtitle', 'automultiplechoice') ?>
                    <div>
                        <button type="button" role="button" class="btn btn-default" id="toggle-answers">
                            Afficher/masquer les réponses
                        </button>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
            $k = 1;
            $nbline = 1;
            foreach ($quiz->questions as $q) {
                echo '<tr>';
                if ($q->getType() === 'section') {
                    echo '<td colspan="3">' . htmlspecialchars($q->name)
                        . '<div class="question-answers">' . format_text($q->description, FORMAT_HTML) . '</div>'
                        . '<input name="q[score][]" value="" type="hidden" />';
                } else {
                    echo '<td>' . $k . '</td>
                        <td class="q-score">
                            <input name="q[score][]" class="form-control" type="number" class="qscore" value="' . $q->score . '" />
                        </td>
                        <td><div><b>' . format_string($q->name) . '</b></div><div>'. format_string($q->questiontext) . '</div>'
                            . HtmlHelper::listAnswers($q);
                    $k++;
                }
                echo "</td>\n</tr>\n";
                $nbline++;
            }
            if ($nbline%2) {
                echo '<tr></tr>';
            }
            ?>
            <tr>
                <td></td>
                <th>
                    <span id="computed-total-score"><?= $quiz->score ?></span> /
                    <span id="total-score"><?= $quiz->score ?></span>
                </th>
                <td>
                    <button type="button" role="button" class="btn btn-default" id="scoring-distribution">Répartir les points</button>
                </td>
            </tr>
        </tbody>
    </table>

    <div>
        <button type="submit" class="btn btn-default">
            <?= get_string('savechanges') ?>
        </button>
    </div>
</form>
