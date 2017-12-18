<?php

/* @var $ADMIN admin_root */
/* @var $PAGE moodle/admin/settings.php page */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/mod/automultiplechoice/locallib.php');

    $PAGE->requires->js_call_amd('mod_automultiplechoice/settings', 'init', [AMC_VERSION_DISTANT]);

    $s = new admin_setting_configtext(
        'xelatexpath',
        get_string('settings_latex_path_short', 'mod_automultiplechoice'),
        get_string('settings_latex_path_full', 'mod_automultiplechoice'),
        '',
        PARAM_TEXT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);

    $defaulttpl = __DIR__ . '/amctemplate';
    $s = new admin_setting_configtext(
        'amctemplate',
        get_string('settings_amctemplate_short', 'mod_automultiplechoice'),
        get_string('settings_amctemplate_full', 'mod_automultiplechoice'),
        $defaulttpl,
        PARAM_TEXT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);

    $s = new admin_setting_configtext(
        'amccodelength',
        get_string('settings_code_length_short', 'mod_automultiplechoice'),
        get_string('settings_code_length_full', 'mod_automultiplechoice'),
        '8',
        PARAM_INT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);

    $s = new admin_setting_configtext(
        'instructionslstudent',
        get_string('settings_instructionslstudent_short', 'mod_automultiplechoice'),
        get_string('settings_instructionslstudent_full', 'mod_automultiplechoice'),
        get_string('settings_instructionslstudent_default', 'mod_automultiplechoice'),
        PARAM_TEXT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);

    $s = new admin_setting_configtext(
        'instructionslnamestd',
        get_string('settings_instructionslnamestd_short', 'mod_automultiplechoice'),
        get_string('settings_instructionslnamestd_full', 'mod_automultiplechoice'),
        get_string('settings_instructionslnamestd_default', 'mod_automultiplechoice'),
        PARAM_TEXT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);

    $s = new admin_setting_configtext(
        'instructionslnameanon',
        get_string('settings_instructionslnameanon_short', 'mod_automultiplechoice'),
        get_string('settings_instructionslnameanon_full', 'mod_automultiplechoice'),
        "",
        PARAM_TEXT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);

    $s = new admin_setting_configtextarea(
        'instructions',
        get_string('settings_instructions_short', 'mod_automultiplechoice'),
        get_string('settings_instructions_full', 'mod_automultiplechoice'),
        "",
        PARAM_RAW
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);

    $s = new admin_setting_configtextarea(
        'scoringrules',
        'Scoring rules',
        "Groups of rules are separed by a line of at least 3 dashes.
<p>
The first line of each block will be the title displayed in the dropdown list.
Eventually, lines of description follow. They will be displayed on the main form of settings.
After a eventual blank line, each line should contain a scoring rule like: <code>M|S ; default|[points] ; [rule]</code>.
The syntax of each rule is described in <a href=\"http://home.gna.org/auto-qcm/auto-multiple-choice.fr/interface-graphique.shtml#bareme\">AMC's documentation</a>.
When the question score is not explicit, it can be written <code>SCORE</code> in the rule.
</p>

Example:
<pre>Défaut
Pour une question simple à un point, un point pour une bonne réponse et aucun point dans tous les autres cas.
Pour une autre question simple, tous les points pour une bonne réponse, 0 si pas de réponse et -1 point dans tous les autres cas.
Pour une question à multiples bonnes réponses, un point est retiré par réponse incorrecte, sans dépasser -1 par question.

S ;       1 ; e=0,v=0,m=0,b=1
S ; default ; e=-1,v=0,m=-1,b=SCORE
M ; default ; e=-1,m=-1,p=-1,haut=SCORE

---
Tout ou rien
Pour toute question, tous les points si la réponse est totalement juste, 0 sinon.
S ; default ; e=0,v=0,m=0,b=SCORE
M ; default ; e=0,mz=SCORE
</pre>

<p>For each question, the first rule matching on the 2 first columns will be used.</p>
",
        "S ;       1 ; e=0,v=0,m=0,b=1",
        PARAM_TEXT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);

    $s = new admin_setting_configtextarea(
        'idnumberprefixes',
        get_string('settings_idnumberprefixes_short', 'mod_automultiplechoice'),
        get_string('settings_idnumberprefixes_full', 'mod_automultiplechoice'),
        '',
        PARAM_TEXT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);

}
