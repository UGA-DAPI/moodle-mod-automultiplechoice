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
    public static function buttonWithAjaxCheck($buttonText, $quizzid, $targetpage, $action, $checks = "") 
    {
        $checklock = json_encode(
            array(
                'a' => $quizzid, 
                'actions' => $checks
            )
        );

        // create action
        $formAction = htmlspecialchars(
            new moodle_url(
                "/mod/automultiplechoice/$targetpage.php", 
                array('a' => $quizzid)
            )
        );

        // create form
        $form = '<form action="' . $formAction . '" method="POST" ';
        if ($checks) {
            $form .= 'class="checklock"';
            $form .= 'data-checklock="' . htmlspecialchars($checklock) . '"';
        }
        $form .= '>';
        $form .= '  <div class="form-field">';
        $form .= '      <input type="hidden" name="action" value="%s" />';
        $form .= '      <button class="btn btn-default" type="submit">%s</button>';
        $form .= '  </div>';
        $form .= '</form>';
        
        return sprintf($form, htmlspecialchars($action), $buttonText);
    }

    public static function printFormFullQuestions(\mod\automultiplechoice\Quizz $quizz) 
    {
        // populate and output the scoring form
        // $quizz is used in _scoring_form.php so it's needed
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

    /**
     * Create a modal
     * 
     * @return the generated HTML
     */
    public static function generateAmcModal()  
    {
        $modalTitle = get_string('amcmodaltitle', 'automultiplechoice');
        $save = get_string('amcmodalsave', 'automultiplechoice');
        $cancel = get_string('amcmodalcancel', 'automultiplechoice');

        $modal = '<div class="modal" id="amcModal" tabindex="-1" role="dialog">';
        $modal .= '  <div class="modal-dialog" role="document">';
        $modal .= '     <div class="modal-content">';
        $modal .= '         <div class="modal-header">';
        $modal .= '             <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        $modal .= '             <h4 class="modal-title">'.$modalTitle.'</h4>';
        $modal .= '         </div>';
        $modal .= '         <div class="modal-body">';
        $modal .= '             <p>One fine body&hellip;</p>';
        $modal .= '         </div>';
        $modal .= '         <div class="modal-footer">';
        $modal .= '             <button type="button" class="btn btn-default" data-dismiss="modal">'.$cancel.'</button>';
        $modal .= '             <button type="button" class="btn btn-primary">'.$save.'</button>';
        $modal .= '         </div>';
        $modal .= '     </div>';
        $modal .= '   </div>';
        $modal .= '</div>';
        echo $modal;
    }
}
