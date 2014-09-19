<?php

/**
 * French strings for automultiplechoice
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_automultiplechoice
 * @copyright  2013 Silecs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'QCM papier';
$string['modulenameplural'] = 'QCM papier';
$string['modulename_help'] = 'Le module QCM papier permet de créer des PDF pour imprimer des QCM puis de corriger automatiquement les réponses scannées.';
$string['automultiplechoice'] = 'QCM papier';
$string['pluginadministration'] = 'QCM papier - édition';
$string['pluginname'] = 'QCM papier';
$string['noautomultiplechoices'] = 'Aucune instance de QCM papier n\'est définie dans ce cours';

$string['dashboard'] = 'Tableau de bord';
$string['documents'] = 'Sujets';
$string['uploadscans'] = 'Dépôt des copies';
$string['grading'] = 'Correction';

$string['instructionsheader'] = 'Rédaction de la consigne';
$string['automultiplechoicename'] = 'Nom du questionnaire';
$string['instructions'] = 'Consigne prédéfinie';
$string['description'] = 'Consigne';
$string['comment'] = 'Commentaire';
$string['qnumber'] = 'Nb. questions';
$string['score'] = 'Note totale';

$string['automultiplechoicename_help'] = 'Le nom complet du questionnaire';
$string['instructions_help'] = 'Le texte associé à cette consigne sera inséré au-dessus de la consigne personnalisée (champ suivant).';
$string['description_help'] = 'Le texte qui sera imprimé sur chaque questionnaire, contenant les consignes et la durée de l\'épreuve.
<br />A la création du questionnaire, le contenu de la consigne prédéfinie (champ précédent) sera inséré au début de la consigne,
et la consigne Attribution des points sera ajoutée à la fin.';
$string['comment_help'] = 'Un commentaire pour l\'auteur, qui ne sera pas imprimé.';
$string['qnumber_help'] = 'Le nombre de questions prévisionnel du questionnaire, pour validation.';
$string['score_help'] = 'Le score total du questionnaire (en points), pour validation.';

$string['amcparams'] = 'Paramètres AMC';
$string['amc_minscore'] = 'Note minimale';
$string['amc_copies'] = 'Nombre de versions';
$string['amc_shuffleq'] = 'Mélanger les questions';
$string['amc_shufflea'] = 'Mélanger les réponses';
$string['amc_separatesheet'] = 'Feuille réponses séparée';
$string['anonymous'] = 'Copies anonymes';
$string['amc_lstudent'] = 'Consigne / n° d\'étudiant';
$string['amc_lname'] = 'Zone d\'identification';
$string['amc_lstudent_help'] = 'Texte affiché à côté de la grille qui permet de saisir son numéro d\'étudiant.';
$string['amc_lname_help'] = 'Intitulé du cadre affiché en haut à droite de la feuille de réponse, par exemple pour indiquer à l\'étudiant qu\'il doit écrire son nom.';
$string['amc_lstudent_default'] = "Veuillez coder votre numéro d'étudiant ci-contre, et écrire votre nom dans la case ci-dessous.";
$string['amc_lname_default'] = 'Nom et prénom';
$string['amc_markmulti'] = 'Marque pour réponses multiples';
$string['amc_markmulti_help'] = 'Un trèfle sera affiché quand une question a plusieurs bonnes réponses.';

$string['questionselect'] = 'Sélection des questions';
$string['questionselected'] = 'Questions choisies';
$string['sortmsg'] = 'Les questions sélectionnées peuvent être triées en les déplaçant à la souris.';
$string['qexpected'] = '{$a} questions attendues.';
$string['savesel'] = 'Enregistrer la sélection';
$string['qcategory'] = 'Catégorie de question';
$string['qtitle'] = 'Question';
$string['qscore'] = 'Note';
$string['amc_displaypoints'] = 'Montrer les points';
$string['scoringrules'] = 'Règles de calcul';
$string['scoringset'] = 'Attribution des points';
$string['scoringsystem'] = 'Barème';
$string['insertsection'] = 'Insérer une nouvelle section';

$string['editselection'] = 'Modifier la sélection de questions';

$string['validateql_wrong_number'] = 'Le nombre de questions n\'est pas celui attendu.';
$string['validateql_wrong_sum'] = 'La somme des points ne fait pas la note totale attendue.' ;
$string['validateql_wrong_score'] = 'Le nombre de points d\'au moins une question n\'est pas valide.';
$string['validate_positive_int'] = 'Ceci devrait être un nombre strictement positif.';
$string['validate_poszero_int'] = 'Ceci devrait être un nombre positif ou nul.';
$string['validate_under_maxscore'] = 'Ceci devrait être inférieur à la note maximale.';
$string['validateql_deletedquestions'] = 'Certaines questions ne sont pas présentes dans Moodle et ont probablement été supprimées.';

$string['prepare'] = 'Prévisualiser les PDF';
$string['prepare-locked'] = 'Télécharger les sujets';
$string['analyse'] = 'Dépôt des copies des étudiants';
$string['note'] = 'Notes et copies corrigées';
$string['export'] = 'Rapports';

$string['questionoperations'] = 'Avant de sélectionner des questions, vous pouvez enrichir la banque de questions par…';
$string['importquestions'] = 'Import/ Création de questions';
$string['createquestions'] = "Création d'une question par formulaire";
