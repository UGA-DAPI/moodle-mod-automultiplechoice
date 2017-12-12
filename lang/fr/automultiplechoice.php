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

// Annotating.
$string['annotating_notify'] = '{$a->nbSuccess} messages envoyés pour {$a->nbStudents} étudiants ayant une copie annotée.';
$string['annotating_rebuilt_sheets'] = 'Régénérer les copies';
$string['annotating_corrected_sheets'] = 'Copies corrigées';
$string['annotating_individual_sheets_available'] = ' copies individuelles annotées disponibles.';
$string['annotating_update_corrected_sheets'] = 'Mettre à jour les copies corrigées (annotées)';
$string['annotating_generate_corrected_sheets'] = 'Générer les copies corrigées';
$string['annotating_sheets_access'] = 'Accès aux copies';
$string['annotating_allow_access'] = 'Permettre l\'accès de chaque étudiant';
$string['annotating_copy_only'] = 'À sa copie corrigée annotée';
$string['annotating_whole_correction'] = 'Au corrigé complet';
$string['annotating_copy_sending'] = 'Envoi des copies';
$string['annotating_send_moodle_message'] = 'Envoyer un message';
$string['annotating_send_moodle_message_title'] = 'Envoyer un message Moodle à chaque étudiant';
$string['annotating_associate_user'] = 'Associer';
$string['annotating_copy_without_user'] = 'Copie(s) without user';

// Annotate process.
$string['annotate_correction_available'] = 'Correction disponible';
$string['annotate_correction_available_body'] = 'Votre copie corrigée est disponible pour le QCM {$a->name}';
$string['annotate_correction_link'] = ' à l\'adresse ';

// Associating.
$string['associating_heading'] = 'Association';
$string['associating_sheets_identified'] = '{$a->automatic} copies automatiquement identifiés, {$a->manualy} copies manuellement identifiées et {$a->unknown} non identifiées.';
$string['associating_relaunch_association'] = 'Relancer l\'association';
$string['associating_launch_association'] = 'Lancer l\'association';
$string['associating_error_note'] = 'Erreur lors du calcul des notes.';
$string['associating_error_associate'] = 'Erreur(s) lors du processus d\'association.';
$string['associating_no_data_for_query'] = 'Aucune donnée ne correspond à votre requête...';

// Common.
$string['unlock_quiz'] = 'Déverrouiller (permettre les modifications du questionnaire)';
$string['lock_quiz'] = 'Verrouiller le questionnaire';
$string['quiz_is_locked'] = 'Le questionnaire est actuellement verrouillé pour éviter les modifications entre l\'impression et la correction.';
$string['quiz_save_error'] = 'Erreur lors de la sauvegarde.';
$string['file_type'] = 'Type du fichier';
$string['access_documents'] = 'Vous pouvez accéder aux documents via l\'onglet';
$string['error_could_not_create_directory'] = 'Impossible de créer le répertoire. Merci de contacter votre administrateur système.';
$string['error_could_not_write_directory'] = 'Impossible d\'écrire dans le répertoire. Merci de contacter votre administrateur système.';
$string['error_amc_getimages'] = 'Erreur découpage scan (amc getimages).';
$string['error_amc_analyse'] = 'Erreur lors de l\'analyse (amc analyse).';
$string['save'] = 'Sauvegarder';

// Dashboard.
$string['subjects_ready_for_distribution'] = 'Les sujets sont prêts à être distribués.';
$string['preparatory_documents_ready'] = 'Les sujets n\'ont pas encore été figés mais les documents préparatoires sont disponibles';
$string['no_document_available'] = 'Aucun document disponible';
$string['pdf_last_prepare_date'] = 'Dernière préparation des sujets PDF le ';
$string['pdf_none_prepared'] = 'Aucun sujet PDF n\'a encore été préparé.';
$string['dashboard_nb_page_scanned'] = '{$a->nbpages} pages scannées ont été déposées le {$a->date}';
$string['dashboard_no_sheets_corrected'] = 'Aucune copie corrigée ou notée.';

// Documents.
$string['documents_meptex_error'] = 'Erreur lors du calcul de mise en page (amc meptex).';
$string['documents_pdf_created'] = 'Fichiers PDF créés.';
$string['documents_zip_archive'] = 'Archive ZIP.';
$string['documents_restore_original_version'] = 'Restaurer la version originale';
$string['documents_mix_answers_and_questions'] = 'Mélanger les questions et les réponses';

