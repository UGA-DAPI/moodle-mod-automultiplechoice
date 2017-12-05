<?php

namespace mod_automultiplechoice\output;

defined('MOODLE_INTERNAL') || die();

class view_annotation implements \renderable, \templatable {
    /**
     * The auto multiple choice quiz.
     *
     * @var \mod_automultiplechoice\local\models\quiz
     */
    protected $quiz;

    /**
     *
     * @var array a set of usefull data
     */
    protected $data;

    /**
     * Contruct
     *
     * @param mod_automultiplechoice/local/models/quiz $quiz A quiz
     * @param array $data A set of usefull data
     */
    public function __construct($quiz, $data) {
        $this->quiz = $quiz;
        $this->data = $data;
    }

    /**
     * Prepare data for use in a template
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {

        $currentpage =  $this->data['pager']['page'];
        $pagingbar = new \paging_bar(
            $this->data['pager']['pagecount'],
            $currentpage,
            $this->data['pager']['perpage'],
            $this->data['pager']['url']
        );

        // replacement for groups_print_activity_menu
        $groups = [];
        foreach ($this->data['groups'] as $group) {
            $selected = intval($group->id) === intval($this->data['group']);
            $groups[] = [
                'value' => $group->id,
                'label' => $group->name,
                'selected' => $selected
            ];
        }

        // for groupmode === 2 see lib/grouplib.php define('VISIBLEGROUPS', 2);
        $content = [
            'quiz' => $this->quiz,
            'alreadyannoted' => $this->data['alreadyannoted'],
            'correctionfileurl' => $this->data['correctionfileurl'],
            'correctionfilename' => $this->data['correctionfilename'],
            'pager' => $output->render($pagingbar),
            'usersdata' => $this->data['usersdata'],
            'students' => $this->data['students'],
            'groupselector' => $groupselector,
            'groups' => $groups,
            'groupmode' => $this->data['isseparategroups'] ? get_string('groupsseparate', 'core') : get_string('groupsvisible', 'core'),
            'correctionaccess' => $this->quiz->corrigeaccess,
            'copyaccess' => $this->quiz->studentaccess,
            'shouldassociate' => $this->data['shouldassociate'],
            'unknowncopies' => $this->data['unknowncopies'],
            'unassociatedusers' => $this->data['unassociatedusers'],
            'showpager' => $this->data['pager']['pagecount'] > $this->data['pager']['perpage'],
            'showunknowncopies' => count($this->data['unknowncopies']) > 0 && !$this->data['shouldassociate']
        ];

        return $content;
    }
}
