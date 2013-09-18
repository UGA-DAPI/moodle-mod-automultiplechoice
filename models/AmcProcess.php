<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

class AmcProcess
{
    /**
     * @var Quizz Contains notably an 'amcparams' attribute.
     */
    protected $quizz;

    /**
     * @var array
     */
    protected $errors = array();

    /**
     * Constructor
     *
     * @param Quizz $quizz
     */
    public function __construct($quizz) {
        $this->quizz = $quizz;
    }

    /**
     * Return the path to a PDF file.
     *
     * @return string Path to a PDF file.
     */
    public function publish()
    {
        /**
         * @todo Fill publish()... and rename func?
         */
    }

    /**
     * Return the errors of the last command.
     * @return array
     */
    public function getLastErrors() {
        return $this->errors;
    }

    /**
     *
     * @param string $cmd
     * @param array $params List of strings.
     * @return boolean Success?
     */
    protected function shellExec($cmd, $params) {
        $escapedCmd = escapeshellcmd($cmd);
        $escapedParams = array_map('escapeshellarg', $params);
        $lines = array();
        $returnVal = 0;
        exec($escapedCmd . " " . join(" ", $escapedParams), $lines, $returnVal);
        /**
         * @todo return $lines? or put them in a attr like errors?
         */
        if ($returnVal === 0) {
            return true;
        } else {
            /**
             * @todo Fill $this->errors
             */
            return false;
        }
    }
}