// Export process.
$string['export_amc_cmd_failed'] = 'Exec of `{$a->cmd}` failed. Is AMC installed?';
$string['export_archive_open_failed'] = 'Echec lors de l\'ouverture de l\'archive {$a->error}';
$string['export_archive_create_failed'] = 'Erreur lors de la création de l\'archive Zip : le fichier n\'a pas été créé. {$a->mask}';
$string['export_file_write_access_error'] = 'Le fichier {$a->file} n\'a pas pu être recréé. Contactez l\'administrateur pour un problème de permissions de fichiers.';
$string['export_file_create_error'] = 'Le fichier n\'a pas pu être recréé. Consultez l\'administrateur.';
$string['export_dir_access_error'] = 'Le répertoire /exports n\'est pas accessible en écriture. Contactez l\'administrateur.';

// Grading.
$string['grading_relaunch_correction'] = 'Relancer la correction';
$string['grading_notes'] = 'Notes';
$string['grading_file_notes_table'] = 'Fichiers tableaux des notes';
$string['grading_sheets_identified'] = '{$a->known} copies identifiées et {$a->unknown} non identifiées.';
$string['grading_statistics'] = 'Statistiques';
$string['grading_not_satisfying_notation'] = 'Si le résultat de la notation ne vous convient pas, vous pouvez modifier le barème puis relancer la correction.';
$string['grading_size'] = 'Effectifs';
$string['grading_mean'] = 'Moyenne';
$string['grading_median'] = 'Médiane';
$string['grading_mode'] = 'Mode';
$string['grading_range'] = 'Intervalle';
$string['grading_no_stats'] = 'Aucune statistique disponnible actuellement.';

// Logs messages.
$string['log_process_running'] = 'AMC est déjà en cours d\'exécution depuis {$a->time} minutes.';
$string['log_scoring_edited'] = 'Le choix du barème a été modifié depuis la dernière préparation des sujets PDF.';
$string['log_questions_changed'] = 'La selection de question été modifié depuis la dernière préparation des sujets PDF.';
$string['log_pdf_changed_since_last_analyse'] = 'Le PDF du QCM a été modifié depuis la dernière analyse des sujets.';
$string['log_pdf_changed_since_last_upload'] = 'Le PDF du QCM a été modifié depuis le dernier dépôt des copies.';
$string['log_last_lock_after_last_upload'] = 'Le dernier verrouillage du QCM a eu lieu après le dernier dépôt des copies.';
$string['log_last_analyse_after_last_upload'] = 'La dernière analyse du sujet a eu lieu après le dernier dépôt des copies.';
$string['log_relaunch_correction_uploads'] = 'Des copies d\'étudiant ont été déposées depuis la dernière notation. Relancer la correction ?';
$string['log_relaunch_correction_scale'] = 'Le barème a été modifié depuis la dernière notation. Relancer la correction ?';
$string['log_relaunch_association_uploads'] = 'Des copies d\'étudiant ont été déposées depuis la dernière association. Relancer l\'association ?';
$string['log_relaunch_association_grading'] = 'Des copies d\'étudiant ont été notées depuis la dernière association. Relancer l\'association ?';
$string['log_sheets_no_grading'] = 'Les copies d\'étudiant n\'ont pas encore été notées.';
$string['log_relaunch_export_grading'] = 'La dernière notation est plus récente que les exports. Re-générer les exports ?';
$string['log_relaunch_annotation_grading'] = 'La dernière notation est plus récente que les copies annotées. Re-générer les copies corrigées ?';
$string['log_relaunch_annotate_annotating'] = 'La dernière annotation est plus récente que les copies annotées PDF. Re-générer les copies corrigées PDF?';
$string['log_unlock_uploads_exists'] = 'Des copies scannées ont déjà été déposées. En cas de modification du QCM, les copies scannées ne seront plus valables.';

// Process.
$string['process_no_quiz_id'] = 'No quiz ID';
$string['process_no_amc_format'] = 'Erreur, pas de format de QCM pour AMC.';
$string['process_unable_to_write_file'] = 'Could not write the file for AMC. Check the space available on disk.';
$string['process_statements_file'] = 'Ce fichier contient tous les énoncés regroupés. <span class="warning">Ne pas utiliser ce fichier pour distribuer aux étudiants.</span>';
$string['process_catalog_file'] = 'Le catalogue de questions.';
$string['process_corrections_file'] = 'Les  corrigés des différentes versions.';
$string['process_archive'] = 'Cette archive contient un PDF par variante de l\'énoncé.';
$string['catalog'] = 'Catalogue';
$string['corrections'] = 'Corrigés';

