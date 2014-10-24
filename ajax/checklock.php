<?php

/*
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

global $CFG;

require_once dirname(dirname(dirname(__DIR__))) . '/config.php';
require_once dirname(__DIR__) . '/models/Log.php';

$instanceid = required_param('a', PARAM_INT);
$actions = explode(',', required_param('actions', PARAM_ALPHAEXT));

$log = mod\automultiplechoice\Log::build($instanceid);

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
