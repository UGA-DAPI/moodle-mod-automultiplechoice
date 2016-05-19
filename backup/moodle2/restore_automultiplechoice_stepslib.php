<?php

/**
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_automultiplechoice_activity_task
 */

/**
 * Structure step to restore one automultiplechoice activity
 */
class restore_automultiplechoice_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = false;

        $paths[] = new restore_path_element('automultiplechoice', '/activity/automultiplechoice');

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_automultiplechoice($data) {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();

        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // insert the automultiplechoice record
        $newitemid = $DB->insert_record('automultiplechoice', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function after_execute() {
        // Add automultiplechoice related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_automultiplechoice', 'description', null);
    }
}