// Questions.
$string['questions_recent_question_not_visible'] = "Si vos questions récentes n'apparaissent pas, pensez à rafraichir la page de votre navigateur (F5) et à trier par date descendante.";
$string['question_remove_confirm'] = 'Êtes vous sur de vouloir supprimer <strong>{$a}</strong> de la sélection?';

// Scoring.
$string['scoring_scale_extract_error'] = 'Erreur lors de l\'extraction du barème';
$string['scoring_scale_save_success'] = 'Les modifications du barème ont été enregistrées.';
$string['scoring_allocate_points'] = 'Répartir les points.';
$string['scoring_show_hide_answers'] = 'Afficher/masquer les réponses.';

// Scan upload.
$string['uploadscans_file_not_accessible'] = 'Impossible d\'accéder au fichier déposé.';
$string['uploadscans_no_image_known'] = 'Erreur, {$a->nbpages} pages scannées mais aucune image n\'a été reconnue (pas de PPM).';
$string['uploadscans_process_end_message'] = 'Le processus s\'est achevé : {$a->nbpages} pages nouvellement scannées, {$a->nbextracted} extraites, {$a->nbidentified} pages avec marqueurs.';
$string['uploadscans_saved_sheets'] = 'Copies enregistrées : <b>{$a->nbsaved}</b> pages scannées ont été déposées le {$a->date}';
$string['uploadscans_add_sheets'] = 'Ajouter des copies';
$string['uploadscans_add_sheets_message'] = 'Si vous déposez de nouvelles pages scannées, elles seront ajoutées aux précédentes.';
$string['uploadscans_no_sheets_uploaded'] = 'Aucune copie n\'a encore été déposée.';
$string['uploadscans_delete_sheets'] = 'Supprimer les copies';
$string['uploadscans_delete_sheets_warn'] = 'Vous pouvez effacer les copies déjà déposées. Ceci effacera aussi les notes. Vous pourrez ensuite déposer de nouveaux scans.';
$string['uploadscans_delete_sheets_confirm'] = 'Supprimer définitivement les copies déposées sur le serveur ?';
$string['uploadscans_unknown_scans'] = 'Scans non reconnus';
$string['uploadscans_delete_unknown_scans'] = 'Effacer tous les scans non reconnus';
$string['uploadscans_download_unknown_scans'] = 'Télécharger tous les scans non reconnus';
$string['uploadscans_install_sqlite3'] = 'Demandez à votre administrateur système d\'installer php-sqlite3 pour voir les fichiers non reconnus';
$string['uploadscans_file'] = 'Fichier scanné (PDF ou TIFF)';

// Settings.
$string['settings_latex_path_short'] = 'Chemin vers XelateX';
$string['settings_latex_path_full'] = 'Chemin vers le moteur LateX XelateX';
$string['settings_amctemplate_short'] = 'Modèle AMC';
$string['settings_amctemplate_full'] = 'Chemin vers le modèle d\'arborescence AMC pour les nouveaux projets';
$string['settings_code_length_short'] = 'Longueur code';
$string['settings_code_length_full'] = 'Longueur du code étudiant pour l\'affichage AMC';
$string['settings_instructionslstudent_short'] = 'Consigne / n° étudiant';
$string['settings_instructionslstudent_full'] = 'Valeur par défaut du champ homonyme, à la création de questionnaires papier.';
$string['settings_instructionslstudent_default'] = 'Veuillez coder votre numéro d\'étudiant ci-contre, et écrire votre nom dans la case ci-dessous.';
$string['settings_instructionslnamestd_short'] = 'Zone d\'identification / Standard';
$string['settings_instructionslnamestd_full'] = 'Consigne par défaut du champ, à la création d\'un questionnaires papier standard.';
$string['settings_instructionslnamestd_default'] = 'Nom et prénom';
$string['settings_instructionslnameanon_short'] = 'Zone d\'identification / Anonyme';
$string['settings_instructionslnameanon_full'] = 'Consigne par défaut du champ, à la création d\'un questionnaires papier anonyme.';
$string['settings_instructions_short'] = 'Default instructions';
$string['settings_instructions_full'] = 'Elements are separed by a line of at least 3 dashes. The first line of each block will be the title displayed in the dropdown list. Example:<pre>Concours\\nVous avez 4 heures.\\nL\'anonymat est garanti.\\n---\\nFirst Test\\nPlease use a pencil and gray each selected case completely.</pre>';
$string['settings_idnumberprefixes_short'] = 'Préfixes du n° d\'étudiant';
$string['settings_idnumberprefixes_full'] = '<p>Préfixes, un par ligne. Attention aux espaces.</p><p>Chacun des préfixes sera inséré au début du numéro d\'étudiant de chaque copie, jusqu\'à ce que l\'étudiant soit identifié parmi les utilisateurs inscrits dans Moodle (cf import LDAP et idnumber). Si aucun préfixe ne permet de trouver l\'étudiant, une identification sans préfixe sera ensuite testée.</p>';

