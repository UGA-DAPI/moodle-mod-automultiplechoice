<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Contains class mod_questionnaire\output\indexpage
 *
 * @package    mod_questionnaire
 * @copyright  2016 Mike Churchward (mike.churchward@poetgroup.org)
 * @author     Mike Churchward
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
namespace mod_automultiplechoice\output;

defined('MOODLE_INTERNAL') || die();

class indexpage implements \renderable, \templatable {
    /**
     * An array of headings
     *
     * @var array
     */
    protected $headings;
    /**
     * An array of rows
     *
     * @var array
     */
    protected $rows;
    /**
     * Contruct
     *
     * @param array $headings An array of renderable headings
     */
    public function __construct(array $titles = [], array $content = []) {
        $this->headings = [];
        $this->rows = [];
        $colnum = 1;
        foreach ($titles as $key => $title) {
            $this->headings['title'.$colnum++] = $title;
        }
        foreach ($content as $key => $row) {
            $this->rows[] = $row;
        }
    }
    /**
     * Prepare data for use in a template
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        $data = ['headings' => [], 'rows' => []];
        foreach ($this->headings as $key => $heading) {
            $data['headings'][$key] = $heading;
        }
        foreach ($this->rows as $row) {
            list($topic, $name, $responses, $type) = $row;
            $data['rows'][] = ['topic' => $topic, 'name' => $name, 'responses' => $responses, 'type' => $type];
        }
        return $data;
    }
}
