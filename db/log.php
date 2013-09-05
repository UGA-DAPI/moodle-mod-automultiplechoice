<?php

/**
 * Definition of log events
 *
 * NOTE: this is an example how to insert log event during installation/update.
 * It is not really essential to know about it, but these logs were created as example
 * in the previous 1.9 NEWMODULE.
 *
 * @package    mod_automultiplechoice
 * @copyright  2013 Silecs <contact@silecs.info>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $DB;

$logs = array(
    array('module'=>'automultiplechoice', 'action'=>'add', 'mtable'=>'automultiplechoice', 'field'=>'name'),
    array('module'=>'automultiplechoice', 'action'=>'update', 'mtable'=>'automultiplechoice', 'field'=>'name'),
    array('module'=>'automultiplechoice', 'action'=>'view', 'mtable'=>'automultiplechoice', 'field'=>'name'),
    array('module'=>'automultiplechoice', 'action'=>'view all', 'mtable'=>'automultiplechoice', 'field'=>'name')
);
