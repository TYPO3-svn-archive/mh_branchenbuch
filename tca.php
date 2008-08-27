<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_mhbranchenbuch_firmen"]["ctrl"]["requestUpdate"] = 'bundesland,landkreis';

$TCA["tx_mhbranchenbuch_firmen"] = array (
	"ctrl" => $TCA["tx_mhbranchenbuch_firmen"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,cruser_id,kategorie,bundesland,landkreis,ort,firma,forename,lastname,typ,adresse,zip,city,telefon,fax,handy,link,video,email,custom1,custom2,custom3,bild,keywords,detail,map_lat,map_lng,hit_count,job"
	),
	"feInterface" => $TCA["tx_mhbranchenbuch_firmen"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(0, 0, 0, 12, 31, 2020),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		'sys_language_uid' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => Array(
					Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
				)
			)
		),
		"cruser_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.owner",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
            Array("",0),
        ),
				"foreign_table" => "fe_users",	
				"foreign_table_where" => "AND fe_users.pid=###STORAGE_PID### ORDER BY fe_users.username",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"kategorie" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.kategorie",		
      'config' => Array (
				'type' => 'select',
				'form_type' => 'user',
				'userFunc' => 'tx_mhbranchenbuch_treeview->displayCategoryTree',
				'treeView' => 1,
				'treeName' => 'txchtreeviewexample',
        'treeMaxDepth' => 999,
				'size' => 10,
				'selectedListStyle' => 'width:150px',
				'minitems' => 0,
				'maxitems' => 100,
				'foreign_table' => 'tx_mhbranchenbuch_kategorien',
				'foreign_table_where' => 'AND tx_mhbranchenbuch_kategorien.pid=###CURRENT_PID### ORDER BY tx_mhbranchenbuch_kategorien.name',
				'wizards' => Array(
					'_PADDING' => 2,
					'_VERTICAL' => 1,
					'add' => Array(
						'type' => 'script',
						'title' => 'LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_kategorien.createNewParentCategory',
						'icon' => 'add.gif',
						'params' => Array(
							'table'=>'tx_mhbranchenbuch_kategorien',
							'pid' => '###CURRENT_PID###',
							'setValue' => 'set'
						),
						'script' => 'wizard_add.php',
					),
					'list' => Array(
						'type' => 'script',
						'title' => 'LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_kategorien.listCategories',
						'icon' => 'list.gif',
						'params' => Array(
							'table'=>'tx_mhbranchenbuch_kategorien',
							'pid' => '###CURRENT_PID###',
						),
						'script' => 'wizard_list.php',
					),
				),
      ),
		),
    "bundesland" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_ort.bundesland",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
            Array("",0),
        ),
				"foreign_table" => "tx_mhbranchenbuch_bundesland",	
				"foreign_table_where" => "AND tx_mhbranchenbuch_bundesland.pid=###CURRENT_PID### ORDER BY tx_mhbranchenbuch_bundesland.name",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"landkreis" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_landkreis.name",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
            Array("",0),
        ),
				"foreign_table" => "tx_mhbranchenbuch_landkreis",	
				"foreign_table_where" => "AND tx_mhbranchenbuch_landkreis.pid=###CURRENT_PID### AND tx_mhbranchenbuch_landkreis.bundesland=###REC_FIELD_bundesland### ORDER BY tx_mhbranchenbuch_landkreis.name",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
    "ort" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_ort.name",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
            Array("",0),
        ),
				"foreign_table" => "tx_mhbranchenbuch_ort",	
				"foreign_table_where" => "AND tx_mhbranchenbuch_ort.pid=###CURRENT_PID### AND tx_mhbranchenbuch_ort.landkreis=###REC_FIELD_landkreis### ORDER BY tx_mhbranchenbuch_ort.name",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"firma" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.firma",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"forename" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.forename",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
			)
		),
		"lastname" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.lastname",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
			)
		),
		"typ" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.typ",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
				  Array("LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.typ.I.7", "7"),
					Array("LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.typ.I.0", "0"),
					Array("LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.typ.I.1", "1"),
					Array("LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.typ.I.2", "2"),
					Array("LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.typ.I.3", "3"),
					Array("LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.typ.I.4", "4"),
					Array("LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.typ.I.5", "5"),
					Array("LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.typ.I.6", "6"),
				),
				"size" => 1,	
				"maxitems" => 1,
			)
		),
		"adresse" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.adresse",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),		
    "zip" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_ort.zip",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"city" => Array (
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_ort.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"telefon" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.telefon",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"fax" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.fax",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
    "handy" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.handy",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"link" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.link",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"video" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.video",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"email" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.email",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"custom1" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.custom1",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"custom2" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.custom2",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"custom3" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.custom3",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"bild" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.bild",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "gif,png,jpeg,jpg",	
				"max_size" => 1000,	
				"uploadfolder" => "uploads/tx_mhbranchenbuch",
				"show_thumbs" => 1,	
				"size" => 5,	
				"minitems" => 0,
				"maxitems" => 50,
			)
		),	
		"keywords" => Array (	
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.keywords",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		
		"detail" => Array (
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.detail",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"map_lat" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.map_lat",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
			)
		),
		"map_lng" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.map_lng",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
			)
		),
    "job" => Array (        
      "exclude" => 1,        
      "label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.job",        
      "config" => Array (
          "type" => "check",
      )
    ),
	 "hit_count" => Array (	
			"exclude" => 1,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.hit_count",		
			"config" => Array (
				"type" => "none",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, cruser_id, kategorie;;;;3-3-3, bundesland, landkreis, ort, firma, forename, lastname, typ, adresse, zip, city, telefon, fax, handy, link, video, email, custom1, custom2, custom3, bild, keywords, detail;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/mh_branchenbuch/rte/], map_lat, map_lng, hit_count, job")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime, sys_language_uid")
	)
);


