<?php
/**
 * @package    mod
 * @subpackage automultiplechoice
 * @copyright  2014 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

/**
 * Description of AmcLogfile
 *
 * @author François Gannaz <francois.gannaz@silecs.info>
 */
class AmcLogfile {
    const LOGFILE = 'AMC_commands.log';

    private $logfile = '';

    private $fh;

    public function __construct($dir) {
        $this->logfile = rtrim($dir, '/') . '/' . self::LOGFILE;
        $this->fh = fopen($this->logfile, 'a');
    }

    /**
     * @param string $msg
     */
    public function write($msg) {
        if ($this->fh) {
            fwrite($this->fh, "\n\n\n[" . date('Y-m-d H:i') . "]\n" . $msg);
        }
    }

    public function clear() {
        if ($this->fh) {
            ftruncate($this->fh, 0);
        }
    }
}
