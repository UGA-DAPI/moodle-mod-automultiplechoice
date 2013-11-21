<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

class AmcProcess
{
    /**
     * @var Quizz Contains notably an 'amcparams' attribute.
     */
    protected $quizz;

    protected $codelength = 0;

    public $workdir;

    protected $relworkdir;

    /**
     * @var array
     */
    protected $errors = array();

    /**
     * Constructor
     *
     * @param Quizz $quizz
     */
    public function __construct($quizz) {
        global $CFG;
        $this->quizz = $quizz;

        $dir = sprintf('automultiplechoice_%05d', $this->quizz->id);
        $this->workdir = $CFG->dataroot . '/local/automultiplechoice/' . $dir;
        $this->relworkdir = '/local/automultiplechoice/' . $dir;

        $this->codelength = (int) get_config('mod_automultiplechoice', 'amccodelength');
        /**
         * @todo error if codelength == 0
         */
    }

    /**
     * Return the errors of the last command.
     * @return array
     */
    public function getLastErrors() {
        return $this->errors;
    }

    /**
     * Shell-executes 'amc meptex'
     * @return bool
     */
    public function amcMeptex() {
        $pre = $this->workdir;
        $res = $this->shellExec(
                'auto-multiple-choice meptex',
                array(
                    '--data', $pre . '/data',
                    '--progression-id', 'MEP',
                    '--progression', '1',
                    '--src', $pre . '/prepare-calage.xy',
                )
        );
        if ($res) {
            $this->log('meptex', '');
        }
        return $res;
    }

    /**
     * Shell-executes 'amc getimages'
     * @param string $scanfile name, uploaded by the user
     * @return bool
     */
    public function amcGetimages($scanfile) {
        $pre = $this->workdir;
        $scanlist = $pre . '/scanlist';
        if (file_exists($scanlist)) {
            unlink($scanlist);
        }
        $mask = $pre . "/scans/*.ppm"; // delete all previous ppm files
        array_map('unlink', glob( $mask ));

        $res = $this->shellExec('auto-multiple-choice getimages', array(
            '--progression-id', 'analyse',
            '--vector-density', '250',
            '--orientation', 'portrait',
            '--list', $scanlist,
            '--copy-to', $pre . '/scans/',
            $scanfile
            ), true);
        if ($res) {
            $nscans = count(file($scanlist));
            $this->log('getimages', $nscans . ' pages');
            return $nscans;
        }
        return $res;
    }

    /**
     * returns stat() information (number and dates) on scanned (ppm) files already stored
     * @return string diagnostic message
     */
    public function statScans() {
        $ppmfiles = glob($this->workdir . '/scans/*.ppm');
        $tsmax = 0;
        $tsmin = PHP_INT_MAX;
        foreach ($ppmfiles as $file) {
            $filedata = stat($file);
            if ( $filedata['mtime'] > $tsmax) {
                $tsmax = $filedata['mtime'];
            }
            if ( $filedata['mtime'] < $tsmin) {
                $tsmin = $filedata['mtime'];
            }
        }
        if ( $ppmfiles ) {
            // return count($ppmfiles) . "copies scannées déposées entre " . $this->isoDate($tsmin) . " et " . $this->isoDate($tsmax) ;
            return count($ppmfiles) . " copies scannées déposées le " . self::isoDate($tsmax) ;
        } else {
            return 'Aucune copie scannée.';
        }
    }

    static function isoDate($timestamp) {
        return date('Y-m-d à H:i:s', $timestamp);
    }

    /**
     * Shell-executes 'amc analyse'
     * @param bool $multiple (see AMC) if multiple copies of the same sheet are possible
     * @return bool
     */
    public function amcAnalyse($multiple = true) {
        $pre = $this->workdir;
        $scanlist = $pre . '/scanlist';
        $parammultiple = '--' . ($multiple ? '' : 'no-') . 'multiple';
        $parameters = array(
            $parammultiple,
            '--tol-marque', '0.2,0.2',
            '--prop', '0.8',
            '--bw-threshold', '0.6',
            '--progression-id' , 'analyse',
            '--progression', '1',
            '--n-procs', '0',
            '--data', $pre . '/data',
            '--projet', $pre,
            '--cr', $pre . '/cr',
            '--liste-fichiers', $scanlist,
            '--no-ignore-red',
            );
        //echo "\n<br> auto-multiple-choice analyse " . join (' ', $parameters) . "\n<br>";
        $res = $this->shellExec('auto-multiple-choice analyse', $parameters, true);
        if ($res) {
            $this->log('analyse', 'OK.');
        }
        return $res;
    }

    
    /**
     * log processed action
     * @param string $action ('prepare'...)
     * @param string $msg
     */
    public function log($action, $msg) {
        $url = '/mod/automultiplechoice/view.php?a='. $this->quizz->id;
        $cm = get_coursemodule_from_instance('automultiplechoice', $this->quizz->id, $this->quizz->course, false, MUST_EXIST);
        add_to_log($this->quizz->course, 'automultiplechoice', $action, $url, $msg, $cm->id, 0);
        return true;
    }

