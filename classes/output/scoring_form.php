<?php

namespace mod_automultiplechoice\output;

defined('MOODLE_INTERNAL') || die();

class scoring_form implements \renderable, \templatable {
    /**
     * An array containing page data
     *
     * @var array
     */
    protected $quiz;

    /**
     * Contruct
     *
     * @param array $content An array of renderable headings
     */
    public function __construct($quiz) {
        $this->quiz = $quiz;
    }
    /**
     * Prepare data for use in a template
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        $granularities = [];
        foreach (['0.25', '0.5', '1.0'] as $value) {
            if ($this->quiz->amcparams->gradegranularity === $value) {
                $granularities[] = ['value' => $value, 'label' => $value, 'selected' => true];
            } else {
                $granularities[] = ['value' => $value, 'label' => $value, 'selected' => false];
            }
        }
        $graderoundings = [];
        foreach (\mod_automultiplechoice\local\amc\params::$scoreRcoundingValues as $key => $label) {
            if ($this->quiz->amcparams->graderounding === $key) {
                $graderoundings[] = ['value' => $key, 'label' => $label, 'selected' => true];
            } else {
                $graderoundings[] = ['value' => $key, 'label' => $label, 'selected' => false];
            }
        }
        $scoringsetsbase = \mod_automultiplechoice\local\models\scoring_system::read()->get_sets();
        $scoringsets = [];
        foreach ($scoringsetsbase as $rank => $scoringset) {
            if ($this->quiz->amcparams->scoringset !== '' &&  $this->quiz->amcparams->scoringset === $rank) {
                $scoringsets[] = ['value' => $rank, 'label' => htmlspecialchars($scoringset->name), 'selected' => true];
            } else {
                $scoringsets[] = ['value' => $rank, 'label' => htmlspecialchars($scoringset->name), 'selected' => false];
            }
        }

        $questions = [];
        $index = 1;
        foreach ($this->quiz->questions as $question) {
            $questions[] = [
                'index' => $index,
                'section' => $question->getType() === 'section',
                'name' => $question->getType() === 'section' ? htmlspecialchars($question->name) : format_string($q->name),
                'text' => $question->getType() === 'section' ? '' : format_string($question->questiontext),
                'description' => $question->getType() === 'section' ? format_text($question->description, FORMAT_HTML) : '',
                'score' => $question->getType() === 'section' ? '' : $question->score,
                'answers' => \mod_automultiplechoice\local\models\question::list_answers($question)
            ];

            if ($question->getType() !== 'section') {
                $index++;
            }
            /*if ($q->getType() === 'section') {
                echo '<td colspan="3">' . htmlspecialchars($q->name)
                    . '<div class="question-answers">' . format_text($q->description, FORMAT_HTML) . '</div>'
                    . '<input name="q[score][]" value="" type="hidden" />';
            } else {
                echo '<td>' . $k . '</td>
                    <td class="q-score">
                        <input name="q[score][]" class="form-control" type="number" class="qscore" value="' . $q->score . '" />
                    </td>
                    <td><div><b>' . format_string($q->name) . '</b></div><div>'. format_string($q->questiontext) . '</div>'
                        . \mod_automultiplechoice\local\helpers\html::listAnswers($q);
                $k++;
            }*/

        }


        $content = [
            'quiz' => $this->quiz,
            'granularities' => $granularities,
            'graderoundings' => $graderoundings,
            'scoringsets' => $scoringsets,
            'questions' => $questions
        ];
        return $content;
    }
}