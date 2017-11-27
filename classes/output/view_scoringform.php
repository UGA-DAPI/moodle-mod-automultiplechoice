<?php

namespace mod_automultiplechoice\output;

defined('MOODLE_INTERNAL') || die();

class view_scoringform implements \renderable, \templatable {
    /**
     * The auto multiple choice quiz.
     *
     * @var \mod_automultiplechoice\local\models\quiz
     */
    protected $quiz;

    /**
     * Contruct
     *
     * @param mod_automultiplechoice/local/models/quiz $quiz A quiz
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
        foreach ([0.25, 0.5, 1.0] as $value) {
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
            if (is_numeric($this->quiz->amcparams->scoringset) &&  intval($this->quiz->amcparams->scoringset) === $rank) {
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
