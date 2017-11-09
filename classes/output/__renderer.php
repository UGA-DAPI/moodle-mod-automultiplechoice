<?php

namespace mod_automultiplechoice\output;

defined('MOODLE_INTERNAL') || die();
class renderer extends \plugin_renderer_base {
    
    public function render_dashboard(\templatable $page, $tabs) {
        //\core_renderer::navbar();
        $data = $page->export_for_template($this);
        $data['tabs'] = $tabs;
        return $this->render_from_template('mod_automultiplechoice/dashboard', $data);
    }

    public function render_questions(\templatable $page, $tabs) {
        $data = $page->export_for_template($this);
        $data['tabs'] = $tabs;
        return $this->render_from_template('mod_automultiplechoice/questions', $data);
    }
    
    public function render_student_view(\templatable $page) {
        $data = $page->export_for_template($this);
        return $this->render_from_template('mod_automultiplechoice/student_view', $data);
    }

    

}