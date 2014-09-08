<?php

/* 
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

namespace mod\automultiplechoice\AmcFormat;

/**
 * @param string $formatName "txt" | "latex"
 * @return mod\automultiplechoice\AmcFormat\Api
 * @throws \Exception
 */
function buildFormat($formatName) {
    $formatName = ucfirst(strtolower($formatName));
    $filename = __DIR__ . '/' . $formatName . '.php';
    if (file_exists($filename)) {
        require_once $filename;
    } else {
        throw new \Exception("Unknown format");
    }
    $formatName = 'mod\\automultiplechoice\\AmcFormat\\' . $formatName;
    return (new $formatName);
}

abstract class Api
{
    /**
     * @var Quizz
     */
    public $quizz;

    /**
     * @var integer
     */
    public $codelength;

    public function __construct($quizz=null, $codelength=10) {
        $this->quizz = $quizz;
        $this->codelength = $codelength;
    }

    /**
     * Compute the whole source file content for AMC, by merging header and question blocks.
     *
     * @return string file content
     */
    public function getContent() {
        if (!$this->quizz) {
            throw new \Exception("No quizz set, cannot convert.");
        }
        $res = $this->getHeader();
        foreach ($this->quizz->questions->getRecords($this->quizz->amcparams->scoringset) as $question) {
            $res .= $this->convertQuestion($question);

        }
        return $res;
    }

    /**
     * @return string
     */
    abstract public function getFilename();

    /**
     * @return string
     */
    abstract public function getFilterName();

    /**
     * Computes the header block of the source file.
     *
     * @return string header block of the AMC-TXT file
     */
    abstract protected function getHeader();

    /**
     * Turns a question into a formatted string, in the AMC-txt (aka plain) format.
     *
     * @param object $question record from the 'question' table
     * @return string
     */
    abstract protected function convertQuestion($question);
}
