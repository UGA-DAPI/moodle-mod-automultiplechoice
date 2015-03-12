<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

require_once __DIR__ . '/AmcProcess.php';
require_once __DIR__ . '/AmcFormat/Api.php';
require_once __DIR__ . '/Log.php';

class AmcProcessPrepare extends AmcProcess
{
   

    /**
     * Shell-executes 'amc prepare' for creating pdf files
     *
     * @param string $formatName "txt" | "latex"
     * @return bool
     */
    public function amcCreatePdf($formatName) {
        $this->errors = array();

        $format = $this->saveFormat($formatName);
        if (!$format) {
            return false;
        }
        $this->getLogger()->clear();

	$path = get_config('mod_automultiplechoice','xelatexpath');
	if ($path==''){
		$path = 'xelatex';
	}
        $pre = $this->workdir;
        $res = $this->shellExecAmc('prepare',
            array(
                '--n-copies', (string) $this->quizz->amcparams->copies,
                '--with', $path,
                '--filter', $format->getFiltername(),
                '--mode', 's[c]',
                '--prefix', $pre,
                '--out-sujet', $pre . '/' . $this->normalizeFilename('sujet'),
                '--out-catalog', $pre . '/' . $this->normalizeFilename('catalog'),
                '--out-calage', $pre . '/prepare-calage.xy',
                '--latex-stdout',
                $pre . '/' . $format->getFilename()
            )
        );
        if ($res) {
            $amclog = Log::build($this->quizz->id);
            $this->log('prepare:pdf', 'catalog corrige sujet');
            $amclog->write('pdf');
        } else {
            $this->errors[] = "Exec of `auto-multiple-choice prepare` failed. Is AMC installed?";
        }
        return $res;
    }

    


}
