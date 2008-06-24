<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// class for displaying the category tree in BE forms.
include_once(t3lib_extMgm::extPath($_EXTKEY).'lib/class.tx_mhbranchenbuch_treeview.php');

t3lib_extMgm::allowTableOnStandardPages('tx_mhbranchenbuch_firmen');


t3lib_extMgm::addToInsertRecords('tx_mhbranchenbuch_firmen');

$TCA["tx_mhbranchenbuch_firmen"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen',		
		'label'     => 'firma',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'treeParentField' => 'root_uid',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mhbranchenbuch_firmen.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, kategorie, bundesland, landkreis, ort, firma, typ, adresse, zip, city, telefon, fax, handy, link, video, email, custom1, custom2, custom3, bild, keywords, detail, hit_count, job, cruser_id",
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_mhbranchenbuch_kategorien');


t3lib_extMgm::addToInsertRecords('tx_mhbranchenbuch_kategorien');

$TCA["tx_mhbranchenbuch_kategorien"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_kategorien',		
		'label'     => 'name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby'    => 'sorting',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',
		'treeParentField' => 'root_uid',
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mhbranchenbuch_kategorien.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, fe_group, name, image, description, root_uid",
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_mhbranchenbuch_bundesland');


t3lib_extMgm::addToInsertRecords('tx_mhbranchenbuch_bundesland');

$TCA["tx_mhbranchenbuch_bundesland"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_bundesland',		
		'label'     => 'name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'default_sortby' => "ORDER BY name",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mhbranchenbuch_bundesland.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, name",
	)
);

t3lib_extMgm::allowTableOnStandardPages('tx_mhbranchenbuch_landkreis');


t3lib_extMgm::addToInsertRecords('tx_mhbranchenbuch_landkreis');

$TCA["tx_mhbranchenbuch_landkreis"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_landkreis',		
		'label'     => 'name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY name",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mhbranchenbuch_ort.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, bundesland, name, detail",
	)
);

t3lib_extMgm::allowTableOnStandardPages('tx_mhbranchenbuch_ort');


t3lib_extMgm::addToInsertRecords('tx_mhbranchenbuch_ort');

$TCA["tx_mhbranchenbuch_ort"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_ort',		
		'label'     => 'name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY name",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mhbranchenbuch_ort.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, landkreis, name",
	)
);

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

#$GLOBALS['TCA']['tt_content']['ctrl']['requestUpdate'] .= ',displayFederalstates,displayAdministrative';

t3lib_extMgm::addPlugin(array('LLL:EXT:mh_branchenbuch/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Yellow Page");


if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_mhbranchenbuch_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_mhbranchenbuch_pi1_wizicon.php';

t3lib_extMgm::addStaticFile($_EXTKEY,'static/', 'Yellow Page (Static)');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:mh_branchenbuch/flexform_ds_pi1.xml');
?>
