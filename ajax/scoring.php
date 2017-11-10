<?php

global $CFG;

require_once(dirname(dirname(dirname(__DIR__))) . '/config.php');


$scoringsetid = optional_param('scoringsetid', null, PARAM_INT);

if (isset($scoringsetid)) {
    $scoringset = \mod_automultiplechoice\local\models\scoring_system::read()->getScoringSet($scoringsetid);
    echo nl2br(htmlspecialchars($scoringset->description));
}
