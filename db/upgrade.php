<?php

/**
 * This file keeps track of upgrades to the automultiplechoice module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod_automultiplechoice
 * @copyright  2013-2014 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute automultiplechoice upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_automultiplechoice_upgrade($oldversion) {
    global $DB;

    if (version_compare(phpversion(), '5.4.0') < 0) {
        error("This module requires PHP 5.4. It won't work with an older PHP.");
    }

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    if ($oldversion < 2014091100) {
// cf http://docs.moodle.org/dev/XMLDB_creating_new_DDL_functions

        $table = new xmldb_table('automultiplechoice_log');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('instanceid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('action', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null, null);
        $table->add_field('actiontime', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);
        $table->add_key('instanceid', XMLDB_KEY_FOREIGN, array('instanceid'), 'automultiplechoice', array('id'));

        $table->add_index('uq_instance_action', XMLDB_INDEX_UNIQUE, array('instanceid', 'action'));
        $status = $dbman->create_table($table);

        // savepoint reached // @fixme is it necessary ?
        upgrade_mod_savepoint(true, 2014091100, 'automultiplechoice');
    }

    if ($oldversion < 2014091121) {
        $roles = $DB->get_records('role', array('archetype' => 'student'));
        $cid = context_system::instance()->id;
        foreach ($roles as $role) {
            assign_capability('mod/automultiplechoice:view', CAP_ALLOW, $role->id, $cid, true);
        }
        context_system::instance()->mark_dirty();
    }

    if ($oldversion < 2014100302) {
        $table = new xmldb_table('automultiplechoice');
        $field = new xmldb_field('studentaccess', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, "author");
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2014100302, 'automultiplechoice');
    }

    if ($oldversion < 2014101400) {
        $table = new xmldb_table('automultiplechoice');
        $field = new xmldb_field('descriptionformat', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, "description");
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2014101400, 'automultiplechoice');
    }

    if ($oldversion < 2014101500) {
        $table = new xmldb_table('automultiplechoice');
        $field = new xmldb_field('corrigeaccess', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, "studentaccess");
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2014101500, 'automultiplechoice');
    }

    // Final return of upgrade result (true, all went good) to Moodle.
    return true;
}
