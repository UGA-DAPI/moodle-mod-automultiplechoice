<?php

namespace mod_automultiplechoice\local\amc;

/**
 * This class provide the ability to write into a log file
 */
class logger {

    const LOGFILE = 'AMC_commands.log';

    /**
     * Path to log file
     */
    private $_logfile = '';

    /**
     * Log file handler
     */
    private $_fh;

    /**
     * Construct a new AmcLogger
     *
     * @param string $dir the base path to the log file
     */
    function __construct($dir) {
        $this->_logfile = rtrim($dir, '/') . '/' . self::LOGFILE;
        $this->_fh = fopen($this->_logfile, 'a');
    }

    /**
     * Write a message into the file
     *
     * @param string $msg the message to write
     */
    public function write($msg) {
        if ($this->_fh) {
            fwrite($this->_fh, "\n\n\n[" . date('Y-m-d H:i') . "]\n" . $msg);
        }
    }

    /**
     * Remove everything in the log file
     */
    public function clear() {
        if ($this->_fh) {
            ftruncate($this->_fh, 0);
        }
    }
}
