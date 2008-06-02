<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_mhbranchenbuch_firmen=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_mhbranchenbuch_kategorien=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_mhbranchenbuch_bundesland=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_mhbranchenbuch_ort=1
');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_mhbranchenbuch_pi1 = < plugin.tx_mhbranchenbuch_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_mhbranchenbuch_pi1.php','_pi1','list_type',1);

// RealURL-AutoConf
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/realurl/class.tx_realurl_autoconfgen.php']['extensionConfiguration'][$_EXTKEY] = 'EXT:mh_branchenbuch/class.tx_mhbranchenbuch_realurl.php:tx_mhbranchenbuch_realurl->getConfig';

// Widget
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/mydashboard/class.tx_mydashboard_widgetmgm.php']['addWidget']['mhbranchenbuch_overview'] = 'EXT:mh_branchenbuch/widgets/class.tx_mhbranchenbuch_overview.php:tx_mhbranchenbuch_overview';
?>
