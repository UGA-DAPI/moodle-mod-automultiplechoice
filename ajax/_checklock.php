<?php

global $CFG;

require_once dirname(dirname(dirname(__DIR__))) . '/config.php';

$instanceid = required_param('a', PARAM_INT);
$actions = explode(',', required_param('actions', PARAM_ALPHAEXT));

$log = \mod_automultiplechoice\local\helpers\log::build($instanceid);

header('Content-Type: application/json; charset="UTF-8"');

try {
    $messages = $log->check($actions);
    $errors = '';
} catch (Exception $e) {
    $messages = array();
    $errors = $e->getMessage();
}
$response = array(
    'lock' => (count($messages) > 0),
    'msg' => $messages,
    'error' => $errors,
);
echo json_encode($response, JSON_PRETTY_PRINT);
