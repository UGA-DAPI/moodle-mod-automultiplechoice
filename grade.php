<?php

/**
 * Redirect the user to the appropriate submission related page
 *
 * @package   mod_automultiplechoice
 * @category  grade
 * @copyright 2013 Silecs
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once dirname(dirname(__DIR__)) . "/config.php";

$id = required_param('id', PARAM_INT);          // Course module ID
$itemnumber = optional_param('itemnumber', 0, PARAM_INT); // Item number, may be != 0 for activities that allow more than one grade per user
$userid = optional_param('userid', 0, PARAM_INT); // Graded user ID (optional)

//in the simplest case just redirect to the view page
redirect('view.php?id='.$id);
