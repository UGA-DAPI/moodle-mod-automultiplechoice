<?php

/**
 *  Defines message providers (types of message sent) for the Automultiplechoice module
 *
 * @package    mod_automultiplechoice
 * @copyright  2013-2014 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// documentation : http://docs.moodle.org/dev/Messaging_2.0#Under_the_bonnet

defined('MOODLE_INTERNAL') || die();

$messageproviders = array(
    // Notification to the student that he has an anotated answer sheet available
    'anotatedsheet' => array(
        'capability' => 'mod/automultiplechoice:view',
        'defaults' => array(
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN,
            'email' => MESSAGE_FORCED,
        ),
    ),
);
