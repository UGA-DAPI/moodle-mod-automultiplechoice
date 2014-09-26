<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2014 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

require_once __DIR__ . '/AmcProcess.php';
require_once __DIR__ . '/Log.php';

class AmcProcessUpload extends AmcProcess
{
    public $nbPages = 0;

    public function upload($filename) {
        if (!$this->amcMeptex()) {
            $this->errors[] = "Erreur lors du calcul de mise en page (amc meptex).";
        }

        $this->nbPages = $this->amcGetimages($filename);
        if (!$this->nbPages) {
            $this->errors[] = "Erreur découpage scan (amc getimages)";
        }

        $analyse = $this->amcAnalyse(true);
        if (!$analyse) {
            $this->errors[] = "Erreur lors de l'analyse (amc analyse).";
        }
    }

    /**
     * Shell-executes 'amc getimages'
     * @param string $scanfile name, uploaded by the user
     * @return bool
     */
    private function amcGetimages($scanfile) {
        $pre = $this->workdir;
        $scanlist = $pre . '/scanlist';
        if (file_exists($scanlist)) {
            unlink($scanlist);
        }
        // delete all previous ppm/... files
        array_map('unlink', $this->findScannedFiles());

        $res = $this->shellExecAmc('getimages', array(
            '--progression-id', 'analyse',
            '--vector-density', '250',
            '--orientation', 'portrait',
            '--list', $scanlist,
            '--copy-to', $pre . '/scans/',
            $scanfile
            )
        );
        if ($res) {
            $nscans = count(file($scanlist));
            $this->log('getimages', $nscans . ' pages');
            return $nscans;
        }
        return $res;
    }

    /**
     * Shell-executes 'amc analyse'
     * @param bool $multiple (opt, true) If false, AMC will check that all the blank answer sheets were distinct.
     * @return bool
     */
    private function amcAnalyse($multiple = true) {
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
        $res = $this->shellExecAmc('analyse', $parameters);
        if ($res) {
            $this->log('analyse', 'OK.');
        }
        return $res;
    }
}