$string['settings_amcversion_label'] = 'Version / Mode AMC';
$string['settings_amcversion_description'] = 'Choix de la version ou du mode AMC.';
$string['settings_amcversion_choice_1_label'] = 'Distant';
$string['settings_amcversion_choice_2_label'] = 'Version AMC locale antérieure ou égale à 1.2';
$string['settings_amcversion_choice_3_label'] = 'Version AMC locale supérieure à 1.2';

$string['settings_amcapiurl_label'] = 'URL de l\'API AMC';
$string['settings_amcapiurl_description'] = 'URL à utiliser pour toutes les manipulations AMC.';

// Student view.
$string['studentview_one_corrected_sheet'] = 'Vous avez une copie corrigée :';
$string['studentview_no_corrected_sheet'] = 'Vous n\'avez pas de copie corrigée pour ce QCM';
$string['studentview_view_corrected_sheet'] = 'Vous pouvez consulter le corrigé ici :';
$string['studentview_sheet'] = 'Copie';
$string['studentview_corrected'] = 'Corrigé';

// OTHERS.
$string['modulename'] = 'QCM papier';
$string['modulenameplural'] = 'QCM papier';
$string['modulename_help'] = 'Le module QCM papier permet de créer des PDF pour imprimer des QCM puis de corriger automatiquement les réponses scannées.';
$string['automultiplechoice'] = 'QCM papier';
$string['pluginadministration'] = 'QCM papier - édition';
$string['pluginname'] = 'QCM papier';
$string['noautomultiplechoices'] = 'Aucune instance de QCM papier n\'est définie dans ce cours';
$string['automultiplechoice:addinstance'] = 'Créer un QCM papier';
$string['automultiplechoice:update'] = 'Modifier un QCM papier';
$string['automultiplechoice:view'] = 'Consulter un QCM ou sa copie';

$string['dashboard'] = 'Tableau de bord';
$string['documents'] = 'Sujets';
$string['uploadscans'] = 'Dépôt des copies';
$string['associating'] = 'Identification';
$string['grading'] = 'Notation';
$string['annotating'] = 'Correction';


// MOD_FORM
$string['instructionsheader'] = 'Rédaction de la consigne';
$string['automultiplechoicename'] = 'Nom du questionnaire';
$string['instructions'] = 'Consigne prédéfinie';
$string['description'] = 'Consigne';
$string['comment'] = 'Commentaire';
$string['qnumber'] = 'Nb. questions';
$string['score'] = 'Total des points';

$string['automultiplechoicename_help'] = 'Le nom complet du questionnaire';
$string['instructions_help'] = 'Le texte associé à cette consigne sera inséré au-dessus de la consigne personnalisée (champ suivant).';
$string['description_help'] = 'Le texte qui sera imprimé sur chaque questionnaire, contenant les consignes et la durée de l\'épreuve.
<br />A la création du questionnaire, le contenu de la consigne prédéfinie (champ précédent) sera inséré au début de la consigne,
et la consigne Attribution des points sera ajoutée à la fin.';
$string['comment_help'] = 'Un commentaire pour l\'auteur, qui ne sera pas imprimé.';
$string['qnumber_help'] = 'Le nombre de questions prévisionnel du questionnaire, pour validation.';
$string['score_help'] = 'Le score total du questionnaire (en points), pour validation.';

$string['modform_uselatexfile'] = 'Utiliser un fichier latex.';
$string['modform_uselatexfilelabel'] = 'Le fichier latex défini les paramètres AMC et du questionnaire.';
$string['modform_latexfile'] = 'Fichier Latex (*.tex).';




