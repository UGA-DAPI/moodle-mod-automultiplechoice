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
    

public function __construct(Quizz $quizz,$formatName = 'latex') {
        parent::__construct($quizz, $formatName);
        
    }
    
/**
     * low-level Shell-executes 'amc annote'
     * fills the cr/corrections/jpg directory with individual annotated copies
     * @return bool
     */
    public function amcAnnote() {
        $pre = $this->workdir;
        if (!is_dir($pre. '/cr/corrections/jpg')) { // amc-annote will silently fail if the dir does not exist
            mkdir($pre. '/cr/corrections/jpg', 0777, true);
        }
        if (!is_dir($pre. '/cr/corrections/pdf')) {
            mkdir($pre. '/cr/corrections/pdf', 0777, true);
        }
    if ($this->quizz->amcparams->answerSheetColumns > 2) {
        $ecart='8';
        $pointsize='110';
    }else{
        $ecart='10';
        $pointsize='80';
    }
    $parameters = array(
            '--projet', $pre,
            '--ch-sign', '3',
            '--cr', $pre . '/cr',
            '--data', $pre.'/data',
            //'--id-file',  '', // undocumented option: only work with students whose ID is in this file
            '--taille-max', '1000x1500',
            '--qualite', '90',
            '--line-width', '2',
            '--indicatives', '1',
            '--symbols', '0-0:none/#000000,0-1:circle/#ff0000,1-0:mark/#ff0000,1-1:mark/#00ff00',
            '--position', 'case',
            '--ecart', $ecart,
            '--pointsize-nl', $pointsize,
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
            $amclog = Log::build($this->quizz->id);
            $amclog->write('annotating');
        }
        return $res;
    }

    /**
     * @return boolean
     */
    public function countAnnotatedFiles() {
        return (count(glob($this->workdir . '/cr/corrections/jpg/page-*.jpg')));
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