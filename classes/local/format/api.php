<?php

namespace mod_automultiplechoice\local\format;

abstract class api
{
    /**
     * @var \mod\automultiplechoice\Quizz
     */
    public $quiz;
    /**
     * @var integer
     */
    public $codelength;
    public function __construct($quiz = null, $codelength = 8) {
        $this->quiz = $quiz;
        $this->codelength = $codelength;
    }
    /**
     * Compute the whole source file content for AMC,
     * by merging header and question blocks.
     *
     * @return string file content
     */
    public function getContent() {
        if (!$this->quiz) {
            throw new \Exception("No quiz set, cannot convert.");
        }
        $res = $this->getHeader();
        foreach ($this->quiz->questions as $question) {
            $res .= $this->convertQuestion($question);
        }
        $res .= $this->getFooter();
        return $res;
    }
    /**
     * Instanciate a class depending on the format
     * must be a way to do that more elegantly...
     *
     * @param string $formatName "txt" | "latex"
     * @param mod\automultiplechoice\Quizz $quizz
     * @return mod\automultiplechoice\amcFormat\Api
     * @throws \Exception
     */
    public function buildFormat($format, $quiz) {
        /*$formatName = ucfirst(strtolower($formatName));
        $filename = __DIR__ . '/' . $formatName . '.php';
        if (file_exists($filename)) {
            include_once $filename;
        } else {
            throw new \Exception("Unknown format");
        }
        $formatName = 'mod\\automultiplechoice\\amcFormat\\' . $formatName;
        return (new $formatName($quizz));*/
        $instance = null;
        if ($format === 'txt') {
            return new \mod_automultiplechoice\local\format\text($quiz);
        }

        return new \mod_automultiplechoice\local\format\latex($quiz);
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
