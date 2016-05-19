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
    private $actions;
    private $exportedFiles;

    public function __construct(Quizz $quizz, $formatName = 'latex') {
        parent::__construct($quizz, $formatName);
        $this->actions = new \stdClass();
        if ($this->isGraded()) {
            $this->exportedFiles = (object) array(
                'grades.ods' => $this->getFileUrl(AmcProcessGrade::PATH_AMC_ODS),
                'grades.csv' => $this->getFileUrl(AmcProcessGrade::PATH_AMC_CSV),
                'grades_apogee.csv' => $this->getFileUrl(AmcProcessGrade::PATH_APOGEE_CSV),
            );
        }
    }

    /**
     * @return boolean
     */
    public function grade() {
        $this->actions = array(
            'scoringset' => (boolean) $this->amcPrepareBareme(),
            'scoring' => (boolean) $this->amcNote(),
            'studentlist' =>(boolean) $this->writeFileStudentsList(),
            'export' => (boolean) $this->amcExport(),
	    'csv' => (boolean) $this->writeFileApogeeCsv(),
	    'gradebook' =>(boolean) $this->writeGrades()
        );
        $this->exportedFiles = (object) array(
            'grades.ods' => $this->getFileUrl(AmcProcessGrade::PATH_AMC_ODS),
            'grades.csv' => $this->getFileUrl(AmcProcessGrade::PATH_AMC_CSV),
            'grades_apogee.csv' => $this->getFileUrl(AmcProcessGrade::PATH_APOGEE_CSV),
        );
        return (array_sum($this->actions) === count($this->actions));
    }

    /**
     *
     * @global moodle_database $DB
     * @param int $userid update grade of specific user only, 0 means all participants
     * @return boolean
     */
    public function anotate() {
        global $DB;
        $this->actions->anotate = $this->amcAnnotePdf();
        if (!$this->actions->anotate) {
            return false;
        }
        return true;
    }

    /**
     * @return StdClass
     */
    public function getResults() {
        return (object) array(
            'actions' => $this->actions,
            'csv' => $this->exportedFiles,
        );
    }

    /**
     * @global core_renderer $OUTPUT
     * @return string
     */
    public function getHtmlErrors() {
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
        foreach ($this->actions as $k => $v) {
            if (!$v) {
                $html .= $OUTPUT->box($errorMsg[$k], 'errorbox');
            }
        }
        return $html;
    }

    /**
     * @return string
     */
    public function getHtmlCsvLinks() {
        $html = '<ul class="amc-files">';
        foreach ((array) $this->exportedFiles as $name => $url) {
            $html .= "<li>" . \html_writer::link($url, $name) . "</li>";
        }
        $html .= "</ul>\n";
        return $html;
    }

    /**
     * computes and display statistics indicators
     * @return string html table with statistics indicators
     */
    public function getHtmlStats() {
        $this->readGrades();
        $mark = array();
        foreach ($this->grades as $rawmark) {
            $mark[] = $rawmark->rawgrade;
        }

        $indics = array('size' => 'effectif', 'mean' => 'moyenne', 'median' => 'médiane', 'mode' => 'mode', 'range' => 'intervalle');
        $out = "<table class=\"generaltable\"><tbody>\n";
        foreach ($indics as $indicen => $indicfr) {
            $out .= '<tr><td>' . $indicfr. '</td><td>' . $this->mmmr($mark, $indicen) . '</td></tr>' . "\n";
        }
        $out .= "</tbody></table>\n";
        return $out;
    }
}
