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

    $settings->add(new admin_setting_configtext('amcdirectory',
		'Répertoire AMC',
        'Répertoire des projets utilisé par AMC',
		'',
		PARAM_TEXT));

	$settings->add(new admin_setting_configtext('amctemplate',
		'Modèle AMC',
        'Projet modèle AMC, dont dérivent les projets créés automatiquement (AMC-txt simple)',
		'',
		PARAM_TEXT));

	$settings->add(new admin_setting_configtext('amccodelength',
		'Longueur code',
        'Longueur du code étudiant pour l\'affichage AMC',
		'10',
		PARAM_INT));

}
