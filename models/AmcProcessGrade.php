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

    public $usersknown = 0;
    public $usersunknown = 0;

    protected $format;
    private $actions;
    /**
     * @global core_renderer $OUTPUT
     * @return string
     */
    /*public function getHtmlErrors() {
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
    }*/

    /**
     * Shell-executes 'amc prepare' for extracting grading scale (Bareme)
     * @return bool
     */
    /*protected function amcPrepareBareme() {
        $pre = $this->workdir;
        $path = get_config('mod_automultiplechoice', 'xelatexpath');
        if ($path === '') {
            $path = '/usr/bin/xelatex';
        }
        $parameters = array(
            '--n-copies', (string) $this->quizz->amcparams->copies,
            '--mode', 'b',
            '--data', $pre . '/data',
            '--filtered-source', $pre . '/prepare-source_filtered.tex', // for AMC-txt, the LaTeX will be written in this file
            '--progression-id', 'bareme',
            '--progression', '1',
            '--with', $path,
            '--filter', $this->format->getFilterName(),
            $pre . '/' . $this->format->getFilename()
        );
        $res = $this->shellExecAmc('prepare', $parameters);
        if ($res) {
            $this->log('prepare:bareme', 'OK.');
        }
        return $res;
    }*/

    /**
     * Shell-executes 'amc note'
     * @return bool
     */
    /*protected function amcNote() {
        $pre = $this->workdir;
        $parameters = array(
            '--data', $pre . '/data',
            '--progression-id', 'notation',
            '--progression', '1',
            '--seuil', '0.86', // black ratio threshold
            '--grain', $this->quizz->amcparams->gradegranularity,
            '--arrondi', $this->quizz->amcparams->graderounding,
            '--notemin', $this->quizz->amcparams->minscore,
            '--notemax', $this->quizz->amcparams->grademax,
            '--postcorrect-student', '', //FIXME inutile ?
            '--postcorrect-copy', '',    //FIXME inutile ?
            );
        $res = $this->shellExecAmc('note', $parameters);
        if ($res) {
            $this->log('note', 'OK.');
        }
        return $res;
    }*/

}
