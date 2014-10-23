<?php

/**
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_automultiplechoice_activity_task
 */

/**
 * Define the complete automultiplechoice structure for backup, with file and id annotations
 */
class backup_automultiplechoice_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $automultiplechoice = new backup_nested_element('automultiplechoice', array('id'), array(
            'name', 'description', 'descriptionformat', 'comment',
            'qnumber', 'score', 'amcparams', 'questions',
            'author', 'studentaccess', 'corrigeaccess',
            'timecreated', 'timemodified'));

        // Define sources
        $automultiplechoice->set_source_table('automultiplechoice', array('id' => backup::VAR_ACTIVITYID));

        // GA -- Je ne sais pas si l'appel annotate_files est utile
        // Define file annotations
        $automultiplechoice->annotate_files('mod_automultiplechoice', 'intro', null); // This file area hasn't itemid

        // Return the root element (automultiplechoice), wrapped into standard activity structure
        return $this->prepare_activity_structure($automultiplechoice);
    }
}