$TCA["tx_mhbranchenbuch_kategorien"] = array (
	"ctrl" => $TCA["tx_mhbranchenbuch_kategorien"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,fe_group,name,root_uid,detail,image,description"
	),
	"feInterface" => $TCA["tx_mhbranchenbuch_kategorien"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"name" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_kategorien.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"root_uid" => Array (	
			"exclude" => 1,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_kategorien.root_uid",		
      'config' => Array (
				'type' => 'select',
				'form_type' => 'user',
				'userFunc' => 'tx_mhbranchenbuch_treeview->displayCategoryTree',
				'treeView' => 1,
				'treeName' => 'txchtreeviewexample',
        'treeMaxDepth' => 999,
				'size' => 10,
				'selectedListStyle' => 'width:150px',
				'minitems' => 0,
				'maxitems' => 2,
				'foreign_table' => 'tx_mhbranchenbuch_kategorien',
				'wizards' => Array(
					'_PADDING' => 2,
					'_VERTICAL' => 1,
					'add' => Array(
						'type' => 'script',
						'title' => 'LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_kategorien.createNewParentCategory',
						'icon' => 'add.gif',
						'params' => Array(
							'table'=>'tx_mhbranchenbuch_kategorien',
							'pid' => '###CURRENT_PID###',
							'setValue' => 'set'
						),
						'script' => 'wizard_add.php',
					),
					'list' => Array(
						'type' => 'script',
						'title' => 'LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_kategorien.listCategories',
						'icon' => 'list.gif',
						'params' => Array(
							'table'=>'tx_mhbranchenbuch_kategorien',
							'pid' => '###CURRENT_PID###',
						),
						'script' => 'wizard_list.php',
					),
				),
      ),
		),
		"image" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_kategorien.image",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "gif,png,jpeg,jpg",	
				"max_size" => 1000,	
				"uploadfolder" => "uploads/tx_mhbranchenbuch",
				"show_thumbs" => 1,	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),	
		"description" => Array (
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_kategorien.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, name, root_uid;;;;3-3-3, image, description;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/mh_branchenbuch/rte/]")
	),
	"palettes" => array (
		"1" => array("showitem" => "fe_group")
	)
);


$TCA["tx_mhbranchenbuch_bundesland"] = array (
	"ctrl" => $TCA["tx_mhbranchenbuch_bundesland"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,name,map_lat,map_lng"
	),
	"feInterface" => $TCA["tx_mhbranchenbuch_bundesland"]["feInterface"],
	"columns" => array (
		't3ver_label' => array (		
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_mhbranchenbuch_bundesland',
				'foreign_table_where' => 'AND tx_mhbranchenbuch_bundesland.pid=###CURRENT_PID### AND tx_mhbranchenbuch_bundesland.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"name" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_bundesland.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"map_lat" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.map_lat",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
			)
		),
		"map_lng" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.map_lng",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, name, map_lat, map_lng")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);

$TCA["tx_mhbranchenbuch_landkreis"] = array (
	"ctrl" => $TCA["tx_mhbranchenbuch_landkreis"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,bundesland,name,detail,map_lat,map_lng"
	),
	"feInterface" => $TCA["tx_mhbranchenbuch_landkreis"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"bundesland" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_ort.bundesland",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_mhbranchenbuch_bundesland",	
				"foreign_table_where" => "AND tx_mhbranchenbuch_bundesland.pid=###CURRENT_PID### ORDER BY tx_mhbranchenbuch_bundesland.name",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_landkreis.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"detail" => Array (
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.detail",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"map_lat" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.map_lat",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
			)
		),
		"map_lng" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.map_lng",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, bundesland, name, detail;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/mh_branchenbuch/rte/], map_lat, map_lng")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);

$TCA["tx_mhbranchenbuch_ort"] = array (
	"ctrl" => $TCA["tx_mhbranchenbuch_ort"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,landkreis,name,zip,detail,map_lat,map_lng"
	),
	"feInterface" => $TCA["tx_mhbranchenbuch_ort"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"landkreis" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_landkreis.name",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_mhbranchenbuch_landkreis",	
				"foreign_table_where" => "AND tx_mhbranchenbuch_landkreis.pid=###CURRENT_PID### ORDER BY tx_mhbranchenbuch_landkreis.name",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_ort.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
				"eval" => "required",
			)
		),
		"zip" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_ort.zip",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
				"eval" => "required",
			)
		),
		"detail" => Array (
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.detail",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"map_lat" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.map_lat",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
			)
		),
		"map_lng" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mh_branchenbuch/locallang_db.xml:tx_mhbranchenbuch_firmen.map_lng",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, landkreis, name, zip, detail;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/mh_branchenbuch/rte/], map_lat, map_lng")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);
?>
