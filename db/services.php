<?php

$services = array(
      'amcservice' => array( //the name of the web service
          'functions' => array (
            'mod_automultiplechoice_call_amc'
          ),
          'requiredcapability' => 'mod/automultiplechoice:update',
          'restrictedusers' => 0,
          'enabled'=> 1,
     )
);

$functions = array(
    'mod_automultiplechoice_call_amc' => array(
        'classname'   => 'mod_automultiplechoice_external',
        'methodname'  => 'call_amc',
        'classpath'   => 'mod/automultiplechoice/externallib.php',
        'description' => 'Call amc commands.',
        'type'        => 'write',
        'ajax'        => true,
        'loginrequired' => true,
    ),
);
