<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2008 Martin Hesse <mail@martin-hesse.info>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


class tx_mhbranchenbuch_realurl {

	/**
	 * Generates additional RealURL configuration and merges it with provided configuration
	 *
	 * @param	array		$params	Default configuration
	 * @param	tx_realurl_autoconfgen		$pObj	Parent object
	 * @return	array		Updated configuration
	 */
  function getConfig($params, &$pObj) {
    return array_merge_recursive($params['config'], array(
      'postVarSets' => array(
        '_DEFAULT' => array(
          'yellowpages' => array(
            array(
    					'GETvar' => 'tx_mhbranchenbuch_pi1[bid]',
    					'lookUpTable' => array(
    						'table' => 'tx_mhbranchenbuch_bundesland',
    						'id_field' => 'uid',
    						'alias_field' => 'name',
    						'addWhereClause' => ' AND NOT deleted',
    						'useUniqueCache' => 1,
    						'useUniqueCache_conf' => array(
    							'strtolower' => 1,
    							'spaceCharacter' => '-',
    						),
    					),
    				),
    				array(
    					'GETvar' => 'tx_mhbranchenbuch_pi1[lid]',
    					'lookUpTable' => array(
    						'table' => 'tx_mhbranchenbuch_landkreis',
    						'id_field' => 'uid',
    						'alias_field' => 'name',
    						'addWhereClause' => ' AND NOT deleted',
    						'useUniqueCache' => 1,
    						'useUniqueCache_conf' => array(
    							'strtolower' => 1,
    							'spaceCharacter' => '-',
    						),
    					),
    				),
    				array(
    					'GETvar' => 'tx_mhbranchenbuch_pi1[oid]',
    					'lookUpTable' => array(
    						'table' => 'tx_mhbranchenbuch_ort',
    						'id_field' => 'uid',
    						'alias_field' => 'name',
    						'addWhereClause' => ' AND NOT deleted',
    						'useUniqueCache' => 1,
    						'useUniqueCache_conf' => array(
    							'strtolower' => 1,
    							'spaceCharacter' => '-',
    						),
    					),
    				),
    				array(
    					'GETvar' => 'tx_mhbranchenbuch_pi1[kid]',
    					'lookUpTable' => array(
    						'table' => 'tx_mhbranchenbuch_kategorien',
    						'id_field' => 'uid',
    						'alias_field' => 'name',
    						'addWhereClause' => ' AND NOT deleted',
    						'useUniqueCache' => 1,
    						'useUniqueCache_conf' => array(
    							'strtolower' => 1,
    							'spaceCharacter' => '_',
    						),
    					),
    				),
          ),  
        )
      )
    ));
  }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mh_branchenbuch/lib/class.tx_mhbranchenbuch_realurl.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mh_branchenbuch/lib/class.tx_mhbranchenbuch_realurl.php']);
}