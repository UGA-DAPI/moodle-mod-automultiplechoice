<?php

/**
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/backup_automultiplechoice_stepslib.php');
require_once(__DIR__ . '/backup_automultiplechoice_settingslib.php');

/**
 * automultiplechoice backup task that provides all the settings and steps to perform one
 * complete backup of the activity
 */
class backup_automultiplechoice_activity_task extends backup_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Choice only has one structure step
        $this->add_step(new backup_automultiplechoice_activity_structure_step('automultiplechoice_structure', 'automultiplechoice.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

        // Link to the list of automultiplechoices
        $search="/(".$base."\/mod\/automultiplechoice\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@ALTERNATIVEINDEX*$2@$', $content);

        // Link to automultiplechoice view by moduleid
        $search="/(".$base."\/mod\/automultiplechoice\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@ALTERNATIVEVIEWBYID*$2@$', $content);

        return $content;
    }
}
