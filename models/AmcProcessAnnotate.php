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

class AmcProcessAnnotate extends AmcProcess
{
    


    
    
    /**
     * low-level Shell-executes 'amc annote'
     * fills the cr/corrections/jpg directory with individual annotated copies
     * @return bool
     */
    private function amcAnnote() {
        $pre = $this->workdir;
        if (!is_dir($pre. '/cr/corrections/jpg')) { // amc-annote will silently fail if the dir does not exist
            mkdir($pre. '/cr/corrections/jpg', 0777, true);
        }
        if (!is_dir($pre. '/cr/corrections/pdf')) {
            mkdir($pre. '/cr/corrections/pdf', 0777, true);
        }
        $parameters = array(
            '--projet', $pre,
            '--ch-sign', '4',
            '--cr', $pre . '/cr',
            '--data', $pre.'/data',
            //'--id-file',  '', // undocumented option: only work with students whose ID is in this file
            '--taille-max', '1000x1500',
            '--qualite', '90',
            '--line-width', '2',
            '--indicatives', '1',
            '--symbols', '0-0:none/#000000,0-1:circle/#ff0000,1-0:mark/#ff0000,1-1:mark/#00ff00',
            '--position', 'case',
            '--ecart', '10',
            '--pointsize-nl', '80',
            '--verdict', '%(ID) Note: %s/%m (score total : %S/%M)',
            '--verdict-question', '"%s / %m"',
            '--no-rtl',
            '--changes-only',
            '--fich-noms', $this->get_students_list(),
            //'--noms-encodage', 'UTF-8',
            //'--csv-build-name', 'surname name',
        );
        $res = $this->shellExecAmc('annote', $parameters);
        if ($res) {
            $this->log('annote', '');
        }
        return $res;
    }

     /**
     * lowl-level Shell-executes 'amc regroupe'
     * fills the cr/corrections/pdf directory with a global pdf file (parameter single==true) for all copies
     * or one pdf per student (single==false)
     * @single bool
     * @return bool
     */
    protected function amcRegroupe() {
        $pre = $this->workdir;    
        $parameters = array(
            //'--id-file',  '', // undocumented option: only work with students whose ID is in this file
            '--no-compose',
            '--projet',  $pre,
            '--sujet', $pre. '/' . $this->normalizeFilename('sujet'),
            '--data', $pre.'/data',
            '--progression-id', 'regroupe',
            '--progression', '1',
            '--fich-noms', $this->get_students_list(),
            '--noms-encodage', 'UTF-8',
            '--sort', 'n',
            '--register',
            '--no-force-ascii',
        '--modele', 'cr-(moodleid).pdf'
            /* // useless with no-compose
              '--tex-src', $pre . '/' . $this->format->getFilename(),
              '--filter', $this->format->getFilterName(),
              '--with', 'xelatex',
              '--filtered-source', $pre.'/prepare-source_filtered.tex',
              '--n-copies', (string) $this->quizz->amcparams->copies,
               */
        );
        $res = $this->shellExecAmc('regroupe', $parameters);
        if ($res) {
            $this->log('regroup', '');
            $amclog = Log::build($this->quizz->id);
            $amclog->write('correction');
        }
        return $res;
    }

     /**
     * (high-level) executes "amc annote" then "amc regroupe" to get one or several pdf files
     * for the moment, only one variant is possible : ONE global file, NO compose
     * @todo (maybe) manages all variants
     * @return bool
     */
    public function amcAnnotePdf() {
        $pre = $this->workdir;    
        //array_map('unlink', glob($pre.  "/cr/corrections/jpg/*.jpg"));
        array_map('unlink', glob($pre.  "/cr/corrections/pdf/*.pdf"));

        if (!$this->amcAnnote()) {
            return false;
        }
        if (!$this->amcRegroupe()) {
            return false;
        }
        $cmd  = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite "
            ." -sOutputFile=".$pre.'/'.$this->normalizeFilename('corrections')
            ." ".$pre."/cr/corrections/pdf/cr-*.pdf";
        $lines = array();
        $returnVal = 0;
        exec($cmd, $lines, $returnVal);

        $this->getLogger()->write($this->formatShellOutput($cmd, $lines, $returnVal));
        if ($returnVal === 0) {
            return true;
        } else {
            /**
             * @todo Fill $this->errors instead of outputing HTML on the fly
             */
            $this->displayShellOutput($cmd, $lines, $returnVal, DEBUG_NORMAL);
            return false;
        }
    }
    


    /**
     * @return boolean
     */
    public function hasAnotatedFiles() {
        return (file_exists($this->workdir . '/cr/corrections/pdf/' . $this->normalizeFilename('corrections')));
    }

    /**
     * count individual anotated answer sheets (pdf files)
     * @return int
     */
    public function countIndividualAnotations() {
        return count(glob($this->workdir . '/cr/corrections/pdf/cr-*.pdf'));
    }

    /**
     * returns a list of students with anotated answer sheets
     * @return array of (int) user.id
     */
    public function getUsersIdsHavingAnotatedSheets() {
        global $DB;

        $files = glob($this->workdir . '/cr/corrections/pdf/cr-*.pdf');
        $userids = array();
        foreach ($files as $file) {
        $userids[] = (int) substr($file,3,-4);
        }

        return $userids;
    }


    /**
    * Sends a Moodle message to all students having an anotated sheet
    * @param $usersIds array(user.id => user.username)
    * @return integer # messages sent
    */
    public function sendAnotationNotification($usersIds) {
        global $USER;
        $url = new \moodle_url('/mod/automultiplechoice.php', array('a' => $this->quizz->id));
        
        $eventdata = new \object();
        $eventdata->component         = 'mod_automultiplechoice';
        $eventdata->name              = 'anotatedsheet';
        $eventdata->userfrom          = $USER;
        $eventdata->subject           = "Correction disponible";
        $eventdata->fullmessageformat = FORMAT_PLAIN;   // text format
        $eventdata->fullmessage       = "Votre copie corrigée est disponible pour le QCM ". $this->quizz->name;
        $eventdata->fullmessagehtml   = "Votre copie corrigée est disponible pour le QCM ". $this->quizz->name
                                      . " à l'adresse " . \html_writer::link($url, $url) ;
        $eventdata->smallmessage      = "Votre copie corrigée est disponible pour le QCM ". $this->quizz->name;

        // documentation : http://docs.moodle.org/dev/Messaging_2.0#Message_dispatching
        $count = 0;
        foreach ($usersIds as $userid) {
            $eventdata->userto = $userid;
            $res = message_send($eventdata);
            if ($res) {
                $count++;
            }
        }
        return $count;
    }

}
