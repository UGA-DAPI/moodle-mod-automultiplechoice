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

    $s = new admin_setting_configtext(
        'xelatexpath',
        'Chemin vers XelateX',
        'Chemin vers le moteur LateX XelateX',
        '',
        PARAM_TEXT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);
    
    $defaulttpl = __DIR__ . '/amctemplate';
    $s = new admin_setting_configtext(
        'amctemplate',
        'Modèle AMC',
        'Chemin vers le modèle d\'arborescence AMC pour les nouveaux projets',
        $defaulttpl,
        PARAM_TEXT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);

    $s = new admin_setting_configtext(
        'amccodelength',
        'Longueur code',
        'Longueur du code étudiant pour l\'affichage AMC',
        '8',
        PARAM_INT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);

    $s = new admin_setting_configtext(
        'instructionslstudent',
        'Consigne / n° étudiant',
        'Valeur par défaut du champ homonyme, à la création de questionnaires papier.',
        "Veuillez coder votre numéro d'étudiant ci-contre, et écrire votre nom dans la case ci-dessous.",
        PARAM_TEXT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);

    $s = new admin_setting_configtext(
        'instructionslnamestd',
        "Zone d'identification / Standard",
        "Consigne par défaut du champ, à la création d'un questionnaires papier standard.",
        "Nom et prénom",
        PARAM_TEXT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);

    $s = new admin_setting_configtext(
        'instructionslnameanon',
        "Zone d'identification / Anonyme",
        "Consigne par défaut du champ, à la création d'un questionnaires papier anonyme.",
        "",
        PARAM_TEXT
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
        "",
        PARAM_TEXT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);

    $s = new admin_setting_configtextarea(
        'idnumberprefixes',
        "Préfixes du n° d'étudiant",
        "Préfixes, un par ligne. Attention aux espaces.\n"
            . "Chacun des préfixes sera inséré au début du numéro d'étudiant de chaque copie, jusqu'à ce que l'étudiant soit identifié"
            . " parmi les utilisateurs inscrits dans Moodle (cf import LDAP et idnumber)."
            . " Si aucun préfixe ne permet de trouver l'étudiant, une identification sans préfixe sera ensuite testée.",
        '',
        PARAM_TEXT
    );
    $s->plugin = 'mod_automultiplechoice';
    $settings->add($s);

}
