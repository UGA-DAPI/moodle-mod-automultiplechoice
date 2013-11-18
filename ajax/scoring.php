<?php

/* 
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

global $CFG;

require_once dirname(dirname(dirname(__DIR__))) . '/config.php';
require_once dirname(__DIR__) . '/models/ScoringSystem.php';

$scoringsetid = optional_param('scoringsetid', null, PARAM_INT);

if (isset($scoringsetid)) {
    $scoringSet = mod\automultiplechoice\ScoringSystem::read()->getScoringSet($scoringsetid);
    echo nl2br(htmlspecialchars($scoringSet->description));
}