$string['amcparams'] = 'Paramètres AMC';
$string['amc_minscore'] = 'Note minimale';
$string['amc_copies'] = 'Nombre de versions';
$string['amc_questionsColumns'] = 'Nb. colonnes de questions';
$string['amc_questionsColumns_help'] = 'Si réglé à "Auto", les questions seront affichées sur deux colonnes quand elles sont nombreuses.';
$string['amc_shuffleq'] = 'Mélanger les questions';
$string['amc_shufflea'] = 'Mélanger les réponses';
$string['amc_separatesheet'] = 'Feuille réponses séparée';
$string['amc_answerSheetColumns'] = 'Nb. colonnes sur cette feuille';
$string['amc_grademax'] = 'Note finale maximale';
$string['amc_gradegranularity'] = 'Précision des notes';
$string['amc_graderounding'] = 'Arrondi des notes';
$string['anonymous'] = 'Copies anonymes';
$string['amc_lstudent'] = 'Consigne / n° d\'étudiant';
$string['amc_lname'] = 'Zone d\'identification';
$string['amc_lstudent_help'] = 'Texte affiché à côté de la grille qui permet de saisir son numéro d\'étudiant.';
$string['amc_lname_help'] = 'Intitulé du cadre affiché en haut à droite de la feuille de réponse, par exemple pour indiquer à l\'étudiant qu\'il doit écrire son nom.';
$string['amc_lstudent_default'] = "Veuillez coder votre numéro d'étudiant ci-contre, et écrire votre nom dans la case ci-dessous.";
$string['amc_lname_default'] = 'Nom et prénom';
$string['amc_markmulti'] = 'Marque pour réponses multiples';
$string['amc_markmulti_help'] = 'Un trèfle sera affiché quand une question a plusieurs bonnes réponses.';
$string['amc_score'] = 'Affichage du mode de barème';
$string['amc_score_help'] = 'Affiche le texte descriptif du barème sur le sujet.';

$string['amc_customlayout'] = 'Personnalisation de la mise en pages';
$string['amc_customlayout_help'] = 'Personnalise la mise en page du questionnaire en changeant les valeurs par défaut.';

$string['questionselect'] = 'Sélection des questions';
$string['questionselected'] = 'Questions choisies';
$string['sortmsg'] = 'Les questions sélectionnées peuvent être triées en les déplaçant à la souris.';
$string['qexpected'] = '{$a} questions attendues.';
$string['savesel'] = 'Enregistrer la sélection';
$string['qcategory'] = 'Catégorie de question';
$string['qtitle'] = 'Question';
$string['qscore'] = 'Points';
$string['amc_displaypoints'] = 'Montrer les points';
$string['scoringrules'] = 'Règles de calcul';
$string['scoringset'] = 'Attribution des points';
$string['scoringsystem'] = 'Barème';
$string['insertsection'] = 'Insérer une nouvelle section ici';

$string['editselection'] = 'Modifier la sélection de questions';

$string['validateql_wrong_number'] = 'Le nombre de questions n\'est pas celui attendu.';
$string['validateql_wrong_sum'] = 'La somme des points ne fait pas la note totale attendue.' ;
$string['validateql_wrong_score'] = 'Le nombre de points d\'au moins une question n\'est pas valide.';
$string['validate_positive_int'] = 'Ceci devrait être un nombre strictement positif.';
$string['validate_poszero_int'] = 'Ceci devrait être un nombre positif ou nul.';
$string['validate_under_maxscore'] = 'Ceci devrait être inférieur à la note maximale.';
$string['validateql_deletedquestions'] = 'Certaines questions ne sont pas présentes dans Moodle et ont probablement été supprimées.';
$string['validate_copies_without_shuffle'] = 'Avoir plusieurs versions sans mélanger questions ni réponses n\'a pas de sens.';

$string['prepare'] = 'Prévisualiser les PDF';
$string['prepare-locked'] = 'Télécharger les sujets';
$string['analyse'] = 'Dépôt des copies des étudiants';
$string['note'] = 'Notes et copies corrigées';
$string['export'] = 'Rapports';

$string['associationusermode'] = 'Affichage des &eacute;tudiants';
$string['associationmode'] = 'Mode d\'identification';

$string['unknown'] = 'Non identifiés';
$string['manual'] = 'Identification manuelle';
$string['auto'] = 'Identification automatique';

$string['without'] = 'Sans copie';
$string['selectuser'] = 'Choisir l\'étudiant';

$string['questionoperations'] = 'Avant de sélectionner des questions, vous pouvez enrichir la banque de questions par…';
$string['importfilequestions'] = 'Importer un fichier de questions';
$string['importquestions'] = 'Import/ Création de questions';
$string['importfilequestions'] = 'Import d\'un fichier de question';
$string['createquestions'] = "Création d'une question par formulaire";
