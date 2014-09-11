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
     * Return the HTML that lists links to the PDF files.
     *
     * @return string
     */
    public function htmlPdfLinks() {
        $opts = array('target' => '_blank');
        $links = array(
            \html_writer::link($this->getFileUrl($this->normalizeFilename('sujet')), $this->normalizeFilename('sujet'), $opts),
            \html_writer::link($this->getFileUrl($this->normalizeFilename('catalog')), $this->normalizeFilename('catalog'), $opts),
        );
        return <<<EOL
        <ul class="amc-files">
            <li>
                $links[0]
                <div>Ce fichier contient tous les énoncés regroupés. <span class="warning">Ne pas utiliser ce fichier pour distribuer aux étudiants.</span></div>
            </li>
            <li>
                $links[1]
                <div>Le corrigé de référence.</div>
            </li>
        </ul>
EOL;
    }

    /**
     * Return the HTML that for the link to the ZIP file.
     *
     * @return string
     */
    public function htmlZipLink() {
        $links = array(
            \html_writer::link($this->getFileUrl($this->normalizeFilename('sujets')), $this->normalizeFilename('sujets')),
        );
        return <<<EOL
        <ul class="amc-files">
            <li>
                $links[0]
                <div>Cette archive contient un PDF par variante de l'énoncé.</div>
            </li>
        </ul>
EOL;
    }

    /**
     * Save the AmcTXT source file.
     *
     * @param string $formatName "txt" | "latex"
     * @return AmcFormat\Api
     */
    protected function saveFormat($formatName) {
        $this->initWorkdir();

        try {
            $format = AmcFormat\buildFormat($formatName);
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
            return false;
        }

        $pre = $this->workdir;
        $amclog = Log::build($this->quizz->id);
        $amclog->write('process');
        $res = $this->shellExec('auto-multiple-choice prepare',
            array(
                '--n-copies', (string) $this->quizz->amcparams->copies,
                '--with', 'xelatex',
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
            $amclog->write('process');
            $this->log('prepare:pdf', 'catalog corrige sujet');
            $amclog->write('pdf');
        } else {
            $this->errors[] = "Exec of `auto-multiple-choice prepare` failed. Is AMC installed?";
            $amclog->write('process', 0);
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

        $zip = new \ZipArchive();
        $ret = $zip->open($pre . '/' . $this->normalizeFilename('sujets'), \ZipArchive::CREATE);
        if ( ! $ret ) {
            printf("Echec lors de l'ouverture de l'archive %d", $ret);
        } else {
            $options = array('add_path' => 'sujets_amc/', 'remove_all_path' => true);
            $zip->addGlob($mask, GLOB_BRACE, $options);
            // echo "Zip status: [" . $zip->status . "]<br />\n";
            // echo "Zip statusSys: [" . $zip->statusSys . "]<br />\n";
            echo "Zipped [" . $zip->numFiles . "] files into [" . basename($zip->filename) . "]<br />\n";
            $zip->close();
        }
        if (!file_exists($pre . '/' . $this->normalizeFilename('sujets'))) {
            echo "<strong>Erreur lors de la création de l'archive Zip : le fichier n'a pas été créé.</strong> $mask\n";
        }
        return $ret;
    }


    /**
     * Initialize the data directory $this->workdir with the template structure.
     */
    protected function initWorkdir() {
        if ( ! file_exists($this->workdir) || ! is_dir($this->workdir)) {
            $parent = dirname($this->workdir);
            if (!is_dir($parent)) {
                if (!mkdir($parent, 0777, true)) {
                    error("Could not create directory. Please contact the administrator.");
                }
            }
            if (!is_writeable($parent)) {
                error("Could not write in directory. Please contact the administrator.");
            } else {
                $templatedir = get_config('mod_automultiplechoice', 'amctemplate');
                $this->shellExec('cp', array('-r', $templatedir, $this->workdir));
            }
        }
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
        $amclog = Log::build($this->quizz->id);
        $amclog->write('process');
        $res = $this->shellExec('auto-multiple-choice imprime', $params);
        if ($res) {
            $amclog->write('process', 0);
            $this->log('imprime', '');
        }
        return $res;
    }

}
