<?php

/**
 * This file replaces the legacy STATEMENTS section in db/install.xml,
 * lib.php/modulename_install() post installation hook and partially defaults.php
 *
 * @package    mod_automultiplechoice
 * @copyright  2013 Silecs <contact@silecs.info>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Post installation procedure
 *
 * @see upgrade_plugins_modules()
 */
function xmldb_automultiplechoice_install() {
    if (version_compare(phpversion(), '5.4.0') < 0) {
        error("This module requires PHP 5.4. It won't work with an older PHP.");
    }
}

/**
 * Post installation recovery procedure
 *
 * @see upgrade_plugins_modules()
 */
function xmldb_automultiplechoice_install_recovery() {
}
