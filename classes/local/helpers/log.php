<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2014 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_automultiplechoice\local\helpers;
//require_once __DIR__ . '/Quizz.php';
/**
 * Log the last action for each activity instance.
 *
 * @author François Gannaz <francois.gannaz@silecs.info>
 */
class log {
    /**
     * @var int Instance ID.
     */
    protected $instanceId;
    /**
     * Constructor.
     *
     * @param int $instanceId
     * @throws Exception
     */
    public function __construct($instanceId) {
        $this->instanceId = (int) $instanceId;
        if ($this->instanceId <= 0) {
            throw new \Exception("Coding error, invalid instance ID.");
        }
    }
    /**
     * Constructor.
     *
     * @param int $instanceId
     * @throws Exception
     */
    static public function build($instanceId) {
        return new self($instanceId);
    }
    /**
     * Read the timestamp of the last action of this name.
     *
     * @global \moodle_database $DB
     * @param string $action
     * @return int
     */
    public function read($action) {
        global $DB;
        $this->isValidAction($action);
        $raw = $DB->get_field("automultiplechoice_log", 'actiontime', array('instanceid' => $this->instanceId, 'action' => $action), IGNORE_MISSING);
        return (int) $raw;
    }
    /**
     * Write the timestamp of this action.
     *
     * @global \moodle_database $DB
     * @param string $action
     * @param int $timestamp (opt) If not set, current timestamp.
     */
    public function write($action, $timestamp=null) {
        global $DB;
        $this->isValidAction($action);
        if ($timestamp === null) {
            $timestamp = $_SERVER['REQUEST_TIME'];
        }
        $record = array(
            'instanceid' => $this->instanceId,
            'action' => $action,
            'actiontime' => $timestamp,
        );
        $id = $DB->get_field("automultiplechoice_log", 'id', array('instanceid' => $this->instanceId, 'action' => $action));
        if ($id > 0) {
            $record['id'] = (int) $id;
            $DB->update_record("automultiplechoice_log", $record);
        } else {
            $DB->insert_record("automultiplechoice_log", $record, false);
        }
    }
    /**
     * @param string|array $actions Array of values among "process", "pdf", "grading", "upload", "unlock".
     * @return array
     * @throws Exception
     */
    public function check($actions)
    {
        if (is_string($actions)) {
            $actions = array($actions);
        }
        $messages = array();
        foreach ($actions as $action) {
            switch ($action) {
                case 'process':
                    $process = $this->read('process');
                    if ($process) {
                        $minutes = (int) \round(($_SERVER['REQUEST_TIME'] - $process) / 60);
                        $messages[] = get_string('log_process_running', 'mod_automultiplechoice', ['time' => $minutes]);
                    }
                    break;
                case 'pdf':
                    $pdf = $this->read('pdf');
                    if (!$pdf) {
                        return [];
                    }
                    if ($this->read('scoring') > $pdf) {
                        $messages[] = get_string('log_scoring_edited', 'mod_automultiplechoice');
                    }
                    if ($this->read('saving') > $pdf) {
                        $messages[] = get_string('log_questions_changed', 'mod_automultiplechoice');
                    }
                    break;
                case 'corrected':
                    $corrected = $this->read('corrected');
                    if (!$corrected) {
                        return [];
                    }
                    if ($this->read('saving') > $corrected) {
                        $messages[] = get_string('log_questions_changed', 'mod_automultiplechoice');
                    }
                    break;
                case 'meptex':
                    $meptex = $this->read('meptex');
                    if (!$meptex) {
                        return [];
                    }
                    if ($this->read('pdf') > $meptex) {
                        $messages[] = get_string('log_pdf_changed_since_last_analyse', 'mod_automultiplechoice');
                    }
                    break;
                case 'upload':
                    $upload = $this->read('upload');
                    if (!$upload) {
                        return [];
                    }
                    if ($this->read('pdf') > $upload) {
                        $messages[] = get_string('log_pdf_changed_since_last_upload', 'mod_automultiplechoice');
                    }
                    if ($this->read('lock') > $upload) {
                        $messages[] = get_string('log_last_lock_after_last_upload', 'mod_automultiplechoice');
                    }
                    if ($this->read('meptex') > $upload) {
                        $messages[] = get_string('log_last_analyse_after_last_upload', 'mod_automultiplechoice');
                    }
                    break;
                case 'grading':
                    $grading = $this->read('grading');
                    if (!$grading) {
                        return [];
                    }
                    if ($this->read('upload') > $grading) {
                        $messages[] = get_string('log_relaunch_correction_uploads', 'mod_automultiplechoice');
                    }
                    if ($this->read('scoring') > $grading) {
                        $messages[] = get_string('log_relaunch_correction_scale', 'mod_automultiplechoice');
                    }
                    break;
                case 'associating':
                    $associating = $this->read('associating');
                    if (!$associating) {
                        return [];
                    }
                    if ($this->read('upload') > $associating) {
                        $messages[] = get_string('log_relaunch_association_uploads', 'mod_automultiplechoice');
                    }
                    if ($this->read('grading') > $associating) {
                        $messages[] = get_string('log_relaunch_association_grading', 'mod_automultiplechoice');
                    }
                    if (!$this->read('grading')) {
                        $messages[] = get_string('log_sheets_no_grading', 'mod_automultiplechoice');
                    }
                    break;
                case 'exporting':
                    $exporting = $this->read('exporting');
                    if (!$exporting) {
                        return [];
                    }
                    if ($this->read('grading') > $exporting) {
                        $messages[] = get_string('log_relaunch_export_grading', 'mod_automultiplechoice');
                    }
                    break;
                case 'annotating':
                    $annotating = $this->read('annotating');
                    if (!$annotating) {
                        return [];
                    }
                    if ($this->read('grading') > $annotating) {
                        $messages[] = get_string('log_relaunch_annotation_grading', 'mod_automultiplechoice');
                    }
                    break;
                case 'annotatePdf':
                    $annotatePdf = $this->read('annotatePdf');
                    if (!$annotatePdf) {
                        return [];
                    }
                    if ($this->read('annotating') > $annotatePdf) {
                        $messages[] = get_string('log_relaunch_annotate_annotating', 'mod_automultiplechoice');
                    }
                    break;
                case 'unlock':
                    $quizrecord = \mod_automultiplechoice\local\models\quiz::findById($this->instanceId);
                    $quiz =  \mod_automultiplechoice\local\models\quiz::readFromRecord($quizrecord);
                    if ($quiz->hasScans()) {
                        $messages[] = get_string('log_unlock_uploads_exists', 'mod_automultiplechoice');
                    }
                    break;
                default:
                    throw new \Exception("Unknown parameter '$action'.");
            }
        }
        return $messages;
    }
    /**
     * @param string $action
     * @return boolean
     * @throws \Exception
     */
    private function isValidAction($action) {
        $valid = array(
            'process',
            'pdf',
            'meptex',
            'saving',
            'scoring',
            'upload',
            'grading',
            'associating',
            'exporting',
            'annotating',
            'annotatePdf',
            'corrected',
            'lock',
            'unlock',
            'manualassoc'
        );
        if (!in_array($action, $valid)) {
            throw new \Exception("The action $action is not valid.");
        }
        return true;
    }
}
