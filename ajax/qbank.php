<?php

global $USER;
require_once(__DIR__ . './../locallib.php');

// automultiplechoice id
$qid = optional_param('qid', null, PARAM_INT);
// course id
$cid = required_param('cid', PARAM_INT);
// section id
$sid = optional_param('sid', null, PARAM_INT);
// category id
$catid = optional_param('catid', null, PARAM_INT);


$course_context = context_course::instance($cid);
if (!has_capability('moodle/question:useall', $course_context, $USER)) {
    $result = [
      'status' => 401,
      'message' => 'You are not allowed to see this.'
    ];
} elseif ($catid) {
    $questions_db = automultiplechoice_list_questions_by_categories($catid);
    $result = [
      'status' => 200,
      'questions' => $questions_db
    ];
} else {
    // only retrieve categories
    $categories_db = automultiplechoice_list_categories();
    $result = [
      'status' => 200,
      'categories' => $categories_db
    ];
}

echo json_encode($result);
