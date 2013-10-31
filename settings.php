<?php
/**
 * @package    mod_automultiplechoice
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* @var $ADMIN admin_root */

defined('MOODLE_INTERNAL') || die;


if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/mod/automultiplechoice/locallib.php');

    $defaulttpl = __DIR__ . '/amctemplate';
    $s = new admin_setting_configtext(
        'amctemplate',
        'Modèle AMC',
        'Modèle d\'arborescence AMC pour les nouveaux projets',
        $defaulttpl,
        PARAM_TEXT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);

    $s = new admin_setting_configtext(
        'amccodelength',
        'Longueur code',
        'Longueur du code étudiant pour l\'affichage AMC',
        '10',
        PARAM_INT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);

    $s = new admin_setting_configtextarea(
        'scorings',
        'Scoring strategies',
        "List of scoring strategies, e.g.<pre>\nNormal (1 pt)    | single  |  1 | e=0,v=0,b=1,m=0\nVicious (1.5pt)  |multiple|1.5|mz=1.5,m=-1,v=-2,p=-5</pre>",
        "",
        PARAM_TEXT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);

    $s = new admin_setting_configtextarea(
        'instructions',
        'Default instructions',
        "Elements are separed by a line of at least 3 dashes. "
            . "The first line of each block will bethe title displayed in the dropdown list. Example:<pre>"
            . "Concours\nVous avez 4 heures.\nL'anonymat est garanti.\n---\nFirst Test\nPlease use a pencil and gray each selected case completely.</pre>",
        "",
        PARAM_TEXT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);
}
