<?php

/**
 * Defines the version of automultiplechoice
 *
 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php
 *
 * @package    mod_automultiplechoice
 * @copyright  2013-2014 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$module->version   = 2014101400;      // The current module version (Date: YYYYMMDDXX)
$module->requires  = 2012120300;      // Requires this Moodle version
$module->cron      = 0;               // Period for cron to check this module (secs)
$module->component = 'mod_automultiplechoice'; // To check on upgrade, that module sits in correct place