    public function lastlog($action) {
        global $DB;

        $cm = get_coursemodule_from_instance('automultiplechoice', $this->quizz->id, $this->quizz->course, false, MUST_EXIST);
        $sql = 'SELECT FROM_UNIXTIME(time) FROM {log} WHERE action=? AND cmid=? ORDER BY time DESC LIMIT 1';
        $res = $DB->get_field_sql($sql, array($action, $cm->id), IGNORE_MISSING);
        return $res;
    }

    /**
     * Gets the moodle_url that points to a file produced by this instance.
     *
     * @global moodle_page $PAGE
     * @param string $filename Local path and file name.
     * @param boolean $forcedld (opt, false)
     * @param integer $contextid (opt)
     * @return \moodle_url
     */
    public function getFileUrl($filename, $forcedld=false, $contextid=null) {
        global $PAGE;
        if (!$contextid) {
            $contextid = $PAGE->context->id;
        }
        return \moodle_url::make_pluginfile_url(
                $contextid,
                'mod_automultiplechoice',
                '',
                NULL,
                '',
                $this->relworkdir . '/' . ltrim($filename, '/'),
                $forcedld
        );
    }

    /**
     *
     * @param string $cmd
     * @param array $params List of strings.
     * @return boolean Success?
     */
    protected function shellExec($cmd, $params, $output=false) {
        $escapedCmd = escapeshellcmd($cmd);
        $escapedParams = array_map('escapeshellarg', $params);
        $shellCmd = $escapedCmd . " " . join(" ", $escapedParams);
        $lines = array();
        $returnVal = 0;
        exec($shellCmd, $lines, $returnVal);
            if ($output) {
                $this->shellOutput($shellCmd, $returnVal, $lines, DEBUG_DEVELOPER);
            }
        if ($returnVal === 0) {
            return true;
        } else {
            /**
             * @todo Fill $this->errors
             */
            $this->shellOutput($shellCmd, $returnVal, $lines, DEBUG_NORMAL);
            return false;
        }
    }

    /**
     * Displays a block containing the shell output
     *
     * @param string $cmd
     * @param integer $returnVal shell return value
     * @param array $lines output lines to be displayed
     */
    protected function shellOutput($cmd, $returnVal, $lines, $debuglevel) {
        if (get_config('core', 'debugdisplay') == 0) {
            return false;
        }
        $html = '<pre style="margin:2px; padding:2px; border:1px solid grey;">' . " \n";
        $html .= $cmd . " \n";
        $i=0;
        foreach ($lines as $line) {
            $i++;
            $html .= sprintf("%03d.", $i) . " " . $line . "\n";
        }
        $html .= "Return value = <b>" . $returnVal. "</b\n";
        $html .= "</pre> \n";
        debugging($html, $debuglevel);
    }

  
    /**
     * returns stat() information (number and dates) on prepared files already available
     * @return string diagnostic message
     */
    public function statPrepare() {
        $txtfiles = glob($this->workdir . '/prepare-*.txt');
        if ($txtfiles) {
            $filedata = stat($txtfiles[0]);
            $msg = count($txtfiles) . " fichier source préparé le " . self::isoDate($filedata['mtime']) . ".  ";
        } else {
            $msg = "Aucun fichier source préparé.  ";
        }
        $pdffiles = glob($this->workdir . '/prepare-*.pdf');
        if ( $pdffiles ) {
            $filedata = stat($pdffiles[0]);
            $msg .= "<b>" . count($pdffiles) . "</b> fichiers PDF préparés le " . self::isoDate($filedata['mtime']) . ".";
        } else {
            $msg .= 'Aucune fichier PDF préparé.';
        }
        return $msg;
    }

}
