<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

require_once __DIR__ . '/AmcProcess.php';
require_once dirname(__DIR__) . '/locallib.php';
require_once __DIR__ . '/Log.php';
require_once __DIR__ . '/AmcFormat/Api.php';


class AmcProcessGrade extends AmcProcess
{
    
    const CSV_SEPARATOR = ';';

    protected $grades = array();
    public $usersknown = 0;
    public $usersunknown = 0;

    protected $format;
    private $actions;
    

        /**
     * Constructor
     *
     * @param Quizz $quizz
     * @param string $formatName "txt" | "latex"
     */
    public function __construct(Quizz $quizz, $formatName = 'latex') {
        parent::__construct($quizz);
        $this->format = amcFormat\buildFormat($formatName, $quizz);
        if (!$this->format) {
            throw new \Exception("Erreur, pas de format de QCM pour AMC.");
        }
        $this->format->quizz = $this->quizz;
        $this->format->codelength = $this->codelength;
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
            'scoring' => "Erreur lors du calcul des notes",
            'export' => "Erreur lors de l'export CSV des notes",
            'csv' => "Erreur lors de la création du fichier CSV des notes",
        );
        foreach ($this->actions as $k => $v) {
            if (!$v) {
                $html .= $OUTPUT->box($errorMsg[$k], 'errorbox');
            }
        }
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



    /**
     * Fills the "grades" property from the CSV.
     *
     * @return boolean
     */
    protected function readGrades() {

        if (count($this->grades) > 0) {
            return true;
        }
        $input = $this->fopenRead($this->workdir . self::PATH_AMC_CSV);
        if (!$input) {
            return false;
        }
        $header = fgetcsv($input, 0, self::CSV_SEPARATOR);
        if (!$header) {
            return false;
        }
        $getCol = array_flip($header);
 
    $this->grades = array();
        while (($data = fgetcsv($input, 0, self::CSV_SEPARATOR)) !== FALSE) {
            $idnumber = $data[$getCol['student.number']];
        $userid=null;
        $userid = $data[$getCol['moodleid']];
        if ($userid) {
            $this->usersknown++;
        } else {
            $this->usersunknown++;
        }
        $this->grades[] = (object) array(
        'userid' => $userid,
                'rawgrade' => str_replace(',', '.', $data[6])
    );
        }
    fclose($input);
        return true;
    }

    
    protected function writeGrades(){

    global $DB;
    $grades = $this->getMarks();
    $record = $DB->get_record('automultiplechoice', array('id' => $this->quizz->id), '*');
    \automultiplechoice_grade_item_update($record, $grades);
    return true;
    } 
    
    /**
     * returns an array to fill the Moodle grade system from the raw marks .
     *
     * @return array grades
     */
    public function getMarks() {
        $this->readGrades();
        $namedGrades = array();
        foreach ($this->grades as $grade) {
            if ($grade->userid) {
                $namedGrades[$grade->userid] = (object) array(
                    'id' => $grade->userid,
                    'userid' => $grade->userid,
                    'rawgrade' => $grade->rawgrade,
                );
            }
        }
        return $namedGrades;
    }

    /**
     * @return boolean
     */
    public function isGraded() {
        if (amc\Log::build($this->quizz->id)->read('grading')){
            return true;
        }else{
             return false;
        }
    }

    /**
     * Computes several statistics indicators from an array
     *
     * @param array $array
     * @param string $output
     * @return float
     */
    protected function mmmr($array, $output = 'mean'){
        if (empty($array) || !is_array($array)) {
            return FALSE;
        } else {
            switch($output){
                case 'size':
                    $res = count($array);
                break;
                case 'mean':
                    $count = count($array);
                    $sum = array_sum($array);
                    $res = $sum / $count;
                break;
                case 'median':
                    rsort($array);
                    $middle = round(count($array) / 2);
                    $res = $array[$middle-1];
                break;
                case 'mode':
                    $v = array_count_values($array);
                    arsort($v);
                    list ($res) = each($v); // read the first key
                break;
                case 'range':
                    sort($array, SORT_NUMERIC);
                    $res = $array[0] . " - " . $array[count($array) - 1];
                break;
            }
            return $res;
        }
    }

    private static function fopenRead($filename) {
        if (!is_readable($filename)) {
            return false;
        }
        $handle = fopen($filename, 'r');
        if (!$handle) {
            return false;
        }
        return $handle;
    }


}
