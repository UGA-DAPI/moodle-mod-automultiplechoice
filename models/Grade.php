<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

require_once __DIR__ . '/AmcProcessGrade.php';
require_once __DIR__ . '/Log.php';

class Grade extends AmcProcessGrade
{
    private $results;

    public function __construct(Quizz $quizz, $formatName = 'latex') {
        parent::__construct($quizz, $formatName);
        $this->results = (object) array(
            'errors' => new \stdClass(),
        );
    }

    /**
     * @return boolean
     */
    public function grade() {
        $actions = array(
            'scoringset' => (boolean) $this->amcPrepareBareme(),
            'scoring' => (boolean) $this->amcNote(),
            'export' => (boolean) $this->amcExport(),
            'csv' => (boolean) $this->writeFileWithIdentifiedStudents(),
        );
        $this->results = (object) array(
            'actions' => (object) $actions,
            'users' => (object) array(
                'known' => $this->usersknown,
                'unknown' => $this->usersunknown,
            ),
            'csv' => (object) array(
                'grades.csv' => $this->getFileUrl(AmcProcessGrade::PATH_AMC_CSV),
                'grades_with_names.csv' => $this->getFileUrl(AmcProcessGrade::PATH_FULL_CSV),

            ),
        );
        return (array_sum($actions) === count($actions));
    }

    /**
     *
     * @global moodle_database $DB
     * @param int $userid update grade of specific user only, 0 means all participants
     * @return boolean
     */
    public function anotate() {
        global $DB;
        $this->results->actions->anotate = $this->amcAnnotePdf();
        if (!$this->results->actions->anotate) {
            return false;
        }
        $grades = $this->getMarks();
        $record = $DB->get_record('automultiplechoice', array('id' => $this->quizz->id), '*');
        \automultiplechoice_grade_item_update($record, $grades);
        return true;
    }

    /**
     * @return StdClass
     */
    public function getResults() {
        return $this->results;
    }

    /**
     * @global core_renderer $OUTPUT
     * @return string
     */
    public function getHtml() {
        global $OUTPUT;
        $html = '';

        // error messages
        $errorMsg = array(
            'scoringset' => "Erreur lors de l'extraction du barème",
            'scoring' => "Erreur lors du calcul des notes",
            'export' => "Erreur lors de l'export CSV des notes",
            'csv' => "Erreur lors de la création du fichier CSV des notes",
            'anotate' => "Erreur lors de l'annotation des copies",
        );
        foreach ($this->results->actions as $k => $v) {
            if (!$v) {
                $html .= $OUTPUT->box($errorMsg[$k], 'errorbox');
            }
        }

        $html .= $OUTPUT->heading("Bilan des notes")
            . $this->computeStats()
            . "<p>Si le résultat de la notation ne vous convient pas, vous pouvez modifier le barème puis relancer la correction.</p>";

        $html .= $OUTPUT->heading("Tableaux des notes")
            . "<p>" . $this->results->users->known . " copies identifiées et " . $this->results->users->unknown . " non identifiées. </p>"
            . '<ul class="amc-files">';
        foreach ((array) $this->results->csv as $name => $url) {
            $html .= "<li>" . \html_writer::link($url, $name) . "</li>";
        }
        $html .= "</ul>\n";

        if (!empty($this->results->actions->anotate)) {
            $url = $this->getFileUrl('cr/corrections/pdf/' . $this->normalizeFilename('corrections'));
            $html .= $OUTPUT->heading("Copies corrigées")
                . \html_writer::link($url, $this->normalizeFilename('corrections'), array('target' => '_blank'))
                . "\n";
        }

        return $html;
    }
}
