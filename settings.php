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
        'instructions',
        'Default instructions',
        "Elements are separed by a line of at least 3 dashes. "
            . "The first line of each block will be the title displayed in the dropdown list. Example:<pre>"
            . "Concours\nVous avez 4 heures.\nL'anonymat est garanti.\n---\nFirst Test\nPlease use a pencil and gray each selected case completely.</pre>",
        "",
        PARAM_TEXT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);

    $s = new admin_setting_configtextarea(
        'scoringrules',
        'Scoring rules',
        "Groups of rules are separed by a line of at least 3 dashes. "
            . "The first line of each block will be the title displayed in the dropdown list. "
            . "Eventually, lines of description follow. They will be displayed on the main form of settings. "
            . "After a eventual blank line, each line should contain a scoring rule like: M|S ; default|[points]... ; [rule]. "
            . "The question score can be written SCORE in the rule. "
            . "For each question, the first rule matching on the 2 first columns will be used. "
            . "Example:<pre>"
            . "Défaut
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
</pre>",
        "",
        PARAM_TEXT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);
}
