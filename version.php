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

$plugin->version   = 2016020400;      // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2012120300;      // Requires this Moodle version
$plugin->cron      = 0;               // Period for cron to check this plugin (secs)
$plugin->component = 'mod_automultiplechoice'; // To check on upgrade, that plugin sits in correct place
