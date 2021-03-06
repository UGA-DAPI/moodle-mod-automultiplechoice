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
     * Save the AmcTXT source file.
     *
     * @param string $formatName "txt" | "latex"
     * @return amcFormat\Api
     */
    public function saveFormat($formatName) {
        try {
            $format = amcFormat\buildFormat($formatName, $this->quizz);
            $format->quizz = $this->quizz;
            $format->codelength = $this->codelength;
        } catch (\Exception $e) {
            // error
            $this->errors[] = $e->getMessage();
            return null;
        }

        $filename = $this->workdir . "/" . $format->getFilename();
        if (file_put_contents($filename, $format->getContent())) {
            return $format;
        } else {
            $this->errors[] = "Could not write the file for AMC. Disk full?";
            return null;
        }
    }

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
            $this->errors[] = "Generate  source file failed";
            return false;
        }
        $this->getLogger()->clear();

	$path = get_config('mod_automultiplechoice','xelatexpath');
	if ($path==''){
		$path = '/usr/bin/xelatex';
	}
        $pre = $this->workdir;
        $res = $this->shellExecAmc('prepare',
            array(
                '--n-copies', (string) $this->quizz->amcparams->copies,
                '--with', $path,
                '--filter', $format->getFiltername(),
                '--mode', 's[sc]',
                '--prefix', $pre,
                '--out-corrige', $pre . '/' . $this->normalizeFilename('corrige'),
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

    /**
     * Executes "amc imprime" then zip the resulting files
     * @return bool
     */
    public function printAndZip() {
        $pre = $this->workdir;
        if (!is_dir($pre . '/imprime')) {
            mkdir($pre . '/imprime');
        }

        $mask = $pre . "/imprime/*.pdf";
        array_map('unlink', glob($mask));
        $this->amcImprime();

        // clean up, or some obsolete files will stay in the zip
        $zipName = $pre . '/' . $this->normalizeFilename('sujets');
        if (file_exists($zipName)) {
            unlink($zipName);
        }

        $zip = new \ZipArchive();
        $ret = $zip->open($zipName, \ZipArchive::CREATE);
        if ( ! $ret ) {
            printf("Echec lors de l'ouverture de l'archive %d", $ret);
        } else {
            $options = array('add_path' => 'sujets_amc/', 'remove_all_path' => true);
            $zip->addGlob($mask, GLOB_BRACE, $options);
            // echo "Zip status: [" . $zip->status . "]<br />\n";
            // echo "Zip statusSys: [" . $zip->statusSys . "]<br />\n";
            echo "<p>Zip de [" . $zip->numFiles . "] fichiers dans [" . basename($zip->filename) . "]</p>\n";
            $zip->close();
        }
        if (!file_exists($zipName)) {
            echo "<strong>Erreur lors de la création de l'archive Zip : le fichier n'a pas été créé.</strong> $mask\n";
        }
        return $ret;
    }

    /**
     * Shell-executes 'amc imprime'
     * @return bool
     */
    protected function amcImprime() {
        $pre = $this->workdir;
        $params = array(
                    '--data', $pre . '/data',
                    '--sujet', $pre . '/' . $this->normalizeFilename('sujet'),
                    '--methode', 'file',
                    '--output', $pre . '/imprime/sujet-%e.pdf'
                );
        // $params[] = '--split'; // M#2076 a priori jamais nécessaire
        $res = $this->shellExecAmc('imprime', $params);
        if ($res) {
            $this->log('imprime', '');
        }
        return $res;
    }

}
