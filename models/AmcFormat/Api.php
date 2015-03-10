<?php

/* 
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

namespace mod\automultiplechoice\amcFormat;

/**
 * @param string $formatName "txt" | "latex"
 * @param mod\automultiplechoice\Quizz $quizz
 * @return mod\automultiplechoice\amcFormat\Api
 * @throws \Exception
 */
function buildFormat($formatName, $quizz) {
    $formatName = ucfirst(strtolower($formatName));
    $filename = __DIR__ . '/' . $formatName . '.php';
    if (file_exists($filename)) {
        require_once $filename;
    } else {
        throw new \Exception("Unknown format");
    }
    $formatName = 'mod\\automultiplechoice\\amcFormat\\' . $formatName;
    return (new $formatName($quizz));
}

abstract class Api
{
    /**
     * @var \mod\automultiplechoice\Quizz
     */
    public $quizz;

    /**
     * @var integer
     */
    public $codelength;

    public function __construct($quizz=null, $codelength=8) {
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
        foreach ($this->quizz->questions as $question) {
            $res .= $this->convertQuestion($question);

        }
        $res .= $this->getFooter();
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
     * @return string header block
     */
    abstract protected function getHeader();

    /**
     * Turns a question into a formatted string
     *
     * @param \mod\automultiplechoice\QuestionListItem $question
     * @return string
     */
    abstract protected function convertQuestion($question);

    /**
     * Computes the header block of the source file.
     *
     * @return string footer block
     */
    abstract protected function getFooter();
}
