<?php

/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2014 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

require_once __DIR__ . '/Quizz.php';

/**
 * Log the last action for each activity instance.
 *
 * @author François Gannaz <francois.gannaz@silecs.info>
 */
class Log {
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
                        $minutes = (int) \round(($_SERVER['REQUEST_TIME'] - $process)/60);
                        $messages[] = "AMC est déjà en cours d'exécution depuis $minutes minutes.";
                    }
                    break;
                case 'pdf':
                    $pdf = $this->read('pdf');
                    if (!$pdf) {
                        return [];
                    }
                    if ($this->read('scoringsystem') > $pdf) {
                        $messages[] = "Le choix du barème a été modifié depuis la dernière préparation des sujets PDF.";
                    }
                    break;
                case 'upload':
                    $upload = $this->read('upload');
                    if (!$upload) {
                        return [];
                    }
                    if ($this->read('pdf') > $upload) {
                        $messages[] = "Le PDF du QCM a été modifié depuis le dernier dépôt des copies.";
                    }
                    if ($this->read('lock') > $upload) {
                        $messages[] = "Le dernier verrouillage du QCM a eu lieu après le dernier dépôt des copies.";
                    }
                    break;
                case 'grading':
                    $grading = $this->read('grading');
                    if (!$grading) {
                        return [];
                    }
                    if ($this->read('upload') > $grading) {
                        $messages[] = "Des copies d'étudiant ont été déposées depuis la dernière notation. Relancer la correction ?";
                    }
                    if ($this->read('scoringsystem') > $grading) {
                        $messages[] = "Le barème a été modifié depuis la dernière notation. Relancer la correction ?";
                    }
                    if ($grading > $this->read('correction')) {
                        $messages[] = "La dernière notation est plus récente que les copies annotées. Re-générer les copies corrigées ?";
                    }
                    break;
                case 'unlock':
                    if (Quizz::findById($this->instanceId)->hasScans()) {
                        $messages[] = "Des copies scannées ont déjà été déposées. En cas de modification du QCM, les copies scannées ne seront plus valables.";
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
        $valid = array('process', 'pdf', 'scoringsystem', 'upload', 'grading', 'correction', 'lock', 'unlock');
        if (!in_array($action, $valid)) {
            throw new \Exception("L'action $action n'est pas valide.");
        }
        return true;
    }
}
