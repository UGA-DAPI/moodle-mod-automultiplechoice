<?php
/**
 * @package    mod_automultiplechoice
 * @copyright  2014 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod\automultiplechoice;

class FlashMessageManager
{
    static public function init() {
        self::clearMessages();
    }

    static public function addMessage($category, $message) {
        global $SESSION;
        $SESSION->flashmessages[$category][] = $message;
    }

    static public function displayMessages($clearAfterwards = true) {
        global $SESSION;
        global $OUTPUT;
        if (empty($SESSION->flashmessages)) {
            return;
        }
        foreach ($SESSION->flashmessages as $status => $messages) {
            if ($messages) {
                foreach ($messages as $message) {
                    $class = ($status === 'error' || $status === 'warning' ? "problem" : $status);
                    echo $OUTPUT->notification($message, "notify" . $class . " alert alert-" . $status);
                }
            }
        }
        if ($clearAfterwards) {
            self::clearMessages();
        }
    }

    static public function clearMessages() {
        global $SESSION;
        $SESSION->flashmessages = array(
            'success' => array(),
            'error' => array(),
            'warning' => array(),
        );
    }
}
