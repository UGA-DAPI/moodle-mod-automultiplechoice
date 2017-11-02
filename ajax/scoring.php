<?php

global $CFG;

require_once(dirname(dirname(dirname(__DIR__))) . '/config.php');


$scoringsetid = optional_param('scoringsetid', null, PARAM_INT);

if (isset($scoringsetid)) {
    $scoringSet = \mod_automultiplechoice\local\models\scoring_system::read()->getScoringSet($scoringsetid);
    echo nl2br(htmlspecialchars($scoringSet->description));
}
