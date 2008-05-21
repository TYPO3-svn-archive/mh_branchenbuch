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

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once('class.tx_mhbranchenbuch_ts.php');

/**
 * Plugin 'Branchenbuch' for the 'mh_branchenbuch' extension.
 *
 * @author	Martin Hesse <mail@martin-hesse.info>
 * @package	TYPO3
 * @subpackage	tx_mhbranchenbuch
 */
 
class tx_mhbranchenbuch_pi1 extends tslib_pibase {
	var $prefixId        = 'tx_mhbranchenbuch_pi1';		// Same as class name
	var $scriptRelPath   = 'pi1/class.tx_mhbranchenbuch_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey          = 'mh_branchenbuch';	// The extension key.
	#var $pi_checkCHash   = true;
	#var $pi_USER_INT_obj = true;
	
	// This table save all company entries
	var $dbTable1        = 'tx_mhbranchenbuch_firmen';
	// This table save all categories
  var $dbTable2       = 'tx_mhbranchenbuch_kategorien';
  // This table save all federal states
  var $dbTable3       = 'tx_mhbranchenbuch_bundesland';
  // This table save all administrative districts
  var $dbTable4       = 'tx_mhbranchenbuch_landkreis';
  // This table save all cities
  var $dbTable5       = 'tx_mhbranchenbuch_ort';
  // This table save all clicks on a banner rotation
  var $dbTable6       = 'tx_mhbranchenbuch_ip';
  
  var $template;
  var $id;
  var $freeCap = FALSE;
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		$this->tsArray = t3lib_div::makeInstance('tx_mhbranchenbuch_ts');
		
    #$GLOBALS["TSFE"]->set_no_cache();
    
    // Flexformdaten beziehen ...
    $this->pi_initPIflexForm();
    
    $this->lConf = array(); // Setup our storage array...
    
    // Assign the flexform data to a local variable for easier access
    $piFlexForm = $this->cObj->data['pi_flexform'];
    
    // Traverse the entire array based on the language...
    // and assign each configuration option to $this->lConf array...
    if($piFlexForm) {
      foreach ( $piFlexForm['data'] as $sheet => $data ) {
        foreach ( $data as $lang => $value ) {
          foreach ( $value as $key => $val ) {
            $this->lConf[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
          }
        }
      }
    }
    
    // Init template
    $ts_tpl        = $this->cObj->fileResource($this->conf['templateFile']);
    $flexform_tpl  = $this->cObj->fileResource('uploads/tx_mhbranchenbuch/' . $this->lConf['template_file']);
     
    $this->template = isset($flexform_tpl) ? $flexform_tpl : $ts_tpl;
    
    // No template available? put out a error
    if (!$this->template) {
    	return '<h1>Error</h1><p>No Template found for ' . $this->extKey . '</p><p>Notice: <ul><li>to change your template path in the setup use:<br /><b>plugin.tx_mhbranchenbuch_pi1.templateFile</b> = path/to/template.html</li><li>you can use the default-template by adding to your root-template (include_static) "Branchenbuch (Static) ..".<br /><b>It is highly recommend to do that</b>!</li></ul></p>';
    }
    
    // Init PageId
    $this->id = $GLOBALS['TSFE']->id;

    // Stores the modul which is called
    $modul = $this->conf['code'] != '' ? array($this->conf['code']) : explode(',',$this->lConf['what_to_display']);
    
    // TS "pid_list"
    $pid = $this->pi_getPidList($this->cObj->data['pages'],$this->cObj->data['recursive']);
    $this->pid = $pid;
    
    // Get the TS-Vars, TS-Vars in the flexform has a higher priority
    $tsVarArray = $this->tsArray->getArray();
      
    foreach($tsVarArray AS $temp_tsVar) {
      $this->lConf[$temp_tsVar] ? $this->$temp_tsVar = $this->lConf[$temp_tsVar] : $this->$temp_tsVar = trim($this->cObj->stdWrap($this->conf[$temp_tsVar],$this->conf[$temp_tsVar.'.']));
    }
    
    // Stores the categories
    $catId = $this->lConf['display_categories'];
    
    // Special LIST-Options:
    $listFederal  = $this->lConf['displayFederalstates'];
    $listAdminis  = $this->lConf['displayAdministrative'];
    $listCity     = $this->lConf['displayCities'];
    
    // Calls the special methode for the display type
    foreach($modul AS $temp) {
      switch ($temp) {
        case 'LIST':
          $content .= $this->displayAll($pid,$catId,$listFederal,$listAdminis,$listCity);
        break;
        case 'SEARCH':
          $content .= $this->displaySearch($pid);
        break;
        case 'LATEST':
          $content .= $this->displayLatest($pid,$catId);
        break;
        case 'TAGCLOUD':
          $content .= $this->displayTagCloud($pid);
        break;
        case 'SINGLE':
          $content .= $this->displaySingle($pid,$catId);
        break;
        case 'STATISTICS':
          $content .= $this->displayStats();
        break;
        case 'ADD-FORM':
          $content .= $this->displayFEForm($pid);
        break;
        case 'EDIT-FORM':
          $content .= $this->listEntries($pid);
        break;
        case 'OVERVIEW':
          $content .= $this->initOverview($pid,$catId);
        break;
        case 'ROTATION':
          $GLOBALS["TSFE"]->set_no_cache();
          $content .= $this->displayRotation($pid);
        break;
        case 'ALPHABETICAL-MENU':
          $content .= $this->displayMenu($pid,$catId);
        break;
      }
    }
    
    // Put all out
    return $this->pi_wrapInBaseClass($content);
	}
	
	
	
	/**
	 * Display all Entries of a selected category
	 *
	 * @param int $pid: PageId 
	 * @param string $catId: list of categories that should displayed
	 *	 
	 * @return	The content that is displayed on the website
	 */
	function displayAll($pid,$catId = FALSE,$listFederal = FALSE,$listAdminis = FALSE,$listCity = FALSE) { 

    if(strlen($catId) > 0)        { $catId        = explode(',',$catId); }
    if(strlen($listFederal) > 0)  { $listFederal  = explode(',',$listFederal); }
    if(strlen($listAdminis) > 0)  { $listAdminis  = explode(',',$listAdminis); }
    if(strlen($listCity) > 0)     { $listCity     = explode(',',$listCity); }
    
    $i        = 0; #init
    $i2       = 0; #init
    $i3       = 0; #init
    $i4       = 0; #init
    $query    = FALSE; #init
    $c_query  = FALSE; #init
    
    // Add Categories to query ...
    if($catId) {
      $query    .= 'AND ';
      $c_query  .= 'AND ';
      
      foreach($catId AS $value) {
        if($i>0) { $query.= ' OR '; $c_query.= ' OR '; $i=0; }
        $query    .= 'FIND_IN_SET(' . $value . ',f.kategorie)';
        $c_query  .= 'FIND_IN_SET(' . $value . ',kategorie)';
        $i++;
      }
    }
    
    // Add Federal States to query ...
    if($listFederal) {
      $query    .= 'AND ';
      $c_query  .= 'AND ';
      
      foreach($listFederal AS $federal) {
        if($i2>0) { $query.= ' OR '; $c_query.= ' OR '; $i2=0; }
        $query    .= 'FIND_IN_SET(' . $federal . ',f.bundesland)';
        $c_query  .= 'FIND_IN_SET(' . $federal . ',bundesland)';
        $i2++;
      }
    }
    
    // Add Administrative Districts to query ...
    if($listAdminis) {
      $query    .= 'AND ';
      $c_query  .= 'AND ';
      
      foreach($listAdminis AS $adminis) {
        if($i3>0) { $query.= ' OR '; $c_query.= ' OR '; $i3=0; }
        $query    .= 'FIND_IN_SET(' . $adminis . ',f.landkreis)';
        $c_query  .= 'FIND_IN_SET(' . $adminis . ',landkreis)';
        $i3++;
      }
    }
    
    // Add Cities to query ...
    if($listCity) {
      $query    .= 'AND ';
      $c_query  .= 'AND ';
      
      foreach($listCity AS $city) {
        if($i4>0) { $query.= ' OR '; $c_query.= ' OR '; $i4=0; }
        $query    .= 'FIND_IN_SET(' . $city . ',f.ort)';
        $c_query  .= 'FIND_IN_SET(' . $city . ',ort)';
        $i4++;
      }
    }
    
    /* PAGEBROWSER INIT */
    $enableFields = $this->cObj->enableFields($this->dbTable1);
    
    $res_c = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
      'uid', 
      $this->dbTable1,
      '`pid` IN(' . $pid . ') ' . $c_query . ' ' . $enableFields
    );
    
    $count = $GLOBALS['TYPO3_DB']->sql_num_rows($res_c);
    
    if (!isset($this->piVars['page'])) $this->piVars['page'] = 0;
    $limit = $this->piVars['page'] * $this->resultsPerPage . "," . $this->resultsPerPage;
    
    $pageBrowser = array(
      'pid'   => $pid,
      'limit' => $limit,
      'page'  => $this->piVars['page'],
      'table' => $this->dbTable1,
      'count' => $count,
    );
    
    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
        SELECT
          f.*,
          k.name AS kname
        FROM
          " . $this->dbTable1 . " f
          RIGHT JOIN " . $this->dbTable2 . " k ON k.uid = f.kategorie
        WHERE
          f.deleted = 0
          AND
          f.hidden  = 0
          AND
          f.pid IN (" . $pid . ")
          " . $query . "
        ORDER BY
          f.firma ASC
        LIMIT " . $limit . "
      ");
    
    return $this->getItem($res,TRUE,'',$pageBrowser);
  }
  
  
  
  /**
  * Displays a searchbox
  *
  * @param int $pid: PageId  
  *  
  * @return	The content that is displayed on the website
  */
  function displaySearch($pid) {
    $markerArray          = array();
    $wrappedSubpartArray  = array();
    
    $keyword              = t3lib_div::_GP('keyword');
    $keyword2             = t3lib_div::_GP('keyword2');
    
    $selectBox            = t3lib_div::_GP('tx_mh_branchenbuch_postVar');
   
    $bundesland           = $this->piVars['bid'];
    $landkreis            = $this->piVars['lid'];
    $ort                  = $this->piVars['oid'];
    
    $sTable               = explode(',',$this->search_tables);
    $sTableCount          = count($sTable);
    
    // Some language
    $markerArray['###LANG_SEARCH_WHO###']     = $this->pi_getLL('search_who');
    $markerArray['###LANG_SEARCH_WHERE###']   = $this->pi_getLL('search_where');
    $markerArray['###LANG_SEARCH_SUBMIT###']  = $this->pi_getLL('search_submit');
    
    $template = $this->cObj->getSubpart($this->template,"###SEARCHBOX###");
    
    // if keyword2 (where) is just active ....
    if($keyword == "" && $keyword2 != "" && $keyword2 != "%") {
      
      $tempKeyword2 = mysql_real_escape_string(htmlentities(trim($keyword2)));
        
      // Sucht anhand von Keyword2 nach moeglichen Städte ...
      $getCity      = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
        SELECT
          *
        FROM
          " . $this->dbTable5 . "
        WHERE
          name LIKE '%" . $tempKeyword2 . "%'
      ");
      
      if($GLOBALS['TYPO3_DB']->sql_num_rows($getCity)) {
        $inCity = array(); #init
        while($cRow = mysql_fetch_assoc($getCity)) {
          $inCity[] = $cRow['uid']; 
        }
      }
      
      // Sucht anhand von Keyword2 nach moeglichen Landkreisen ...
      $getAdminsitrativeDistrict  = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
        SELECT
          *
        FROM
          " . $this->dbTable4 . "
        WHERE
          name LIKE '%" . $tempKeyword2 . "%'
      ");
      
      if($GLOBALS['TYPO3_DB']->sql_num_rows($getAdminsitrativeDistrict)) {
        $inAdminDistrict = array(); #init
        while($lRow = mysql_fetch_assoc($getAdminsitrativeDistrict)) {
          $inAdminDistrict[] = $lRow['uid']; 
        }
      }
      
      $lookKeyword2 = ''; #init
      $lookKeyword2 .= count($inCity) > 0 ? " AND FIND_IN_SET(f.ort,'" . implode(',',$inCity) . "') "  : '';
      $lookKeyword2 .= count($inAdminDistrict) > 0 ? " AND FIND_IN_SET(f.landkreis,'" . implode(',',$inAdminDistrict) . "') "  : '';
      
      $showXS = $this->search_showXS == 1 ? '' : ' AND f.typ != 7';

      $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
        SELECT
          f.*,
          k.name AS kname
        FROM
          " . $this->dbTable1 . " f
          JOIN " . $this->dbTable2 . " k ON k.uid = f.kategorie
        WHERE
          f.pid IN ($pid)
          " . $showXS . "
          " . $lookKeyword2 . "
        ORDER BY
          f.firma ASC
      ");

      if($GLOBALS['TYPO3_DB']->sql_num_rows($res)) 
      {
        $markerArray['###SEARCHRESULT###']  = $this->getItem($res,TRUE,'',$pageBrowser); 
      } 
      else 
      {
        $markerArray['###SEARCHRESULT###']  = $this->pi_getLL('search_not_found');
      }   
      
      $markerArray['###VALUE_SEARCH###']    = t3lib_div::_GP('keyword');
      $markerArray['###VALUE_SEARCH2###']   = t3lib_div::_GP('keyword2');
      $markerArray['###ACTION_URI###']      = 'index.php?id=' . $this->search_pid . '&amp;no_cache=1';
      
    }
    // if keyword (who) is active (AND/OR)
    elseif ($keyword != "" && $keyword != "%" && strlen($keyword) > 2) 
    {
      $keyword      = explode(' ',$keyword);
      $cPVar        = count($keyword);
      $query        = ''; #init
      $i            = 0; #init
      $c            = 0; #init

      if(strlen($keyword2) > 2) {
        $tempKeyword2 = mysql_real_escape_string(htmlentities(trim($keyword2)));
        
        // Sucht anhand von Keyword2 nach moeglichen Staedte ...
        $getCity      = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
          SELECT
            *
          FROM
            " . $this->dbTable5 . "
          WHERE
            name LIKE '%" . $tempKeyword2 . "%'
        ");
        
        if($GLOBALS['TYPO3_DB']->sql_num_rows($getCity)) {
          $inCity = array(); #init
          while($cRow = mysql_fetch_assoc($getCity)) {
            $inCity[] = $cRow['uid']; 
          }
        }
        
        // Sucht anhand von Keyword2 nach moeglichen Landkreisen ...
        $getAdminsitrativeDistrict  = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
          SELECT
            *
          FROM
            " . $this->dbTable4 . "
          WHERE
            name LIKE '%" . $tempKeyword2 . "%'
        ");
        
        if($GLOBALS['TYPO3_DB']->sql_num_rows($getAdminsitrativeDistrict)) {
          $inAdminDistrict = array(); #init
          while($lRow = mysql_fetch_assoc($getAdminsitrativeDistrict)) {
            $inAdminDistrict[] = $lRow['uid']; 
          }
        }
        
      }
      
      $lookKeyword2 = ''; #init
      $lookKeyword2 .= count($inCity) > 0 ? " AND FIND_IN_SET(f.ort,'" . implode(',',$inCity) . "') "  : '';
      $lookKeyword2 .= count($inAdminDistrict) > 0 ? " AND FIND_IN_SET(f.landkreis,'" . implode(',',$inAdminDistrict) . "') "  : '';
    	
      foreach($keyword AS $string) {
    		If($i > 0) {
          If($cPVar>1) { 
            $query .= ' AND ';  
          } else {
            $query .= ' OR ';
          }
          $i=0; 
        }
        foreach($sTable AS $search_table) {
          if($c<($sTableCount*$cPVar)) {
            $query .= "f.$search_table LIKE '%$string%' OR ";
            $c++; 
          }
        }
        $query = substr($query,0,strlen($query)-strlen(' OR '));
    		$i++;
    	}
    	
      $query2 = explode('AND',$query);
      
      $x = 1; #init;
      $xMax = count($query2);
      foreach($query2 AS $tquery) {
        $new_query .= '(' . $tquery . ')';
        if($x>0 && $x<$xMax) {
          $new_query .= ' AND ';
        }
        $x++;
      }
      
      $showXS = $this->search_showXS == 1 ? '' : ' AND f.typ != 7';

      $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
        SELECT
          f.*,
          k.name AS kname
        FROM
          " . $this->dbTable1 . " f
          JOIN " . $this->dbTable2 . " k ON k.uid = f.kategorie
        WHERE
          f.pid IN (" . $pid . ")
          AND
          (" . $new_query . ")
          " . $showXS . "
          " . $lookKeyword2 . "
        ORDER BY
          f.firma ASC
      ");

      if($GLOBALS['TYPO3_DB']->sql_num_rows($res)) 
      {
        $markerArray['###SEARCHRESULT###']  = $this->getItem($res,TRUE,'',$pageBrowser); 
      } 
      else 
      {
        $markerArray['###SEARCHRESULT###']  = $this->pi_getLL('search_not_found');
      }   
      
      $markerArray['###VALUE_SEARCH###']    = t3lib_div::_GP('keyword');
      $markerArray['###VALUE_SEARCH2###']   = t3lib_div::_GP('keyword2');
      $markerArray['###ACTION_URI###']      = 'index.php?id=' . $this->search_pid . '&amp;no_cache=1';
    }
    // if booth empty
    else 
    {
      $markerArray['###ACTION_URI###']      = 'index.php?id=' . $this->search_pid . '&amp;no_cache=1';
      $markerArray['###SEARCHRESULT###']    = $this->pi_getLL('search_default');
      $markerArray['###VALUE_SEARCH###']    = '';
      $markerArray['###VALUE_SEARCH2###']   = '';
    }
    
    return $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray);
  }



  /**
  * Get the latest entrys
  *
  * @param int $pid: PageId 
  *    
  * @return	The content that is displayed on the website
  */
  function displayLatest($pid,$catId) {    
  
    if(strlen($catId) > 0) { $catId  = explode(',',$catId); } else { $catId = FALSE; }
    
    $i        = 0; #init
    $query    = ''; #init
    
    if($catId) {
      $query  = 'AND ';
      foreach($catId AS $value) {
        if($i>0) { $query.= ' OR '; $i=0; }
        $query    .= 'FIND_IN_SET(' . $value . ',f.kategorie)';
        $i++;
      }
    } else {
      $query    = FALSE;
    }
    
    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
      SELECT
        f.*,
        k.name AS kname
      FROM
        " . $this->dbTable1 . " f
        LEFT JOIN " . $this->dbTable2 . " k ON k.uid = f.kategorie
      WHERE
        f.deleted = 0
        AND
        f.hidden  = 0
        AND
        f.pid IN (" . $pid . ")
        " . $query . "
      ORDER BY
        f.crdate DESC
        LIMIT " . $this->limitLatest);
        
    return $this->getItem($res,TRUE);
  }
  
  
  
  /**
  * Displays a cool TagCloud!
  * Special thanks to Inbreed :-)
  *
  * @param int $pid: PageId   
  *  
  * @return	a cool tagcloud
  */
  function displayTagCloud($pid) {
      
    $markerArray          = array();
    $wrappedSubpartArray  = array();
    
    $bundesland   = $this->piVars['bid'];
    $landkreis    = $this->piVars['lid'];
    $ort          = $this->piVars['oid'];
    $kategorie    = $this->piVars['kid'];
    
    $search = ''; #init
    if($kategorie) {
      $search = ' AND ort = ' . $ort . ' AND landkreis = ' . $landkreis  . ' AND bundesland = ' . $bundesland;
    } elseif ($ort) {
      $search = ' AND ort = ' . $ort . ' AND landkreis = ' . $landkreis  . ' AND bundesland = ' . $bundesland;
    } elseif ($landkreis) {
      $search = ' AND landkreis = ' . $landkreis  . ' AND bundesland = ' . $bundesland;  	
    } elseif($bundesland) {
      $search = ' AND bundesland = ' . $bundesland;
    }
    
    $template = $this->cObj->getSubpart($this->template,"###TAGCLOUD###");
    
    $res    = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
      SELECT 
        k.name,
        k.uid,
        COUNT(*) AS anzahl
      FROM 
        " . $this->dbTable1 . " f
      LEFT JOIN 
        " . $this->dbTable2 . " k ON FIND_IN_SET(k.uid, f.kategorie)
      WHERE 
        f.pid IN (" . $pid . ")
        " . $search . "
      AND
        f.hidden = 0
      AND
        f.deleted = 0
      AND
        f.pid IN (" . $pid . ")
      GROUP BY 
        k.uid 
      ORDER BY 
        RAND()
    ");
    
    $tagCloud = array(); #init
    
    if($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
      while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
        if($row['deleted'] == '1' OR $row['hidden'] == '1') continue;
        $tagCloud[htmlentities($row['name'])] = intval($row['anzahl']) . ',' . intval($row['uid']);
      }
    }
    
    $maxCloud = @max($tagCloud);
    $minCloud = @min($tagCloud);
   
    $color    = explode(',',$this->CloudColors); 
    $cMax     = count($color);
    
    $div      = $maxCloud/$this->maxFontSize;
    
    function make_seed() {
      list($usec, $sec) = explode(' ', microtime());
      return (float) $sec + ((float) $usec * 100000);
    }
        
    if(isset($tagCloud)) {
      $content = ''; #init
      foreach($tagCloud AS $cat => $c) {
      
        $getCountAndUid = explode(',',$c);
        
        $catLink = $this->pi_linkTP($cat ,array($this->prefixId . '[cat]' => $getCountAndUid[1]),1,$this->single_pid);
        
        $t_c = $getCountAndUid[0]; #sum of the entries in the tagcloud
        srand(make_seed());
        $c/=$div; 
        
        // minFontSize & maxFontSize
        if($c < $this->minFontSize) $c = $this->minFontSize;
        if($c > $this->maxFontSize) $c = $this->maxFontSize;
        
        // TagCloudLink
        if($this->TagCloudLink == 1) {
          $linkParams = array(
            'style' => 'color:'.$color[rand(0,$cMax)].';font-size:'.$c.'px'
          );
          $cat = '<nobr>' . $this->cObj->addParams($catLink,$linkParams) . '</nobr>'; 
        } else {
          $cat = '<span style="color:'.$color[rand(0,$cMax)].';font-size:'.$c.'px"><nobr>' . $cat . '</nobr></span>';
        }
        
        if($this->countEntrys == 1) {
          $markerArray['###TAGCLOUD_ENTRIES###'] = $cat . ' <span class="tx-mh_branchenbuch_tagcloudCount">(' . $t_c . ')</span>';
        } else {
          $markerArray['###TAGCLOUD_ENTRIES###'] = $cat;
        }
        
        $content .= $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray);
      
      }
    } else {
      $markerArray['###TAGCLOUD_ENTRIES###']  = $this->cObj->stdWrap($this->pi_getLL('tagcloud_errorCat'),$this->conf['TagCloud_stdWrap.']);
      $content = $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray);
    }
    return $content;
  }
  
  
  
  /**
  * Displays a Banner-Rotation
  *
  * @param int $pid: PageId  
  *  
  * @return	The content that is displayed on the website
  */
  function displayRotation($pid) {
    
    $content              = ''; #init
    
    $markerArray          = array();
    $wrappedSubpartArray  = array();
    
    $template = $this->cObj->getSubpart($this->template,"###ROTATION###");
    
    $excludeList  = explode(',', $this->rotationExclude);
    $query        = ''; #init
    
    if($this->rotationExclude != "" && is_array($excludeList)) {
      foreach($excludeList AS $excludeId) {
        $query .= 'AND typ != ' . $excludeId . ' ';
      }
    } elseif ($this->rotationExclude != "") {
      $query = 'AND typ != ' . $this->rotationExclude;
    }
    
    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
      SELECT 
        *
      FROM
        " . $this->dbTable1 . "
      WHERE
        deleted = 0
        AND
        hidden = 0
        AND
        bild != ''
        AND
        pid IN (" . $pid . ")
        " . $query . "
      ORDER BY
        RAND()
      LIMIT " . $this->rotationLimit
      );
    
    /*
    if($this->rotationLimit > 1) {
      $GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] = '<script src="'.t3lib_extMgm::extRelPath('mh_branchenbuch').'res/SimpleSlide.js" type="text/javascript"></script>';
    }*/
    
    if($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
      $i = 0; #init
      while($row = mysql_fetch_array($res)) {
        $file                         = 'uploads/tx_mhbranchenbuch/' . $row['bild'];
        $imgTSConfig                  = Array();
        $imgTSConfig['file']          = $file;
        $imgTSConfig['file.']['maxW'] = $this->imgMaxWidth;
        $imgTSConfig['file.']['maxH'] = $this->imgMaxHeight;
        $imgTSConfig['altText']       = $row['firma'];
        $imgTSConfig['titleText']     = $row['firma'];
        $imgTSConfig['params']        = 'class="tx_mhbranchenbuch-image"';
        
        $linkParams = array(
          'target' => $this->rotationTarget
        );
        
        if($this->rotationLimit > 1) {
          $content .= $this->cObj->addParams($this->pi_linkTP($this->cObj->IMAGE($imgTSConfig),array($this->prefixId.'[rotation]'=> $row['uid']),0,$this->single_pid),$linkParams);   
          $content .= $i<$this->rotationLimit ? '<br />' : FALSE;
        } else {
          $content = $this->cObj->addParams($this->pi_linkTP($this->cObj->IMAGE($imgTSConfig),array($this->prefixId.'[rotation]'=> $row['uid']),0,$this->single_pid),$linkParams);
        }
        $i++;
      }
    } else {
      $content = $this->pi_getLL('error_rotation');
    }

    $markerArray['###IMAGE###'] = $content;
    
    return $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray);
  }
  
  
  
  /**
  * Displays a Alphabetical-Menu
  *
  * @param int $pid: PageId  
  * @param int $catId: CategoryId  
  *  
  * @return	The content that is displayed on the website
  */
  function displayMenu($pid,$catId = FALSE) {
    
    // Categories
    if(strlen($catId) > 0) { $catId  = explode(',',$catId); } else { $catId = FALSE; }
    
    $i        = 0; #init
    $query    = ''; #init
    
    if($catId) {
      $query  = 'AND ';
      foreach($catId AS $value) {
        if($i>0) { $query.= ' OR '; $i=0; }
        $query    .= 'FIND_IN_SET(' . $value . ',kategorie)';
        $i++;
      }
    } else {
      $query    = FALSE;
    }
    
    
    $menu_temp            = '<ul id="mhbranchenbuch_lettermenu">'; #init
    
    $markerArray          = array();
    $wrappedSubpartArray  = array();
    
    $postVarLetter        = $this->piVars['letter'];
    
    $template = $this->cObj->getSubpart($this->template,"###ALPHABETICAL_MENU###");
    
    $markerArray['###LANG_CHOOSE_LETTER###'] = $this->pi_getLL('choose_letter');
    
    $alphabetic = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
    
    foreach($alphabetic AS $letter) {
      $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
        SELECT 
          firma
        FROM
          " . $this->dbTable1 . "
        WHERE
          deleted = 0
        AND
          hidden = 0
        AND
          pid IN (" . $pid . ")
        AND
          substring(firma,1,1) = '" . $letter . "'
      ");

      if(@$GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
        $cssStyle = ($letter == $postVarLetter) ? 'mhbranchenbuch_letter_act' : 'mhbranchenbuch_letter';
        $menu_temp .= '<li class="' . $cssStyle . '">' . $this->pi_linkTP($this->cObj->stdWrap($letter,$this->conf['letter.']), array($this->prefixId . '[letter]' => $letter),1,$this->id) . '</li>';
      } else {
        $menu_temp .= '<li class="mhbranchenbuch_letter">' . $this->cObj->stdWrap($letter,$this->conf['emptyLetter.']) . '</li>';
      }
    }
    $menu_temp .= '<li class="mhbranchenbuch_letter">' . $this->pi_linkTP($this->cObj->stdWrap($this->letterAll,$this->conf['letter.']), array($this->prefixId . '[letter]' => 'all'),1,$this->id) . '</li>';
    $menu_temp .= '</ul>';
    
    $markerArray['###MENU###'] = $menu_temp;
    
    if(in_array($postVarLetter,$alphabetic) OR $postVarLetter == 'all') {
      
      $subStr = ($postVarLetter == 'all') ? '' : 'AND substring(f.firma,1,1) = "' . $postVarLetter . '"';
      
      /* PAGEBROWSER INIT */
      $enableFields = $this->cObj->enableFields($this->dbTable1);
      
      $res_c = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
        'uid', 
        $this->dbTable1,
        '`pid` IN(' . $pid . ') ' . $query . ' AND substring(firma,1,1) = "' . $postVarLetter . '" ' . $enableFields
      );
      
      $count = $GLOBALS['TYPO3_DB']->sql_num_rows($res_c);
      
      if (!isset($this->piVars['page'])) $this->piVars['page'] = 0;
      $limit = $this->piVars['page'] * $this->resultsPerPage . "," . $this->resultsPerPage;
      
      $pageBrowser = array(
        'pid'   => $pid,
        'limit' => $limit,
        'page'  => $this->piVars['page'],
        'table' => $this->dbTable1,
        'count' => $count,
      );
      
      $res2 = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
        SELECT
          f.*,
          k.name AS kname
        FROM
          " . $this->dbTable1 . " f
          LEFT JOIN " . $this->dbTable2 . " k ON k.uid = f.kategorie
        WHERE
          f.deleted = 0
        AND
          f.hidden  = 0
        AND
          f.pid IN (" . $pid . ")
          $subStr
          $query
        ORDER BY
          f.firma
      ");
      
      $markerArray['###RESULTS###'] = $this->getItem($res2,TRUE,'',$pageBrowser);
    } else {
      $markerArray['###RESULTS###'] = '';
    }
    return $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray);
  }
  
  
  
  /**
  * The dataoutput
  *
  * @param array $res: Database-Query
  * @param boolean $full: Let it TRUE, other is in work
  * @param boolean $detail: TRUE = SingleView (other Templatemarker)  
  * @param array $pageBrowser: conf for Pagebrowser  
  *       
  * @return	The content that is displayed on the website
  */
  function getItem($res, $full = FALSE, $detail = FALSE, $pageBrowser = FALSE) {

    if($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
      while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
        
        // Versteckte & gelöschte Elemente werden übersprungen ...
        if($row['deleted'] == '1' OR $row['hidden'] == '1') continue;
        
        if($detail) {
        
          $template = $this->cObj->getSubpart($this->template,"###$detail###");
          
        } else {
          
          // Filtert die 8 Anzeige-Methoden raus und 
          // bindet dann das erforderliche Template ein
          switch($row['typ']) {
            case '0':
              $template = $this->cObj->getSubpart($this->template,"###S_ENTRY###");
            break;
            
            case '1':
              $template = $this->cObj->getSubpart($this->template,"###M_ENTRY###");
            break;
            
            case '2':
              $template = $this->cObj->getSubpart($this->template,"###L_ENTRY###");
            break;
            
            case '3':
              $template = $this->cObj->getSubpart($this->template,"###XL_ENTRY###");
            break;
            
            case '4':
              $template = $this->cObj->getSubpart($this->template,"###XXL_ENTRY###");
            break;
            
            case '5':
              $template = $this->cObj->getSubpart($this->template,"###XXL_ENTRY_2###");
            break;
            
            case '6':
              $template = $this->cObj->getSubpart($this->template,'###ADVERTISE_ENTRY###');
            break;
            
            case '7':
              $template = $this->cObj->getSubpart($this->template, '###XS_ENTRY###');
            break;
            
          }
        }
        
        // Some language
        $markerArray['###LANG_ENTRY_DETAIL###'] = $this->pi_getLL('entry_detail');
    
        $markerArray['###DETAIL###']   = $this->pi_RTEcssText($row['detail']);
        $markerArray['###CATEGORY###'] = $this->cObj->stdWrap($row['kname'],$this->conf['category_stdWrap.']);  
      	$markerArray['###ADDRESS###']  = $this->cObj->stdWrap(nl2br($row['adresse']),$this->conf['address_stdWrap.']);
      	$markerArray['###PHONE###']    = $this->cObj->stdWrap($row['telefon'],$this->conf['tel_stdWrap.']);
      	$markerArray['###FAX###']      = $this->cObj->stdWrap($row['fax'],$this->conf['fax_stdWrap.']);
      	$markerArray['###MOBILE###']   = $this->cObj->stdWrap($row['handy'],$this->conf['mobile_stdWrap.']);
      	$markerArray['###JOB###']      = ($row['job'] == 1) ? $this->pi_linkTP($this->cObj->stdWrap($this->conf['job'],$this->conf['job.']), array($this->prefixId . '[detail]' => $row['uid']),1,$this->single_pid) : '';
      	$markerArray['###VIDEO###']    = strlen($row['video'])>0 ? $this->pi_linkTP($this->cObj->stdWrap($this->conf['video'],$this->conf['video.']),array($this->prefixId . '[video]' => $row['uid']),1,$this->single_pid) : '';
      	$markerArray['###MORE###']     = strlen($row['detail'])>0 ? $this->pi_linkTP($this->cObj->stdWrap($this->conf['more_stdWrap'],$this->conf['more_stdWrap.']),array($this->prefixId . '[detail]' => $row['uid']),1,$this->single_pid) : '';
        
        $markerArray['###CUSTOM1###']  = $this->cObj->stdWrap($row['custom1'],$this->conf['custom1.']);
        $markerArray['###CUSTOM2###']  = $this->cObj->stdWrap($row['custom2'],$this->conf['custom2.']);
        $markerArray['###CUSTOM3###']  = $this->cObj->stdWrap($row['custom3'],$this->conf['custom3.']);
        
        $linkType = explode(',',$this->linkType);
        
        if(strlen($row['detail'])>0 && $this->linkTitle == 1 && in_array($row['typ'],$linkType)) {
          $markerArray['###TITLE###'] = $this->pi_linkTP($this->cObj->stdWrap($row['firma'],$this->conf['title_stdWrap.']),array($this->prefixId.'[detail]'=> $row['uid']),'',$this->single_pid);
        } else {
          if(isset($row['link']) && $this->linkTitle == 1 && in_array($row['typ'],$linkType)) {
            $markerArray['###TITLE###'] = $this->cObj->getTypoLink($this->cObj->stdWrap($row['firma'],$this->conf['title_stdWrap.']), $row['link'], '', $this->conf['linkTarget']);
          } else {
            $markerArray['###TITLE###'] = $this->cObj->stdWrap($row['firma'],$this->conf['title_stdWrap.']);
          }
        }
        
        if($row['map_lat'] != '' && $row['map_lng'] != '') {
          $markerArray['###MAP###'] = $this->initMap($row['map_lat'],$row['map_lng'],FALSE,FALSE,FALSE,$row['uid']);
        } else {
          $markerArray['###MAP###'] = '';
        }

      	// Image Settings
        $file                         = ($row['bild'] == false) ? $this->conf['noImage'] : 'uploads/tx_mhbranchenbuch/'. $row['bild'];
        $imgTSConfig                  = Array();
        $imgTSConfig['file']          = $file;
        $imgTSConfig['file.']['maxW'] = $this->imgMaxWidth;
        $imgTSConfig['file.']['maxH'] = $this->imgMaxHeight;
        $imgTSConfig['altText']       = $row['firma'];
        $imgTSConfig['titleText']     = $row['firma'];
        $imgTSConfig['params']        = $this->imageParams;
        
        if(strlen($row['detail'])>0) {
          $markerArray['###IMAGE###'] = $this->pi_linkTP($this->cObj->IMAGE($imgTSConfig),array($this->prefixId.'[detail]'=> $row['uid']),1,$this->single_pid);
        } else {
          if(isset($row['link'])) {
            $markerArray['###IMAGE###'] = $this->cObj->getTypoLink($this->cObj->IMAGE($imgTSConfig),$row['link'],'', $this->conf['linkTarget']);
          } else {  
            $markerArray['###IMAGE###'] = $this->cObj->IMAGE($imgTSConfig);
          }
        }
      	
      	// E-Mail Konfiguration
      	
        if($this->mailTyp == 1 && $row['email']) {
          $markerArray['###EMAIL###'] = $this->pi_linkTP($this->cObj->stdWrap($this->conf['email_stdWrap'],$this->conf['email_stdWrap.']),array($this->prefixId.'[email]'=> $row['uid']),0,$this->single_pid);
        } 
        elseif($this->mailTyp == 0 && $row['email']) 
        {
          $temp_conf                        = $this->typolink_conf;
          $temp_conf['parameter.']['wrap']  = '|'.$row['email'];
          $markerArray['###EMAIL###']       = $this->cObj->typolink($this->conf['email_stdWrap'],$temp_conf);
        } 
        else 
        {
          $markerArray['###EMAIL###'] = '';
        }
    
        // WWW Konfiguration
        
      	if(!$row['link']) {
          $markerArray['###WWW###'] = ''; 
        } else {    
          $markerArray['###WWW###'] = $this->cObj->getTypoLink($this->conf['www_stdWrap'],$row['link'],'', $this->conf['linkTarget']);     
        }
       
        // Voller output ...
        
        if($full == TRUE) { $content .= $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray); }
      }
      
      /* PAGEBROWSER START */
      if(is_array($pageBrowser)) {
        $wrapArr = array(
          'browseBoxWrap'           => '<div class="browseBoxWrap">|</div>',
          'showResultsWrap'         => '<div class="showResultsWrap">|</div>',
          'browseLinksWrap'         => '<div class="browseLinksWrap">|</div>',
          'showResultsNumbersWrap'  => '<span class="showResultsNumbersWrap">|</span>',
          'disabledLinkWrap'        => '<span class="disabledLinkWrap">|</span>',
          'inactiveLinkWrap'        => '<span class="inactiveLinkWrap">|</span>',
          'activeLinkWrap'          => '<span class="activeLinkWrap">|</span>'
        );

        $this->internal['res_count']          = $pageBrowser['count'];
        $this->internal['currentTable']       = $pageBrowser['table']; 
        $this->internal['results_at_a_time']  = $this->resultsPerPage;
        $this->internal['maxPages']           = $this->maxPages;
        $this->internal['showRange']          = $this->showRange;
        $this->internal['showFirstLast']      = $this->showFirstLast;
        $this->internal['showResultCount']    = $this->showResultCount;
        $this->internal['dontLinkActivePage'] = $this->dontLinkActivePage;
        $this->internal['pagefloat']          = $this->pagefloat;
        
        $content .= $this->pi_list_browseresults(0,'',$wrapArr,'page');
      }
      /* PAGEBROWSER END */
      
      return !$full ? $markerArray : $content;
    
    } else {
      $template = $this->cObj->getSubpart($this->template,"###ERROR###");
      $markerArray['###TEXT###'] = $this->pi_getLL('error_display_not_found');
      $markerArray['###LANG_ERROR_HEADER###'] = $this->pi_getLL('error_header');
      $markerArray['###LANG_BACK###']         = $this->pi_getLL('back');
      return $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray);
    }
  }
  
  
  
  function getCatMenu($content,$conf) {
    
    $menu   = array(); #init
    $lConf  = $conf["userFunc."];
    
    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
      SELECT
        *
      FROM
        " . $this->dbTable2 . "
      WHERE
        hidden = 0
      AND
        deleted = 0
      ORDER BY
        name        
    ");
    
    if($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
      while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
        $getCount = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
          SELECT
            *
          FROM
            " . $this->dbTable1 . "
          WHERE
            hidden = 0
          AND
            deleted = 0
          AND
            FIND_IN_SET(" . $row['uid'] . ", kategorie)
        ");
        
        $count = ($lConf['catMenuCount'] == 1) ? ' (' . $GLOBALS['TYPO3_DB']->sql_num_rows($getCount) . ')' : '';
        
        array_push($menu, 
          array(
            'title'           => $row['name'].$count,
            'uid'             => $lConf['single_pid'],
            '_ADD_GETVARS'    => '&' . $this->prefixId . '[cat]=' . $row['uid'].'&no_cache=1',
            //'_OVERRIDE_HREF'  => $this->pi_linkTP_keepPIvars_url($this->piVars,1,0,$lConf['single_pid']),
          )
        );
        
      }
    }
    return $menu;
  }
  
  
  
  /**
  * Displays the form for the mail or a singleview for a entry
  *
  * @param int $pid: PageId 
  *    
  * @return	The method that be needed
  */
  function displaySingle($pid,$catId = FALSE) {
    
    if(isset($this->piVars['email'])) {
      // get the contact form
      return $this->displayContactForm($pid); 
    } 
    elseif(isset($this->piVars['display'])) 
    {
      // get a single entry
      
      $uid    = $this->piVars['display'];
      
      $res    = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
        SELECT
          f.*,
          k.name AS kname
        FROM
          " . $this->dbTable1 . " f
          LEFT JOIN " . $this->dbTable2 . " k ON k.uid = f.kategorie
        WHERE
          f.uid = " . $uid . "
        ORDER BY
          f.crdate DESC
          LIMIT 1");
        
      return $this->getItem($res, TRUE);
      
    } 
    elseif(isset($this->piVars['edit'])) 
    {
      // get the fe edit form
      return $this->displayEditForm($pid);
    }
    elseif(isset($this->piVars['detail'])) 
    {
      // get the detail-view of a entry
      $uid    = $this->piVars['detail'];
      
      $res    = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
        SELECT
          f.*,
          k.name AS kname
        FROM
          " . $this->dbTable1 . " f
          LEFT JOIN " . $this->dbTable2 . " k ON k.uid = f.kategorie
        WHERE
          f.uid = " . $uid . "
        ORDER BY
          f.crdate DESC
        LIMIT 1
      ");
          
      return $this->getItem($res, TRUE, 'TEMPLATE_DETAIL');
    }
    elseif(isset($this->piVars['bid'])) 
    {
      return $this->initOverview($pid,$catId);
    } 
    elseif(isset($this->piVars['rotation'])) 
    {
      return $this->getRotationRedirect($this->piVars['rotation']);
    }
    elseif(isset($this->piVars['video'])) 
    {
      return $this->getVideoPresentation($this->piVars['video']);
    }
    elseif(isset($this->piVars['delete'])) 
    {
      return $this->deleteEntry($this->piVars['delete']);
    }
    elseif(isset($this->piVars['cat'])) 
    {
      return $this->displayAll($pid,$this->piVars['cat']);
    }
    else 
    {
      return $this->initOverview($pid);
    }
  }
  
  
  
  /**
  * Displays a contact form
  *
  * @param int $pid: PageId 
  *   
  * @return	the form
  */
  function displayContactForm($pid) {
       
    $piVar_email  = $this->piVars['email'];
    $piVar_formId = t3lib_div::_GP('formid');
    $piVar_spam   = t3lib_div::_GP('antispam');
    
    $valid        = FALSE; 
    $content      = FALSE;
    
    $markerArray          = array();
    $wrappedSubpartArray  = array();
    
    // Some language
    $markerArray['###LANG_MAIL_RECEIVER###']  = $this->pi_getLL('mail_receiver');
    $markerArray['###LANG_MAIL_NAME###']      = $this->pi_getLL('mail_name');
    $markerArray['###LANG_MAIL_EMAIL###']     = $this->pi_getLL('mail_email');
    $markerArray['###LANG_MAIL_MESSAGE###']   = $this->pi_getLL('mail_message');
    $markerArray['###LANG_MAIL_SUBMIT###']    = $this->pi_getLL('mail_submit');
    $markerArray['###LANG_MAIL_CANCEL###']    = $this->pi_getLL('mail_cancel');
    
    // form is submitted
    if($piVar_formId > 0) {
    
      $res      = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
        SELECT 
          email
        FROM 
          " . $this->dbTable1 . "
        WHERE 
          deleted = 0
          AND 
          hidden  = 0
          AND 
          uid = " . $piVar_email . "
        LIMIT 1
      ");
       
      $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
      
      $markerArray['###MAIL_TO###'] = $row['email'];

      $absender = $this->mail_from;
      $betreff  = $this->pi_getLL('mail_subject');
      $header   = $this->mail_header."\n\n";
      
      $tempVar  = FALSE;
      
      foreach(t3lib_div::_GP('tx_mh_branchenbuch_postVar') AS $field => $var) {
        $content .= $field.': ' . htmlentities($var) . "\n\n";
        $tempVar .= $var;
      }
      
      if (t3lib_extMgm::isLoaded('captcha')) {
      
        session_start();
        $captchaStr = $_SESSION['tx_captcha_string'];
        $_SESSION['tx_captcha_string'] = '';
        if ($captchaStr != "" && $captchaStr == t3lib_div::_GP('captcha_response')) {
          $valid = TRUE;
        }
        
        // Debug
        #echo 'CaptchaStr: '. $captchaStr;
        #echo '<br />PiVar: ' . t3lib_div::_GP('captcha_response');
        
      } elseif($piVar_spam == '2') {
      
        $valid = TRUE;
        
      }
        
      if(strlen($tempVar) > 0 && $valid == TRUE) {
        if(mail($row['email'], $betreff, $header.$content, "From: ".$absender)) {
          
          // Some language
          $markerArray['###LANG_MAIL_SUCCESS_SEND_HEADER###']   = $this->pi_getLL('mail_success_header');
          $markerArray['###LANG_MAIL_SUCCESS_SEND_TEXT###']     = $this->pi_getLL('mail_success_text');
          
          $template = $this->cObj->getSubpart($this->template,"###MAIL_SUCCESS###");
        } else {
          $template = $this->cObj->getSubpart($this->template,"###ERROR###");
          $markerArray['###LANG_ERROR_HEADER###'] = $this->pi_getLL('error_header');
          $markerArray['###LANG_BACK###']         = $this->pi_getLL('back');
          $markerArray['###TEXT###']              = $this->pi_getLL('error_mailpid');
        }
      } else {
        $template = $this->cObj->getSubpart($this->template,"###ERROR###");
        $markerArray['###LANG_BACK###']         = $this->pi_getLL('back');
        $markerArray['###LANG_ERROR_HEADER###'] = $this->pi_getLL('error_header');
        $markerArray['###TEXT###']              = $this->pi_getLL('error_mail_unknown');
      }
      
    }
    elseif(isset($piVar_email)) 
    {
      // the mail-form
      $template = $this->cObj->getSubpart($this->template,"###MAIL###");
      
      $res      = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
        SELECT 
          `firma`
        FROM 
          " . $this->dbTable1 . "
        WHERE 
          `deleted` = 0
          AND 
          `hidden`  = 0
          AND 
          `uid`     = " . $piVar_email . "
        LIMIT 1
      ");
      
      $row = @$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
      
      $markerArray['###MAIL_TO###'] = htmlentities($row['firma']);
      
      if (t3lib_extMgm::isLoaded('captcha')) {
        $markerArray['###ANTISPAM###'] = '<img src="'.t3lib_extMgm::siteRelPath('captcha').'captcha/captcha.php" alt="" /> <input size="30" type="text" name="captcha_response" id="captcha_response" value="' . $_SESSION['tx_captcha_string'] . '" />';
      } else {
        $markerArray['###ANTISPAM###'] = '<input size="3" type="text" name="antispam" />';
      }
    }
    else 
    {
      $template = $this->cObj->getSubpart($this->template,"###ERROR###");
      $markerArray['###LANG_BACK###']         = $this->pi_getLL('back');
      $markerArray['###LANG_ERROR_HEADER###'] = $this->pi_getLL('error_header');
      $markerArray['###TEXT###']              = $this->pi_getLL('error_mail_unknown');
    }
    return $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray);
  }



  /**
  * Displays some little statistics
  *
  * @return	some statistics
  */
  function displayStats() {
    
    $template   = $this->cObj->getSubpart($this->template,"###STATISTICS###");

    // Count all entries
    $count_all  = @mysql_fetch_object($GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
      SELECT 
        count(uid) AS anzahl
      FROM 
        " . $this->dbTable1 . "
    "));
    
    // Count all hidden entries
    $count_hidden = @mysql_fetch_object($GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
      SELECT 
        count(uid) AS anzahl
      FROM 
        " . $this->dbTable1 . "
      WHERE 
        hidden = 1
      AND 
        deleted = 0
    "));
    
    // Count all deleted entries
    $count_deleted  = @mysql_fetch_object($GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
      SELECT 
        count(uid) AS anzahl
      FROM 
        " . $this->dbTable1 . "
      WHERE 
        deleted = 1
      AND 
        hidden = 0
    "));
       
    // Count categories
    $count_cats = @mysql_fetch_object($GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
      SELECT 
        count(uid) AS anzahl
      FROM 
        " . $this->dbTable2 . "
      WHERE 
        hidden = 0
      AND 
        deleted = 0
    "));
    
    // Count clicks today of all entries
    $count_clicks = @mysql_fetch_object($GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
      SELECT 
        count(uid) AS anzahl
      FROM 
        " . $this->dbTable6 . "
      WHERE 
        hidden = 0
      AND 
        deleted = 0
      AND 
        logdate = CURDATE()
    "));
    
    // Count all clicks of of all entries
    $count_clicks_all = @mysql_fetch_object($GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
      SELECT 
        count(uid) AS anzahl
      FROM 
        " . $this->dbTable6 . "
      WHERE 
        hidden = 0
      AND 
        deleted = 0
    "));
    
    // Count all clicks yesterday of of all entries
    $count_clicks_yesterday = @mysql_fetch_object($GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
      SELECT 
        count(uid) AS anzahl
      FROM 
        " . $this->dbTable6 . "
      WHERE 
        hidden = 0
      AND 
        deleted = 0
      AND 
        logdate = CURDATE()-1
    "));
    
    $markerArray['###STAT_ALL###']              = isset($count_all->anzahl) ? $count_all->anzahl : '0';
    $markerArray['###STAT_HIDDEN###']           = isset($count_hidden->anzahl) ? $count_hidden->anzahl : '0';
    $markerArray['###STAT_DELETED###']          = isset($count_deleted->anzahl) ? $count_deleted->anzahl : '0';
    $markerArray['###STAT_CAT###']              = isset($count_cats->anzahl) ? $count_cats->anzahl : '0';
    $markerArray['###STAT_CLICKS_TODAY###']     = isset($count_clicks->anzahl) ? $count_clicks->anzahl : '0';
    $markerArray['###STAT_CLICKS_ALL###']       = isset($count_clicks_all->anzahl) ? $count_clicks_all->anzahl : '0';
    $markerArray['###STAT_CLICKS_YESTERDAY###'] = isset($count_clicks_yesterday->anzahl) ? $count_clicks_yesterday->anzahl : '0';
    
    // The company directory have <b>###STAT_ALL###</b> entries. 
    // <b>###STAT_HIDDEN###</b> entries are not public yet and <b>###STAT_DELETED###</b> entries are deleted.
    // <br />We have <b>###STAT_CAT###</b> categories.<br />
    // Overall our visitor have clicked <b>###STAT_CLICKS_ALL###</b> times on a logo from a company, 
    // <b>###STAT_CLICKS_TODAY###</b> today and <b>###STAT_CLICKS_YESTERDAY###</b> yesterday.
    
    $markerArray['###LANG_STATISTIC###'] = $this->sprintf2(
      $this->pi_getLL('statistic'), 
      array(
        'stat_all'              => $markerArray['###STAT_ALL###'],
        'stat_hidden'           => $markerArray['###STAT_HIDDEN###'],
        'stat_deleted'          => $markerArray['###STAT_DELETED###'],
        'stat_cat'              => $markerArray['###STAT_CAT###'],
        'stat_clicks_all'       => $markerArray['###STAT_CLICKS_ALL###'],
        'stat_clicks_today'     => $markerArray['###STAT_CLICKS_TODAY###'],
        'stat_clicks_yesterday' => $markerArray['###STAT_CLICKS_YESTERDAY###']
      )
    );
    
    return $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray);
  }
  
  

  /**
  * Display a FE Form to submit a entry
  *
  * @param int $pid: PageId   
  *    
  * @return	the form
  */
  function displayFEForm($pid) {
    
    $GLOBALS["TSFE"]->set_no_cache();
    
    // Some language
    $arrayAll = array(
      'step1_legend', 'step2_legend', 'step3_legend', 'step3_2_legend', 'choose', 'step3_2_city',
      'step3_2_city_submit', 'step4_header', 'feform_important', 'feform_entry',
      'feform_xs', 'feform_s', 'feform_m', 'feform_l', 'feform_xl', 'feform_xxl', 'feform_xxl2',
      'feform_category', 'feform_categorywish', 'feform_categorywish_desc', 'feform_general', 'feform_company',
      'feform_address', 'feform_tel', 'feform_fax', 'feform_mobile', 'feform_www', 'feform_email', 'feform_upload_legend',
      'feform_upload_choose', 'feform_keywords_legend', 'feform_keywords_desc', 'feform_detailed_legend', 'feform_detailed_desc',
      'feform_job', 'feform_job_desc', 'feform_finish_legend', 'feform_terms', 'feform_terms_desc', 'feform_submit', 
      'feform_success_header', 'feform_success_text'
    );
    
    foreach ($arrayAll as $marker) {
     $markerArray['###LANG_'.strtoupper($marker).'###'] = $this->pi_getLL($marker); 
    }
    
    if($this->maxEntriesPerUser > 0 && $this->getEntriesPerUser($GLOBALS['TSFE']->fe_user->user['uid']) >= $this->maxEntriesPerUser) {
      
      return $this->listEntries($pid);
    
    } else {
    
      $postFormId   = t3lib_div::_GP('formid');
      $postAntiSpam = t3lib_div::_GP('antispam');
      
      $bid          = $this->piVars['bid'];
      $lid          = $this->piVars['lid'];
      $oid          = $this->piVars['oid'];
      
      $content      = ''; #init
      $postVar      = array();
      $error        = 0;
       
      $markerArray          = array();
      $wrappedSubpartArray  = array();
      
      $required_fields      = strlen($this->feForm_required) > 0 ? explode(',',$this->feForm_required) : FALSE;
      
      $this->includeHeaderData();
      
      $GLOBALS['TSFE']->fe_user->user['uid'];
      
      // Form send?
      if($postFormId > 0) {
             
        // Here are the Formdata
        $x        = t3lib_div::_GP('tx_mhbranchenbuch_postVar');
        
        // Check required fields ...
        if($required_fields) {
          foreach($required_fields AS $required_field) {
            if(!$x[trim($required_field)]) { $error = 1; $debug = $required_field; }
          }
        }
        
        // All valid?
        if(!$error) {
              
          // Read the formdata
          foreach($x AS $field => $var) {
            if(is_array($var)) {
              foreach($var AS $var2) {
                $postVar[$field][] = $var2;
              }
            } else {
              $postVar[$field] = trim($var);
            }
          }
          
          $category = is_array($postVar['kategorie']) ? implode(',',$postVar['kategorie']) : $postVar['kategorie'];
  
          // Get uploaded picture
          if($_FILES['tx_mhbranchenbuch']['name']) {
            require_once (PATH_t3lib .'class.t3lib_basicfilefunc.php');
            
            $this->fileFunc = t3lib_div::makeInstance("t3lib_basicFileFunctions");
            $sauber = $this->fileFunc->cleanFileName($_FILES['tx_mhbranchenbuch']['name']);
            $unique = $this->fileFunc->getUniqueName($sauber, "uploads/tx_mhbranchenbuch/");
            
            // Check imagesize
            $fileInfo = $this->fileFunc->getTotalFileInfo($_FILES['tx_mhbranchenbuch']['tmp_name']);
  
            if(($fileInfo['size']/1024) <= $this->feForm_maxsize) {
              move_uploaded_file($_FILES['tx_mhbranchenbuch']['tmp_name'],$unique);
              $temp_unique  = explode('/',$unique);    
              $uploadName   = $temp_unique[2];
            }
          }
          
          $postVar['job'] = ($postVar['job'] == '1') ? '1' : '0';
          
          // Is a valid category choosen?
          if($category) {
            $insertArray = array(
              'pid'         => $pid,
              'crdate'      => time(),
              'cruser_id'   => $GLOBALS['TSFE']->fe_user->user['uid'],
              'hidden'      => 1,
              'kategorie'   => $category,
              'firma'       => $postVar['firma'],
              'adresse'     => $postVar['anschrift'],
              'telefon'     => $postVar['telefon'],
              'fax'         => $postVar['fax'],
              'link'        => $postVar['www'],
              'email'       => $postVar['email'],
              'keywords'    => $postVar['keywords'],
              'handy'       => $postVar['handy'],
              'typ'         => $postVar['typ'],
              'bundesland'  => $postVar['bundesland'],
              'landkreis'   => $postVar['landkreis'],
              'ort'         => $postVar['ort'],
              'job'         => $postVar['job'],
              'detail'      => $postVar['details'],
              'custom1'     => $postVar['custom1'],
              'custom2'     => $postVar['custom2'],
              'custom3'     => $postVar['custom3'],
              'bild'        => $uploadName
             );
          } else {
            $insertArray = array(
              'pid'         => $pid,
              'crdate'      => time(),
              'cruser_id'   => $GLOBALS['TSFE']->fe_user->user['uid'],
              'hidden'      => 1,
              'firma'       => $postVar['firma'],
              'adresse'     => $postVar['anschrift'],
              'telefon'     => $postVar['telefon'],
              'fax'         => $postVar['fax'],
              'link'        => $postVar['www'],
              'email'       => $postVar['email'],
              'keywords'    => $postVar['keywords'],
              'handy'       => $postVar['handy'],
              'typ'         => $postVar['typ'],
              'bundesland'  => $postVar['bundesland'],
              'landkreis'   => $postVar['landkreis'],
              'ort'         => $postVar['ort'],
              'job'         => $postVar['job'],
              'detail'      => $postVar['details'],
              'custom1'     => $postVar['custom1'],
              'custom2'     => $postVar['custom2'],
              'custom3'     => $postVar['custom3'],
              'bild'        => $uploadName
            );
          }
          
          $query = $GLOBALS['TYPO3_DB']->INSERTquery($this->dbTable1, $insertArray);
          
          // If entry is successfull in the db, give a "success"-template back
          if($GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query)) {
            $template   = $this->cObj->getSubpart($this->template,"###FE_FORM_SUCCESS###");
            
             // Some language
            $markerArray['###LANG_FEFORM_SUCCESS_HEADER###']   = $this->pi_getLL('feform_success_header');
            $markerArray['###LANG_FEFORM_SUCCESS_TEXT###']     = $this->pi_getLL('feform_success_text');
            
            // Mail-Report
            if($this->feForm_report > 0 && $this->admin != '') {
              mail($this->admin, $this->pi_getLL('feform_mailsubject'),  $this->getMailBody($GLOBALS['TYPO3_DB']->sql_insert_id()), "From: ".$this->mail_from);
            } 
          }
        // If not, then cancel and give a error back
        } else {
          $template   = $this->cObj->getSubpart($this->template,"###ERROR###");
          $markerArray['###LANG_ERROR_HEADER###'] = $this->pi_getLL('error_header');
          $markerArray['###LANG_BACK###']         = $this->pi_getLL('back');
          $markerArray['###TEXT###']              = $this->pi_getLL('error_feform_fields');
        }
        
        return $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray);
      }
      // Form not yet sended? Give the whole form out ...
      else 
      {
        foreach ($arrayAll as $marker) {
         $markerArray['###LANG_'.strtoupper($marker).'###'] = $this->pi_getLL($marker); 
        }
        
        $output = ''; #init
        
        // STEP 1
        $template   = $this->cObj->getSubpart($this->template,"###FE_SIGNUP_STEP1###");
        $conf1      = array(
          #'value'     => t3lib_div::getIndpEnv('TYPO3_SITE_URL') . $this->pi_getPageLink($this->id,'',array($this->prefixId . '[bid]' => '')),
          'value'     => 'index.php?id=' . $this->id . '&amp;' . $this->prefixId . '[bid]=',
          'addSelect' => 'onChange="MM_jumpMenu(\'parent\',this,0)"',
          'noCache'   => 1,
        );
        $markerArray['###ITEMS###'] = $this->makeDropdownSelect($bid, $this->dbTable3, 'step1', $conf1);
        $output .= $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray);
        
        // STEP 2
        if ($bid) { 
          $template   = $this->cObj->getSubpart($this->template,"###FE_SIGNUP_STEP2###");
          $conf2      = array(
            #'value'     => t3lib_div::getIndpEnv('TYPO3_SITE_URL') . $this->pi_getPageLink($this->id,'',array($this->prefixId . '[bid]' => $bid, $this->prefixId . '[lid]' => '')),
            'value'     => 'index.php?id=' . $this->id . '&amp;' . $this->prefixId . '[bid]='  . $bid . '&amp;' . $this->prefixId . '[lid]=',
            'addSelect' => 'onChange="MM_jumpMenu(\'parent\',this,0)"',
            'where'     => ' AND bundesland = ' . $bid,
            'noCache'   => 1,
          );
          $markerArray['###ITEMS###'] = $this->makeDropdownSelect($lid, $this->dbTable4, 'step2', $conf2);
          $output .= $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray);
        }
        
        // STEP 3
        if ($lid) {
          $template   = $this->cObj->getSubpart($this->template,"###FE_SIGNUP_STEP3###");
          $conf3      = array(
            #'value'     => t3lib_div::getIndpEnv('TYPO3_SITE_URL') . $this->pi_getPageLink($this->id,'',array($this->prefixId . '[bid]' => $bid, $this->prefixId . '[lid]' => $lid, $this->prefixId . '[oid]' => '')),
            'value'     => 'index.php?id=' . $this->id . '&amp;' . $this->prefixId . '[bid]='  . $bid . '&amp;' . $this->prefixId . '[lid]=' . $lid . '&amp;' . $this->prefixId . '[oid]=',
            'addSelect' => 'onChange="MM_jumpMenu(\'parent\',this,0)"',
            'where'     => ' AND landkreis = ' . $lid,
            'noCache'   => 1,
            'hidden'    => 1,
          );
          
          // feForm_createCity (Insert select-field for "your city not here?")
          if($this->feForm_createCity == 1) { 
            #$conf3['addInnerTop'] = '<option class="tx_mhbranchenbuch_newCity" value="' . t3lib_div::getIndpEnv('TYPO3_SITE_URL') . $this->pi_getPageLink($this->id,'',array($this->prefixId . '[bid]' => $bid, $this->prefixId . '[lid]' => $lid, $this->prefixId . '[oid]' => 'create')) . '&amp;no_cache=1">' . $this->pi_getLL('feform_yourCity') . '</option>';
            $conf3['addInnerTop'] = '<option class="tx_mhbranchenbuch_newCity" value="index.php?id=' . $this->id . '&amp;' . $this->prefixId . '[bid]='  . $bid . '&amp;' . $this->prefixId . '[lid]=' . $lid . '&amp;' . $this->prefixId . '[oid]=create&amp;no_cache=1">' . $this->pi_getLL('feform_yourCity') . '</option>';
          }
          
          $markerArray['###ITEMS###'] = $this->makeDropdownSelect($oid, $this->dbTable5, 'step3', $conf3);
          $output .= $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray);
        }
        
        // STEP 4 (last step)
        if($oid) {
          if($oid == 'create') {
            $template   = $this->cObj->getSubpart($this->template,"###FE_SIGNUP_STEP3_2###");
            $output    .= $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray);
            
            if(t3lib_div::_GP('name_new') && $this->feForm_createCity == 1) {
              $insertArray = array(
                'name'      => mysql_real_escape_string(t3lib_div::_GP('name_new')),
                'landkreis' => $lid,
                'pid'       => $pid,
                'crdate'    => time(),
                'tstamp'    => time(),
                'hidden'    => 1,
              );
              if($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$GLOBALS['TYPO3_DB']->INSERTquery($this->dbTable5, $insertArray))) {
                // sends a report to a admin
                if($this->admin != '') {
                  
                  $cityMailBody = $this->sprintf2($this->pi_getLL('feform_mailbody_city'),
                    array(
                    'city' => htmlentities(t3lib_div::_GP('name_new'))
                    )
                  );
                  
                  mail($this->admin, $this->pi_getLL('feform_mailsubject_city'), $cityMailBody, "From: ".$this->mail_from);
                }
                #header('LOCATION: ' . t3lib_div::getIndpEnv('TYPO3_SITE_URL') . $this->pi_getPageLink($this->id,'',array($this->prefixId . '[bid]' => $bid, $this->prefixId . '[lid]' => $lid, $this->prefixId . '[oid]' => $GLOBALS['TYPO3_DB']->sql_insert_id())) . '&no_cache=1');
                header('LOCATION: index.php?id=' . $this->id . '&' . $this->prefixId . '[bid]='  . $bid . '&' . $this->prefixId . '[lid]=' . $lid . '&' . $this->prefixId . '[oid]=' . $GLOBALS['TYPO3_DB']->sql_insert_id() . '&no_cache=1');
              }
            }
          
          } else {
            
            $template   = $this->cObj->getSubpart($this->template,"###FE_SIGNUP_STEP4###");
            $markerArray['###UPLOAD_MAXSIZE###'] = $this->feForm_maxsize;
            
            $markerArray['###LANG_FEFORM_UPLOAD_SIZE###'] = $this->sprintf2($this->pi_getLL('feform_upload_size'), 
              array(
                'size' => $this->feForm_maxsize
              )
            );
            
            $markerArray['###LANG_FEFORM_KEYWORDS_COUNT###'] = $this->sprintf2($this->pi_getLL('feform_keywords_count'),
              array(
                'keywords' => '<b id="tx_mhbranchenbuch_words">?</b>'  
              )
            );
            
            // User is logged in?
            if($GLOBALS['TSFE']->fe_user->user['uid'] > 0) {
              // Fill out the fileds which are allready available in FE-User-Table
              $markerArray['###NAME###']    = $GLOBALS['TSFE']->fe_user->user['name'];
              $markerArray['###EMAIL###']   = $GLOBALS['TSFE']->fe_user->user['email'];
              $markerArray['###TEL###']     = $GLOBALS['TSFE']->fe_user->user['telephone'];
              $markerArray['###FAX###']     = $GLOBALS['TSFE']->fe_user->user['fax'];
              $markerArray['###WWW###']     = $GLOBALS['TSFE']->fe_user->user['www'];
              $markerArray['###COMPANY###'] = $GLOBALS['TSFE']->fe_user->user['company'];
              $markerArray['###ADDRESS###'] = $GLOBALS['TSFE']->fe_user->user['address']."\n".$GLOBALS['TSFE']->fe_user->user['zip']." " . $GLOBALS['TSFE']->fe_user->user['city'];
            } else {
              $markerArray['###NAME###']    = '';
              $markerArray['###EMAIL###']   = '';
              $markerArray['###TEL###']     = '';
              $markerArray['###FAX###']     = '';
              $markerArray['###WWW###']     = '';
              $markerArray['###COMPANY###'] = '';
              $markerArray['###ADDRESS###'] = '';
            }
            
            $markerArray['###BID###']  = $bid;
            $markerArray['###LID###']  = $lid;
            $markerArray['###OID###']  = $oid;
            
            $catHTML    = '<select onchange="tx_mhbranchenbuch_selCat(this.options[this.selectedIndex].value,this.options[this.selectedIndex].text);" id="tempCats" name="tempCats" size="5" multiple="multiple">';
            $categories = explode(',',$row['kategorie']);
    
            $catName = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"SELECT `name`, `uid` FROM " . $this->dbTable2 . " WHERE `deleted` = 0 ORDER BY `name`");
            if($GLOBALS['TYPO3_DB']->sql_num_rows($catName)) {
              while($row2 = mysql_fetch_array($catName)) {
                $catHTML .= '<option value="' . $row2['uid'] . '">' . $row2['name'] . '</option>';
              }
            }
            $catHTML .= '</select><select id="selectedCats" name="tx_mhbranchenbuch_postVar[kategorie][]" size="5" multiple="multiple"></select>';
                
            $markerArray['###ROOTLINE###']            = $this->getOViewRootline($bid,$lid,$oid);
            $markerArray['###SELECT_CATEGORIES###']   = $catHTML;
            $output .= $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray);
          }
        }
        return $output;
      }
    }
  }
  
  
  
  /**
  * list the possible entries that can be edited by a FE-User
  *
  * @param int $pid: PageId 
  *    
  * @return	list with the entries
  */
  function listEntries($pid) {
    
    $GLOBALS["TSFE"]->set_no_cache();
    
    $markerArray    = array();
    $subpartArray   = array();
    
    // which column
    $order1         = $this->piVars['by'];
    $validOrder     = array('crdate','deleted','hidden','firma','uid');
    
    $order  = $order1 != '' ? $order1 : 'crdate';
    $order  = in_array($order,$validOrder) ? $order : 'crdate';
    
    // asc, desc
    $order2         = $this->piVars['typ'];
    $orderTyp       = $order2 != '' ? $order2 : 'asc';
    $orderTyp       = in_array($orderTyp,array('desc','asc')) ? $orderTyp : 'asc';
    
    $selected_crdate  = $order == 'crdate' ? 'selected="selected"' : FALSE;
    $selected_firma   = $order == 'firma' ? 'selected="selected"' : FALSE;
    $selected_uid     = $order == 'uid' ? 'selected="selected"' : FALSE;
    $selected_deleted = $order == 'deleted' ? 'selected="selected"' : FALSE;
    $selected_hidden  = $order == 'hidden' ? 'selected="selected"' : FALSE;
    $selected_desc    = $orderTyp == 'desc' ? 'selected="selected"' : FALSE;
    $selected_asc     = $orderTyp == 'asc' ? 'selected="selected"' : FALSE;
    
    // Some language ...
    $markerArray['###LANG_FEEDIT_TABLE_ID###']        = $this->pi_getLL('feedit_table_id');
    $markerArray['###LANG_FEEDIT_TABLE_COMPANY###']   = $this->pi_getLL('feedit_table_company');
    $markerArray['###LANG_FEEDIT_TABLE_STATUS###']    = $this->pi_getLL('feedit_table_status');
    $markerArray['###LANG_FEEDIT_TABLE_SIGNEDUP###']  = $this->pi_getLL('feedit_table_signedup');
    $markerArray['###LANG_FEEDIT_TABLE_CLICKS###']    = $this->pi_getLL('feedit_table_clicks');
    $markerArray['###LANG_FEEDIT_TABLE_CLICKINFO###'] = $this->pi_getLL('feedit_table_clickinfo');
    
    // Some JavaScript
    $headerData = '
    <script type="text/javascript">
    <!--
      function MM_jumpMenu(targ,selObj,restore) {
        eval(targ+".location=\'"+selObj.options[selObj.selectedIndex].value+"\'");
        if (restore) selObj.selectedIndex=0;
      }
    -->
    </script>';
    
    $GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = $headerData;
    
    // Sort DropdownMenu
    $markerArray['###SORTBY###']  = $this->pi_getLL('sortby') . ' 
      <select name="sort" onChange="MM_jumpMenu(\'parent\',this,0)">
        <option ' . $selected_crdate . ' value="index.php?id=' . $this->id . '&amp;' . $this->prefixId . '[by]=crdate&amp;' . $this->prefixId . '[typ]=' . $orderTyp . '">' . $this->pi_getLL('sortby_crdate') . '</option>
        <option ' . $selected_firma . ' value="index.php?id=' . $this->id . '&amp;' . $this->prefixId . '[by]=firma&amp;' . $this->prefixId . '[typ]=' . $orderTyp . '">' . $this->pi_getLL('sortby_company') . '</option>
        <option ' . $selected_uid . ' value="index.php?id=' . $this->id . '&amp;' . $this->prefixId . '[by]=uid&amp;' . $this->prefixId . '[typ]=' . $orderTyp . '">' . $this->pi_getLL('sortby_uid') . '</option>
        <option ' . $selected_deleted . ' value="index.php?id=' . $this->id . '&amp;' . $this->prefixId . '[by]=deleted&amp;' . $this->prefixId . '[typ]=' . $orderTyp . '">' . $this->pi_getLL('sortby_deleted') . '</option>
        <option ' . $selected_hidden . ' value="index.php?id=' . $this->id . '&amp;' . $this->prefixId . '[by]=hidden&amp;' . $this->prefixId . '[typ]=' . $orderTyp . '">' . $this->pi_getLL('sortby_hidden') . '</option>
      </select>
      
      <select name="sort" onChange="MM_jumpMenu(\'parent\',this,0)">
        <option ' . $selected_desc . ' value="index.php?id=' . $this->id . '&amp;' . $this->prefixId . '[by]=' . $order . '&amp;' . $this->prefixId . '[typ]=desc">' . $this->pi_getLL('sortby_desc') . '</option>
        <option ' . $selected_asc . ' value="index.php?id=' . $this->id . '&amp;' . $this->prefixId . '[by]=' . $order . '&amp;' . $this->prefixId . '[typ]=asc">' . $this->pi_getLL('sortby_asc') . '</option>
      </select>
    ';
    
    $userId = $GLOBALS['TSFE']->fe_user->user['uid'];
    
    // User logged in?
    if($userId > 0) {
    
      $template   = $this->cObj->getSubpart($this->template,"###FE_EDIT_ENTRIES###");
      $subpart    = $this->cObj->getSubpart($template,"###CONTENT###");
      
      $getEntries = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
        SELECT 
          `uid`, `crdate`, `deleted`, `hidden`, `firma`, `bild`, `email`, `link`
        FROM  
          `" .$this->dbTable1 . "`
        WHERE
          `cruser_id` = " . $userId . "
          AND
          `pid` IN (" . $pid . ")
        ORDER BY 
          `" . $order . "` " . $orderTyp . "
      ");
    
      $rows = ''; # init
      if($GLOBALS['TYPO3_DB']->sql_num_rows($getEntries)) {
        while($row = mysql_fetch_array($getEntries)) {
          
          // Count clicks today of all entries
          $count_clicks_today = @mysql_fetch_object($GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
            SELECT 
              count(uid) AS anzahl
            FROM 
              `" . $this->dbTable6 . "`
            WHERE
              `fid` = " . $row['uid'] . "
            AND
              `hidden` = 0
            AND 
              `deleted` = 0
            AND 
              `logdate` = CURDATE()
          "));
          
          // Count all clicks of of all entries
          $count_clicks_all = @mysql_fetch_object($GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
            SELECT 
              count(uid) AS anzahl
            FROM 
              " . $this->dbTable6 . "
            WHERE
              fid = " . $row['uid'] . "
            AND
              hidden = 0
            AND 
              deleted = 0
          "));
          
          // Count all clicks yesterday of of all entries
          $count_clicks_yesterday = @mysql_fetch_object($GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
            SELECT 
              count(uid) AS anzahl
            FROM 
              " . $this->dbTable6 . "
            WHERE
              fid = " . $row['uid'] . "
            AND
              hidden = 0
            AND 
              deleted = 0
            AND 
              logdate = CURDATE()-1
          "));
      
          $row['hidden'] > 0 ? $hidden = '<span style="color:red; font-weight:bold;">' . $this->pi_getLL('feedit_notPublic') . '</span>&nbsp;' : $hidden = '<span style="color:green; font-weight:bold;">' . $this->pi_getLL('feedit_ok') . '</span>&nbsp;';
          $row['deleted'] > 0 ? $deleted = '<span style="color:red; font-weight:bold;">' . $this->pi_getLL('feedit_deleted') . '</span>&nbsp;' : $deleted = '';
          
          if($this->FEedit == 1) {
            $edit = $this->pi_linkTP($this->pi_getLL('feeditform_do_edit'),array($this->prefixId.'[edit]'=> $row['uid']),0, $this->single_pid);
          }
          
          if($this->FEdelete == 1) {
             $delete = $this->pi_linkTP($this->pi_getLL('feeditform_do_delete'),array($this->prefixId.'[delete]'=> $row['uid']),0, $this->single_pid);
          }
          
          $markerArray['###CLICKS_ALL###']        = $count_clicks_all->anzahl;
          $markerArray['###CLICKS_TODAY###']      = $count_clicks_today->anzahl;
          $markerArray['###CLICKS_YESTERDAY###']  = $count_clicks_yesterday->anzahl;
          
          $markerArray['###UID###']     = $row['uid'];
          $markerArray['###NAME###']    = htmlentities($row['firma']);
          $markerArray['###DATE###']    = date('d.m.y',$row['crdate']);
          $markerArray['###STATUS###']  = ($deleted != '') ? $deleted : $hidden;
          $markerArray['###BUTTONS###'] = $edit. ' ' . $delete;
          
          $sspart = $this->cObj->getSubpart($subpart,"###ITEM###");
          $rows .= $this->cObj->substituteMarkerArrayCached($sspart, $markerArray);
          
          $subpartArray['###ITEM###']   = $rows;
        }
      } else {
        $template   = $this->cObj->getSubpart($this->template,"###ERROR###");
        $markerArray['###LANG_ERROR_HEADER###'] = $this->pi_getLL('error_header');
        $markerArray['###LANG_BACK###']         = $this->pi_getLL('back');
        $markerArray['###TEXT###']              = $this->pi_getLL('error_noEntries');
      }
    } else {
      $template   = $this->cObj->getSubpart($this->template,"###ERROR###");
      $markerArray['###LANG_ERROR_HEADER###'] = $this->pi_getLL('error_header');
      $markerArray['###LANG_BACK###']         = $this->pi_getLL('back');
      $markerArray['###TEXT###']              = $this->pi_getLL('error_editEntries_register');
    }

    return $this->cObj->substituteMarkerArrayCached($template,$markerArray,$subpartArray);
  }
  
  
  
  /**
  * edit a selected entry
  *
  * @return	edit-form
  */
  function displayEditForm($pid) {
    
    $userId               = $GLOBALS['TSFE']->fe_user->user['uid'];
    $formId               = t3lib_div::_GP('formid');
    
    $UID                  = $this->piVars['edit'];
    $content              = ''; #init
    
    $markerArray          = array();
    $wrappedSubpartArray  = array();
    
    $updateArray          = array(); #init
    
    // Some language ...
    $arrayAll = array(
      'feform_edit_header','feform_edit_text','feeditform_legend', 'feeditform_location',
      'feeditform_federalstate','feeditform_admindistrict', 'feeditform_city', 'feeditform_category',
      'feeditform_keywords', 'feeditform_company', 'feeditform_address', 'feeditform_tel', 'feeditform_fax',
      'feeditform_mobile', 'feeditform_email', 'feeditform_www', 'feeditform_cancel', 'feeditform_submit', 
      'feeditform_job_desc', 'feeditform_general', 'feeditform_upload_choose', 'feeditform_upload_size',
      'feeditform_keywords_legend', 'feeditform_keywords_desc', 'feeditform_keywords_count', 'feeditform_detailed_legend',
      'feeditform_detailed_desc', 'feeditform_job', 'feeditform_job_desc', 'feeditform_upload_legend', 
      'feeditform_current_upload', 'feeditform_delPic',
    );
    
    foreach($arrayAll AS $marker) {
      $markerArray['###LANG_' . strtoupper($marker) . '###'] = $this->pi_getLL($marker);
    }
    
    // Helper-Function
    $this->includeHeaderData();

    if($userId > 0) {
      // Benutzer eingeloggt ...
      
      // Überprüft ob der FE-Benutzer == Autor ist
      $permissionCheck = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
        SELECT 
          `uid`
        FROM  
          " . $this->dbTable1 . "
        WHERE
          uid = " . intval($UID) . "
        AND
          cruser_id = " . intval($userId) . "
        LIMIT 1
      "); 
      
      if($GLOBALS['TYPO3_DB']->sql_num_rows($permissionCheck)) {
      
        // Formular abgeschickt ...
        if($formId > 0) {
        
          // Das sind die Formulardaten
          $x = t3lib_div::_GP('tx_mhbranchenbuch_postVar');
          
          // Formulardaten auslesen ...
          foreach($x AS $field => $var) {
            if(is_array($var)) {
              foreach($var AS $var2) {
                $postVar[$field][] = $var2;
              }
            } else {
              $postVar[$field] = trim($var);
            }
          }
          
          $category = is_array($postVar['kategorie']) ? implode(',',$postVar['kategorie']) : $postVar['kategorie'];
          
          // Wurde eine vorhandene Kategorie ausgewählt? 
          if($category) 
          {
            $updateArray = array(
              'kategorie'   => $category,
              'firma'       => $postVar['firma'],
              'adresse'     => $postVar['anschrift'],
              'telefon'     => $postVar['telefon'],
              'fax'         => $postVar['fax'],
              'link'        => $postVar['www'],
              'email'       => $postVar['email'],
              'keywords'    => $postVar['keywords'],
              'handy'       => $postVar['handy'],
              'job'         => $postVar['job'],
              'detail'      => $postVar['details'],
              'custom1'     => $postVar['custom1'],
              'custom2'     => $postVar['custom2'],
              'custom3'     => $postVar['custom3']
             );
          } 
          else
          {
            $updateArray = array(
              'firma'       => $postVar['firma'],
              'adresse'     => $postVar['anschrift'],
              'telefon'     => $postVar['telefon'],
              'fax'         => $postVar['fax'],
              'link'        => $postVar['www'],
              'email'       => $postVar['email'],
              'keywords'    => $postVar['keywords'],
              'handy'       => $postVar['handy'],
              'job'         => $postVar['job'],
              'detail'      => $postVar['details'],
              'custom1'     => $postVar['custom1'],
              'custom2'     => $postVar['custom2'],
              'custom3'     => $postVar['custom3']
            );
          }
          
          // Delete uploaded picture
          if(t3lib_div::_GP('delPic'))  { 
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc(
              $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                'bild', 
                $this->dbTable1, 
                'uid = ' . intval($UID) . ' AND cruser_id = ' . intval($userId), 
                '', 
                '', 
                '1'
              )
            );
            
            chmod('uploads/tx_mhbranchenbuch/' . $row['bild'],0777);
            unlink('uploads/tx_mhbranchenbuch/' . $row['bild']);
            
            $updateArray['bild'] = ''; 
          }
          
          // Check if new file upload
          if($_FILES['tx_mhbranchenbuch']['name']) {
            require_once (PATH_t3lib .'class.t3lib_basicfilefunc.php');
            
            $this->fileFunc = t3lib_div::makeInstance("t3lib_basicFileFunctions");
            $sauber = $this->fileFunc->cleanFileName($_FILES['tx_mhbranchenbuch']['name']);
            $unique = $this->fileFunc->getUniqueName($sauber, "uploads/tx_mhbranchenbuch/");
            
            // Check imagesize
            $fileInfo = $this->fileFunc->getTotalFileInfo($_FILES['tx_mhbranchenbuch']['tmp_name']);
  
            if(($fileInfo['size']/1024) <= $this->feForm_maxsize) {
              move_uploaded_file($_FILES['tx_mhbranchenbuch']['tmp_name'],$unique);
              $temp_unique  = explode('/',$unique);    
              $uploadName   = $temp_unique[2];
            }
            
            $updateArray['bild'] = $uploadName;
          }
          
          // insert new data
          $query = $GLOBALS['TYPO3_DB']->UPDATEquery($this->dbTable1, 'uid=' . intval($UID), $updateArray);
          
          // if successfull give a status report back
          if($GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query)) {
            $template   = $this->cObj->getSubpart($this->template,"###FE_FORM_SUCCESS_EDIT###");
            
            $markerArray['###BACK###'] = $this->pi_linkTP($this->pi_getLL('back'),array($this->prefixId.'[edit]'=> $UID),0, $this->single_pid);
            
            // send mail that a user hast edit his entry
            if($this->feForm_report > 0 && $this->admin != '') {
              mail($this->admin, $this->pi_getLL('feform_mailsubject_edit'), $this->getMailBody($UID), "From: ".$this->mail_from);
            } 
          }
        } else {
          
          // Update location ...
          $bid  = $this->piVars['bid'];
          $lid  = $this->piVars['lid'];
          $oid  = $this->piVars['oid'];
          
          $updateLocation = FALSE;
          
          if($bid) {
            $updateLocation = $GLOBALS['TYPO3_DB']->UPDATEquery($this->dbTable1, 'uid=' . intval($UID), array('bundesland' => $bid));
          } elseif($lid) {
            $updateLocation = $GLOBALS['TYPO3_DB']->UPDATEquery($this->dbTable1, 'uid=' . intval($UID), array('landkreis' => $lid));
          } elseif($oid) {
            $updateLocation = $GLOBALS['TYPO3_DB']->UPDATEquery($this->dbTable1, 'uid=' . intval($UID), array('ort' => $oid));
          }
          
          if($updateLocation) {
            $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $updateLocation);
            header("LOCATION: index.php?id=" . $this->id . "&" . $this->prefixId . "[edit]=" . $UID . "&no_cache=1");
          }
          
          // Check Keyword-Length on Pageload
          $GLOBALS['TSFE']->pSetup['bodyTagAdd'] = 'onload="tx_mhbranchenbuch_checkKeywords(document.getElementById(\'keywords\').value);"';
          
          $template = $this->cObj->getSubpart($this->template,"###FE_EDIT_FORM###");
          
          $query    = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
            SELECT
              *
            FROM
              `" . $this->dbTable1 . "`
            WHERE
              `uid` = " . intval($UID) . "
              AND
              `cruser_id` = " . intval($userId) . "
            LIMIT 1
          ");
          
          if($GLOBALS['TYPO3_DB']->sql_num_rows($query)) {
            $row = mysql_fetch_array($query);
            
            // Databasefields 
            foreach($row AS $feld => $inhalt) {
              $markerArray["###db_$feld###"] = htmlentities($inhalt);
            }
            
            $catHTML    = '<select name="tx_mhbranchenbuch_postVar[kategorie][]" size="5" multiple="multiple">';
            $categories = explode(',',$row['kategorie']);

            $catName = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"SELECT `name`, `uid` FROM " . $this->dbTable2 . " WHERE `deleted` = 0 AND `hidden` = 0 ORDER BY `name`");
            if($GLOBALS['TYPO3_DB']->sql_num_rows($catName)) {
              while($row2 = mysql_fetch_array($catName)) {
                if(in_array($row2['uid'], $categories)) {
                  $catHTML .= '<option value="' . $row2['uid'] . '" selected="selected">' . $row2['name'] . '</option>';
                } else {
                  $catHTML .= '<option value="' . $row2['uid'] . '">' . $row2['name'] . '</option>';
                }
              }
            }
            $catHTML .= '</select>';
            
            // Image Settings
            $file                         = ($row['bild'] == '') ? $this->conf['noImage'] : 'uploads/tx_mhbranchenbuch/' . $row['bild'];
            $imgTSConfig                  = Array();
            $imgTSConfig['file']          = $file;
            $imgTSConfig['file.']['maxW'] = $this->imgMaxWidth;
            $imgTSConfig['file.']['maxH'] = $this->imgMaxHeight;
            $imgTSConfig['altText']       = $row['firma'];
            $imgTSConfig['titleText']     = $row['firma'];
            $imgTSConfig['params']        = $this->imageParams;
            
            $markerArray['###CURRENT_IMAGE###'] = $this->cObj->IMAGE($imgTSConfig);
                    
            $markerArray['###CHECKJOB###'] = ($row['job'] == 1) ? 'checked="1"' : '';
            
            $markerArray['###LANG_FEEDITFORM_UPLOAD_SIZE###'] = $this->sprintf2($this->pi_getLL('feeditform_upload_size'), 
              array(
                'size' => $this->feForm_maxsize
              )
            );
            
            $markerArray['###LANG_FEEDITFORM_KEYWORDS_COUNT###'] = $this->sprintf2($this->pi_getLL('feeditform_keywords_count'),
              array(
                'keywords' => '<b id="tx_mhbranchenbuch_words">?</b>'  
              )
            );
            
            $markerArray['###SELECT_CATEGORIES###'] = $catHTML;
            
            $fCfg = array(
              'value'     => 'index.php?id=' . $this->id . '&amp;' . $this->prefixId . '[edit]=' . $row['uid'] . '&amp;' . $this->prefixId . '[bid]=',
              'addSelect' => 'onChange="MM_jumpMenu(\'parent\',this,0)"',
              'noCache'   => 1
            );
            
            $lCfg = array(
              'value'     => 'index.php?id=' . $this->id . '&amp;' . $this->prefixId . '[edit]=' . $row['uid'] . '&amp;' . $this->prefixId . '[lid]=',
              'addSelect' => 'onChange="MM_jumpMenu(\'parent\',this,0)"',
              'noCache'   => 1,
              'where'     => ' AND bundesland = ' . $row['bundesland']
            );
            
            $oCfg = array(
              'value'     => 'index.php?id=' . $this->id . '&amp;' . $this->prefixId . '[edit]=' . $row['uid'] . '&amp;' . $this->prefixId . '[oid]=',
              'addSelect' => 'onChange="MM_jumpMenu(\'parent\',this,0)"',
              'noCache'   => 1,
              'where'     => ' AND landkreis = ' . $row['landkreis']
            );
            
            $markerArray['###SELECT_FEDERAL_STATES###']           = $this->makeDropdownSelect($row['bundesland'], $this->dbTable3, 'bundesland', $fCfg);
            $markerArray['###SELECT_ADMINISTRATIVE_DISTRICT###']  = $this->makeDropdownSelect($row['landkreis'], $this->dbTable4, 'landkreis', $lCfg);
            $markerArray['###SELECT_CITIES###']                   = $this->makeDropdownSelect($row['ort'], $this->dbTable5, 'ort', $oCfg);         
          }
          
        }
        
      }
      
    } else {
      // Benutzer nicht eingeloggt ...
      $template   = $this->cObj->getSubpart($this->template,"###ERROR###");
      $markerArray['###LANG_ERROR_HEADER###'] = $this->pi_getLL('error_header');
      $markerArray['###LANG_BACK###']         = $this->pi_getLL('back');
      $markerArray['###TEXT###']              = $this->pi_getLL('error_editEntries_register');
    }
    
    return $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray);
  }
  
  
  
  /**
  * "delete" a entry (it will be only hidden)
  *
  * @param $uid: unique id
  *     
  * @return	true or false
  */
  function deleteEntry($uid) {
  
    $markerArray   = array();
    $SubpartArray  = array();
    
    $userId = $GLOBALS['TSFE']->fe_user->user['uid'];
    
    if($this->FEdelete == 1) {
      // if fe-user == author
      $permissionCheck = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
        SELECT 
          `uid`
        FROM  
          " . $this->dbTable1 . "
        WHERE
          uid = " . intval($uid) . "
        AND
          cruser_id = " . intval($userId) . "
        LIMIT 1
      "); 
      
      if($GLOBALS['TYPO3_DB']->sql_num_rows($permissionCheck)) {
        $query = $GLOBALS['TYPO3_DB']->UPDATEquery($this->dbTable1, 'uid=' . $uid, array('hidden' => 1));
        if($GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query)) {
          $template = $this->cObj->getSubpart($this->template,"###FE_DELETE_OK###");
          // Some language
          $markerArray['###LANG_FEFORM_DELETE_HEADER###'] = $this->pi_getLL('feform_delete_header');
          $markerArray['###LANG_FEFORM_DELETE_TEXT###']   = $this->pi_getLL('feform_delete_text');
          
          $delMailBody = $this->sprintf2($this->pi_getLL('feedit_mailbody_delete'),
            array(
            'uid' => $uid
            )
          );
          
          mail($this->admin, $this->pi_getLL('feedit_mailsubject_delete'), $delMailBody, "From: ".$this->mail_from);
          
          return $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray);
        }
      }
      return false;
    }
  }
  
  

  /**
  * Helper-"Function"
  * Gets a Dropdown-Menu
  *
  * @param int $uid: id for a element which is selected in the form
  * @param string $database: database table
  * @param string $postVar: postVar name 
  * @param array $conf: individuell settings
  *           
  * @return	a var with a generated dropdown-menu
  */
  function makeDropdownSelect($uid = 0, $database = FALSE, $postVar, $conf = FALSE) {
    
    $delete = isset($conf['deleted']) ? TRUE : FALSE;
    $hidden = isset($conf['hidden']) ? TRUE : FALSE;

    $delAndHidden = ''; #init
   
    if($delete && $hidden) {
      $delAndHidden .= '';
    } elseif($delete == FALSE && $hidden) {
      $delAndHidden .= 'deleted = 0 '; 
    } elseif($delete && $hidden == FALSE) {
      $delAndHidden	.= 'hidden = 0 ';
    } elseif($delete == FALSE && $hidden == FALSE) {
      $delAndHidden .= 'deleted = 0 AND hidden = 0 ';
    }
       
    if($database) {
      $temp = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
        SELECT 
          uid, name, hidden, deleted
        FROM  
          `" . $database . "`
        WHERE 
          $delAndHidden
          " . $conf['where'] . "
        ORDER BY 
          name
      ");

      $content = ''; #init
      
      if(@$GLOBALS['TYPO3_DB']->sql_num_rows($temp)) {
        
        $content = '<select name="tx_mhbranchenbuch_postVar[' . $postVar . ']" ' . $conf['addSelect'] . '><option value="">' . $this->pi_getLL('choose') . '</option>';
        $content .= $conf['addInnerTop'];
        
        while($row = mysql_fetch_array($temp)) {
        
          if($row['deleted'] == '1' OR $row['hidden'] == '1') continue;
          
          $value = ($conf['value'] != '') ? $conf['value'].$row['uid'] : $row['uid'];
          
          if($conf['noCache'] == '1') {
            $value .= '&amp;no_cache=1';
          }
          
          if($uid == TRUE && $row['uid'] == $uid) {
            $content .= '<option value="' . $value . '" selected="1">' . htmlentities(trim($row['name'])) . '</option>';
          } else {
            $content .= '<option value="' . $value . '">' . htmlentities(trim($row['name'])) . '</option>';
          }
        }
        
        $content .= $conf['addInnerBottom'];
        $content .= '</select>';
        
      } else {
      
        $content = isset($conf['error']) ? $conf['error'] : $this->pi_getLL('error_selectDB');
      
      }
      
      return $content;
      
    } else {
      return false;
    }
  }



  /**
  * Initialize the Overview-Function
  * 
  * @param int $pid: PageId  
  * @param int $catId: Category 
  *
  * @return	a method
  */
  function initOverview($pid,$catId = FALSE) {

    $bundesland   = $this->piVars['bid'];
    $landkreis    = $this->piVars['lid'];
    $ort          = $this->piVars['oid'];
    $kategorie    = $this->piVars['kid'];
    
    $content = FALSE;
    $content = isset($bundesland) ? $this->getLID($bundesland) : $content;
    $content = isset($landkreis) ? $this->getOID($landkreis,$bundesland) : $content;
    $content = isset($ort) ? $this->displayOverview($pid,$ort,$landkreis,$bundesland,$kategorie,$catId) : $content;

    if($this->overviewMode == 1 && $bundesland <= 0 && $landkreis <= 0 && $ort <= 0) {
      $content = $this->getBID();
    } elseif ($this->overviewMode == 2 && $landkreis <= 0) {
      $content = $this->getLID($this->overviewID,$this->single_pid);
    } elseif ($this->overviewMode == 3 && $ort <= 0) {
      $ovID    = explode(',', $this->overviewID);
      $content = $this->getOID($ovID[0],$ovID[1],$this->single_pid);
    } elseif ($this->overviewMode == 4) {
      $ovID    = explode(',', $this->overviewID);
      $content = $this->displayOverview($pid,$ovID[0],$ovID[1],$ovID[2],$kategorie,$catId);
    }

    return $content;
  }
  
  
  
  /**
  * Gets the federal states
  * 
  *
  * @return	a list with the federal states
  */
  function getBID() {
    $markerArray   = array();
    $SubpartArray  = array();
    
    $template = $this->cObj->getSubpart($this->template,"###FEDERAL_STATES###");
    $subpart  = $this->cObj->getSubpart($template,'###CONTENT###');
    $sspart   = $this->cObj->getSubpart($subpart,'###ITEM###');
    
    // Some language
    $markerArray['###LANG_OVERVIEW_STEP1###'] = $this->pi_getLL('overview_step1');
    
    $sql = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
      SELECT 
        uid, name
      FROM  
        " . $this->dbTable3 . "
      WHERE 
        deleted = 0
        AND 
        hidden = 0
      ORDER BY 
        name
    ");
    
    if($GLOBALS['TYPO3_DB']->sql_num_rows($sql)) {
      $rows = ''; #init
      while($row = mysql_fetch_array($sql)) {
      
        $res_c = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
          SELECT 
            uid
          FROM  
            " . $this->dbTable1 . "
          WHERE 
            bundesland = " . intval($row['uid']) . "
            AND
            deleted = 0
            AND 
            hidden = 0
        ");
        
        $name    = htmlentities($row['name']);
        
        $markerArray['###COUNT###'] = mysql_numrows($res_c);
        $markerArray['###NAME###']  = $this->pi_linkTP($name ,array($this->prefixId . '[bid]' => $row['uid']),1,$this->single_pid);
        
        $rows .= $this->cObj->substituteMarkerArrayCached($sspart, $markerArray);
      }
    } else {
      $markerArray['###COUNT###'] = '0';
      $markerArray['###NAME###']  = $this->pi_getLL('error_federalStates');
      
      $rows = $this->cObj->substituteMarkerArrayCached($sspart, $markerArray);
    }
    
    $SubpartArray['###ITEM###'] = $rows; 

    return $this->cObj->substituteMarkerArrayCached($template, $markerArray, $SubpartArray);
  }
  
  
  
  /**
  * Gets the administrative district
  * 
  * @param int $bid: Federal State Id
  * @param int $single_pid: alternative single page id     
  *
  * @return	a list with the administrative districts of the federal state
  */
  function getLID($bid,$single_pid = FALSE) {
    $markerArray   = array();
    $SubpartArray  = array();
    
    $template   = $this->cObj->getSubpart($this->template,"###ADMINISTRATIVE_DISTRICT###");
    $subpart    = $this->cObj->getSubpart($template,'###CONTENT###');
    $sspart     = $this->cObj->getSubpart($subpart,'###ITEM###');
    
    // Some language
    $markerArray['###LANG_OVERVIEW_STEP2###'] = $this->pi_getLL('overview_step2');
    
    $single_pid = $single_pid == FALSE ? $this->id : $single_pid;
    
    $sql = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
      SELECT 
        uid, name
      FROM  
        " . $this->dbTable4 . "
      WHERE 
        bundesland = " . intval($bid) . "
        AND
        deleted = 0
        AND 
        hidden = 0
      ORDER BY name
    ");
    
    $rows = ''; #init
    
    if($GLOBALS['TYPO3_DB']->sql_num_rows($sql)) {
      while($row = mysql_fetch_array($sql)) {
      
        $res_c = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
          SELECT 
            uid
          FROM  
            " . $this->dbTable1 . "
          WHERE 
            landkreis = " . intval($row['uid']) . "
            AND
            bundesland = " . intval($bid) . "
            AND
            deleted = 0
            AND 
            hidden = 0
        ");

        $name = htmlentities($row['name']);
        
        $markerArray['###COUNT###'] = mysql_numrows($res_c);
        $markerArray['###NAME###']  = $this->pi_linkTP($name,array($this->prefixId . '[bid]' => $bid,$this->prefixId . '[lid]' => $row['uid']),1,$single_pid);
    
        $rows .= $this->cObj->substituteMarkerArrayCached($sspart, $markerArray);
      }
      
      $row_bid = mysql_fetch_array($GLOBALS['TYPO3_DB']->sql(TYPO3_db,"SELECT map_lat, map_lng FROM $this->dbTable3 WHERE uid = " . intval($bid)));
      
      if($row_bid['map_lat'] != '' && $row_bid['map_lng'] != '') {
        $markerArray['###MAP###'] = $this->initMap($row_bid['map_lat'],$row_bid['map_lng'],FALSE,$bid);
      } else {
        $markerArray['###MAP###'] = '';
      }
      
    } else {
      $markerArray['###COUNT###'] = '0';
      $markerArray['###NAME###']  = $this->pi_getLL('error_administrativeDistrict');
      
      $rows = $this->cObj->substituteMarkerArrayCached($sspart, $markerArray);
    }
    
    $SubpartArray['###ITEM###']     = $rows; 
    $markerArray['###ROOTLINE###']  = $this->getOViewRootline($bid);
    
    return $this->cObj->substituteMarkerArrayCached($template, $markerArray, $SubpartArray);
  }
  
  
  
  /**
  * Gets the cities
  * 
  * @param int $lid: Administrative District Id 
  * @param int $bid: Federal State Id  
  *    
  *
  * @return	a list with the cities of the administrative district
  */
  function getOID($lid,$bid) {
    $markerArray   = array();
    $SubpartArray  = array();
    
    $template = $this->cObj->getSubpart($this->template,'###CITIES###');
    $subpart  = $this->cObj->getSubpart($template,'###CONTENT###');
    $sspart   = $this->cObj->getSubpart($subpart,'###ITEM###');
    
    // Some language
    $markerArray['###LANG_OVERVIEW_STEP3###']       = $this->pi_getLL('overview_step3');
    $markerArray['###LANG_OVERVIEW_STEP3_INFO###']  = $this->pi_getLL('overview_step3_info');
    
    $sql = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
      SELECT 
        uid, name
      FROM  
        " . $this->dbTable5 . "
      WHERE 
        landkreis = " . intval($lid) . "
        AND
        deleted = 0
        AND 
        hidden = 0
      ORDER BY 
        name
    ");
    
    $rows = ''; #init 
    
    if($GLOBALS['TYPO3_DB']->sql_num_rows($sql)) {
      while($row = mysql_fetch_array($sql)) {
        
        $res_c = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
          SELECT 
            uid
          FROM  
            " . $this->dbTable1 . "
          WHERE 
            ort = " . intval($row['uid']) . "
            AND
            landkreis = " . intval($lid) . "
            AND
            bundesland = " . intval($bid) . "
            AND
            deleted = 0
            AND 
            hidden = 0
        ");
        
        $name    = htmlentities($row['name']);
        
        $markerArray['###COUNT###'] = mysql_numrows($res_c);
        $markerArray['###NAME###']  = $this->pi_linkTP($name,array($this->prefixId . '[bid]' => $bid, $this->prefixId . '[lid]' => $lid, $this->prefixId . '[oid]' => $row['uid']),1,$this->id);
        
        $rows .= $this->cObj->substituteMarkerArrayCached($sspart,$markerArray);
      }
    } else {
      $markerArray['###COUNT###'] = '0';
      $markerArray['###NAME###']  = $this->pi_getLL('error_cities');
      $rows = $this->cObj->substituteMarkerArrayCached($sspart, $markerArray);
    }
    
    $row_lid = mysql_fetch_array($GLOBALS['TYPO3_DB']->sql(TYPO3_db,"SELECT detail, map_lat, map_lng FROM $this->dbTable4 WHERE uid = " . intval($lid)));
    $markerArray['###DETAIL###']    = strlen($row_lid['detail']) > 0 ? $this->pi_RTEcssText($row_lid['detail']) : $this->pi_getLL('error_display_adInfo');
    
    if($row_lid['map_lat'] != '' && $row_lid['map_lng'] != '') {
      $markerArray['###MAP###'] = $this->initMap($row_lid['map_lat'],$row_lid['map_lng'],$lid);
    } else {
      $markerArray['###MAP###'] = '';
    }
    
    $SubpartArray['###ITEM###']     = $rows; 
    $markerArray['###ROOTLINE###']  = $this->getOViewRootline($bid,$lid);
    
    return $this->cObj->substituteMarkerArrayCached($template, $markerArray, $SubpartArray);
  }
  
  
  
  /**
  * The Overview
  * 
  * @param int $pid: PageId
  * @param int $oid: City Id
  * @param int $lid: Administrative District Id
  * @param int $bid: Federal State Id
  * @param int $kid: Category Id        
  *    
  * @return	a overview of the categories (and the number on entries in it) and the last x entries
  */
  function displayOverview($pid,$oid,$lid,$bid,$kid = FALSE,$catId = FALSE) {
    $markerArray   = array();
    $SubpartArray  = array();
    
     // Some language
    $markerArray['###LANG_OVERVIEW_CATEGORY###']    = $this->pi_getLL('overview_category');
    $markerArray['###LANG_OVERVIEW_LAST3###']       = $this->pi_getLL('overview_last3');
    $markerArray['###LANG_BACK###']                 = $this->pi_getLL('back');
    $markerArray['###LANG_OVERVIEW_STEP4###']       = $this->pi_getLL('overview_step4');
    $markerArray['###LANG_OVERVIEW_STEP4_INFO###']  = $this->pi_getLL('overview_step4_info');
    
    if($kid) {
      $template = $this->cObj->getSubpart($this->template,"###OVERVIEW_LIST###");
 
      /* PAGEBROWSER INIT */
      $enableFields = $this->cObj->enableFields($this->dbTable1);
      
      $res_c = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
        'uid', 
        $this->dbTable1,
        '`pid` IN(' . $pid . ') AND `kategorie`=' . $kid . ' AND `bundesland`=' . $bid . ' AND `landkreis`=' . $lid . ' AND `ort`=' . $oid  . ' ' . $enableFields
      );
      
      $count = $GLOBALS['TYPO3_DB']->sql_num_rows($res_c);
      if (!isset($this->piVars['page'])) $this->piVars['page'] = 0;
      $limit = $this->piVars['page'] * $this->resultsPerPage . "," . $this->resultsPerPage;
      
      $pageBrowser = array(
        'pid'   => $pid,
        'limit' => $limit,
        'page'  => $this->piVars['page'],
        'table' => $this->dbTable1,
        'count' => $count,
      );
      
      $orderBy = isset($this->overviewSort) ? " FIND_IN_SET(f.typ,'" . $this->overviewSort . "'), f.firma ASC " : ' f.firma ASC ';
      
      $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
        SELECT
          f.*,
          k.name AS kname
        FROM
          " . $this->dbTable1 . " f
          RIGHT JOIN " . $this->dbTable2 . " k ON k.uid = f.kategorie
        WHERE
          f.bundesland = " . intval($bid) . "
          AND
          f.landkreis = " . intval($lid) . "
          AND
          f.ort = " . intval($oid) . "
          AND
          FIND_IN_SET(" . $kid . ",f.kategorie)
          AND
          f.deleted = 0
          AND
          f.hidden  = 0
          AND
          f.pid IN (" . $pid . ")
        ORDER BY
          " . $orderBy . "
        LIMIT " . $limit
      );

      $markerArray['###ROOTLINE###']  = $this->getOViewRootline($bid,$lid,$oid,$kid);
      $markerArray['###ENTRIES###']   = $this->getItem($res,TRUE,'',$pageBrowser);
      
      return $this->cObj->substituteMarkerArrayCached($template, $markerArray, $SubpartArray);
      
    } else {
      $template = $this->cObj->getSubpart($this->template,"###OVERVIEW###");
      
      $markerArray['###CATEGORIES###'] = $this->getCategories($pid,$bid,$lid,$oid,$catId,'0');
      
      // Map
      $row_oid = mysql_fetch_array($GLOBALS['TYPO3_DB']->sql(TYPO3_db,"SELECT map_lat, map_lng FROM $this->dbTable5 WHERE uid = " . intval($oid)));
      
      if($row_oid['map_lat'] != '' && $row_oid['map_lng'] != '') {
        $markerArray['###MAP###'] = $this->initMap($row_oid['map_lat'],$row_oid['map_lng'],FALSE,FALSE,$oid);
      } else {
        $markerArray['###MAP###'] = '';
      }
      
      // Last x Entries
      $getLast = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
        SELECT
          f.*,
          k.name AS kname
        FROM
          " . $this->dbTable1 . " f
          LEFT JOIN " . $this->dbTable2 . " k ON k.uid = f.kategorie
        WHERE 
          f.bundesland = " . intval($bid) . "
          AND
          f.landkreis = " . intval($lid) . "
          AND
          f.ort = " . intval($oid) . "
          AND
          f.deleted = 0
          AND
          f.hidden  = 0
          AND
          f.pid IN (" . $pid . ")
          " . $x_query . "
        ORDER BY
          f.crdate DESC
          LIMIT " . $this->limitLatest);
      
      $markerArray['###LATEST###']    = $this->getItem($getLast,TRUE);
      $markerArray['###ROOTLINE###']  = $this->getOViewRootline($bid,$lid,$oid,$kid);
      
      $row_lid = mysql_fetch_array($GLOBALS['TYPO3_DB']->sql(TYPO3_db,"SELECT detail FROM $this->dbTable5 WHERE uid = " . intval($oid)));
      $markerArray['###DETAIL###']    = strlen($row_lid['detail']) > 0 ? $this->pi_RTEcssText($row_lid['detail']) : $this->pi_getLL('error_display_ad2Info');
      
      return $this->cObj->substituteMarkerArrayCached($template, $markerArray, $SubpartArray);
    }
  }
  
  
  
  /**
  * The rootline-menu for the overview
  *
  * @param int $bid: Federal State Id
  * @param int $lid: Administrative District Id
  * @param int $oid: City Id
  * @param int $kid: Category Id  
  *
  * @return	the menu
  */
  function getOViewRootline($bid = FALSE, $lid = FALSE, $oid = FALSE, $kid = FALSE) {
    $status   = 0; #init
    $temp     = ''; #init

    if($kid) {
      $q1 = '
        o.name AS oname, 
        o.uid AS oid, 
        l.name AS lname, 
        l.uid AS lid, 
        b.name AS bname, 
        b.uid AS bid, 
        k.name AS kname';
        
      $q2 = 
        $this->dbTable3. ' b
        LEFT JOIN ' . $this->dbTable4 . ' l ON l.uid = ' . intval($lid) . '
        LEFT JOIN ' . $this->dbTable5 . ' o ON o.uid = ' . intval($oid) . ' 
        LEFT JOIN ' . $this->dbTable2 . ' k ON k.uid = ' . intval($kid) . '
        WHERE b.uid = ' . intval($bid);
      
      $status = 1;
      
    } elseif($oid) {

      $q1 = '
        o.name AS oname, 
        o.uid AS oid, 
        l.name AS lname, 
        l.uid AS lid, 
        b.name AS bname, 
        b.uid AS bid';
        
      $q2 = 
        $this->dbTable3. ' b
        LEFT JOIN ' . $this->dbTable4 . ' l ON l.uid = ' . intval($lid) . '
        LEFT JOIN ' . $this->dbTable5 . ' o ON o.uid = ' . intval($oid) . '
        WHERE b.uid = ' . intval($bid);
      
      $status = 2;
      
    } elseif($lid) {
    
      $q1 = '
        l.name AS lname, 
        l.uid AS lid, 
        b.name AS bname, 
        b.uid AS bid';

      $q2 = 
        $this->dbTable3. ' b
        LEFT JOIN ' . $this->dbTable4 . ' l ON l.bundesland = b.uid WHERE l.uid = ' . intval($lid);
      
      $status = 3;
      
    } else {
      $q1 = '
        name AS bname, 
        uid AS bid';

      $q2 = $this->dbTable3 . ' WHERE uid = ' . intval($bid);
      
      $status = 4;
    }
    
    $rootline = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
      SELECT
        $q1
      FROM
        $q2
      LIMIT 1
    ");
      
    if($GLOBALS['TYPO3_DB']->sql_num_rows($rootline)) {    
      $row = mysql_fetch_array($rootline);
      
      $urlConf_b = array(
        $this->prefixId . '[bid]' => $bid  
      );
      
      $urlConf_l = array(
        $this->prefixId . '[bid]' => $bid, 
        $this->prefixId . '[lid]' => $lid
      );
      
      $urlConf_o = array(
        $this->prefixId . '[bid]' => $bid, 
        $this->prefixId . '[lid]' => $lid, 
        $this->prefixId . '[oid]' => $oid
      );
      
      $f = $this->pi_linkTP(htmlentities($this->overviewPathStart),array(),1,$this->id);
      $b = $this->pi_linkTP(htmlentities($row['bname']),$urlConf_b,1,$this->id);
      $l = $this->pi_linkTP(htmlentities($row['lname']),$urlConf_l,1,$this->id);
      $o = $this->pi_linkTP(htmlentities($row['oname']),$urlConf_o,1,$this->id);
      $k = htmlentities($row['kname']);
      
      $sep = $this->overviewPathSeperator;
      
      switch($status) {
        case 1:
          $temp = $f.$sep.$b.$sep.$l.$sep.$o.$sep.$k;
        break;
        
        case 2:
          $temp = $f.$sep.$b.$sep.$l.$sep.$o;
        break;
        
        case 3:
          $temp = $f.$sep.$b.$sep.$l;
        break;
        
        case 4:
          $temp = $f.$sep.$b;
        break;
      }
      
      return $temp;
    }
  }
  
  
  
  /**
  * Redirects you to the Website of a Entry and counts visits
  *
  * @param int $id: id from a entry
  *  
  * @return	a redirection-page
  */
  function getRotationRedirect($id) {
  
    $markerArray          = array();
    $wrappedSubpartArray  = array();
    
    $template = $this->cObj->getSubpart($this->template,"###ROTATION_REDIRECT###");
   
    $markerArray['###LANG_REDIRECT###'] = $this->pi_getLL('redirect');
    $IP                                 = $_SERVER['REMOTE_ADDR'];
    
    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
      SELECT
        *
      FROM
        " . $this->dbTable1 . "
      WHERE
        uid = " . intval($id) . "
      AND
        deleted = 0
      AND
        hidden = 0
    ");        
    
    if($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
      
      $checkTable = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"SELECT * FROM $this->dbTable6 WHERE ip = '$IP' AND fid = $id AND logdate = CURDATE()");
      if(!$GLOBALS['TYPO3_DB']->sql_num_rows($checkTable)) {
        $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"INSERT INTO $this->dbTable6 SET logdate = NOW(), tstamp =  " . time() . ", fid = " . intval($id) . ", ip = '$IP'");
        $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"UPDATE $this->dbTable1 SET hit_count = hit_count+1 WHERE uid = " . intval($id));
      }
       
      $row = mysql_fetch_array($res);
      $WWW = $this->pi_getPageLink($row['link']);
      
      if($this->directRedirect == 1) {
        header("LOCATION: " . t3lib_div::locationHeaderUrl($WWW));
      } else {
        $markerArray['###COMPANY###']   = htmlentities(trim($row['firma']));
        $markerArray['###REDIRECT###']  = "<meta http-equiv=\"refresh\" content=\"$this->redirectTime; URL=$WWW\" />";
        $markerArray['###LANG_REDIRECT_TEXT###']  = $this->sprintf2($this->pi_getLL('redirect_text'),
          array(
            'company' => $markerArray['###COMPANY###']
          )
        );
      }
      
    } else {
    
      $content = $this->pi_getLL('error_rotationRedirect');
      
    }
    
    return $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray); 
  }
  
  
  
  /**
  * Shows the Videopresentation, require a .flv-File
  *
  * @param int $id: id from a entry
  *  
  * @return	a videopresentation
  */
  function getVideoPresentation($id) {
  
    $markerArray          = array(); #init
    $wrappedSubpartArray  = array(); #init
    
    $template = $this->cObj->getSubpart($this->template,"###PRESENTATION###");
    
    // Some language ...
    $markerArray['###LANG_PRESENTATION###'] = $this->pi_getLL('presentation');
    $markerArray['###LANG_BACK###']         = $this->pi_getLL('back');
    
    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
      SELECT
        `firma`,
        `video`
      FROM
        " . $this->dbTable1 . "
      WHERE
        uid = " . intval($id) . "
      AND
        deleted = 0
      AND
        hidden = 0
    ");        
    
    if($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
      $row        = mysql_fetch_assoc($res);
      
      $getFile    = explode('.',$row['video']);
      $countFile  = count($getFile);
      $flvPlayerPath  = './typo3conf/ext/' . $this->extKey . '/res/player_flv.swf';
      $flvPlayer  = '
      <object type="application/x-shockwave-flash" data="' . $flvPlayerPath . '" width="320" height="240">
        <param name="movie" value="' . $flvPlayerPath . '" />
        <param name="allowFullScreen" value="true" />
        <param name="FlashVars" value="flv=' . $row['video'] . '&amp;title=' . $row['firma'] . '&amp;loop=0&amp;autoplay=0&amp;autoload=1&amp;margin=0&amp;playercolor=666666&amp;loadingcolor=ff4d33&amp;buttonovercolor=ff4d33&amp;slidercolor1=f5f5f5&amp;slidercolor2=fafafa&amp;sliderovercolor=ff4d33&amp;showstop=1&amp;showvolume=1&amp;showtime=1" />
      </object>';
      
      $markerArray['###VIDEO###']   = $getFile[$countFile-1] == 'flv' ? $flvPlayer : $this->pi_getLL('error_video');
      $markerArray['###COMPANY###'] = htmlentities(trim($row['firma']));
    }
    
    return $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray);
  }
  
  
  
  /**
  * Init the (Google)-Map
  * 
  * @param int $lat: Latitude
  * @param int $lng: Longitude
  * @param int $bid: Federal State Id    
  * @param int $lid: Administrative District Id     
  * @param int $oid: City Id    
  * @param int $uid: Unique Id (Id of a entry)  
  *  
  * @return	a map  
  */
  function initMap($lat, $lng, $lid = FALSE, $bid = FALSE, $oid = FALSE, $uid = FALSE) {
    
    $template = $this->cObj->getSubpart($this->template,"###MAPCODE###");
    
    $api                  = $this->map_api;
    $output               = ''; #init
    $markerArray          = array(); #init
    $wrappedSubpartArray  = array(); #init
    
    $WHERE_CLAUSE = ''; #init
    if($bid) { $WHERE_CLAUSE .= 'bundesland = ' . intval($bid); $zoom = $this->map_zoom1; }
    if($lid) { $WHERE_CLAUSE .= ' landkreis = ' . intval($lid); $zoom = $this->map_zoom2; }
    if($oid) { $WHERE_CLAUSE .= ' ort = ' . intval($oid); $zoom = $this->map_zoom3; }
    if($uid) { $WHERE_CLAUSE .= ' uid = ' . intval($uid); $zoom = $this->map_zoom4; }
    
    if($api) {
      
      $sql = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
        SELECT 
        uid, firma, map_lat, map_lng, adresse, link, telefon, fax, detail, bild
        FROM  
          " . $this->dbTable1 . "
        WHERE 
          $WHERE_CLAUSE
        AND
          map_lat != ''
        AND
          map_lng != ''
        AND
          deleted = 0
        AND 
          hidden = 0
      ");
      
      $marker = ''; #init
      if($GLOBALS['TYPO3_DB']->sql_num_rows($sql)) {
        while($row = mysql_fetch_array($sql)) {
          $marker_content = $this->cObj->getTypoLink($row['firma'],$row['link'],'', $this->conf['linkTarget']);

          $adresse = htmlentities($row['adresse']);
          $adresse = preg_replace("/\r\n|\n|\r/", "<br>", $adresse);
          $marker_content .= "<br />" . $adresse;
          $marker_content .= "<br /><br />" .$this->cObj->stdWrap($row['telefon'],$this->conf['tel_stdWrap.']);
        	$marker_content .= "<br />" .$this->cObj->stdWrap($row['fax'],$this->conf['fax_stdWrap.']);
        	
        	if($this->map_showImage == 1) {
          	$file                         = ($row['bild'] == false) ? $this->conf['noImage'] : 'uploads/tx_mhbranchenbuch/'. $row['bild'];
            $imgTSConfig                  = Array();
            $imgTSConfig['file']          = $file;
            $imgTSConfig['file.']['maxW'] = $this->imgMaxWidth;
            $imgTSConfig['file.']['maxH'] = $this->imgMaxHeight;
            $imgTSConfig['altText']       = $row['firma'];
            $imgTSConfig['titleText']     = $row['firma'];
            $imgTSConfig['params']        = $this->imageParams;
            
            if(strlen($row['detail'])>0) {
              $marker_content .= "<br /><br />" .$this->pi_linkTP($this->cObj->IMAGE($imgTSConfig),array($this->prefixId.'[detail]'=> $row['uid']),1,$this->single_pid);
            } else {
              if(isset($row['link'])) {
                $marker_content .= "<br /><br />" .$this->cObj->getTypoLink($this->cObj->IMAGE($imgTSConfig),$row['link'],'', $this->conf['linkTarget']);
              } else {  
                $marker_content .= "<br /><br />" .$this->cObj->IMAGE($imgTSConfig);
              }
            }
          }
          
          $marker .= "gmap.addOverlay(createMarker( new GLatLng(" . $row['map_lat'] . "," . $row['map_lng'] . "), '<p class=\"mh_branchenbuch_mapcon\">" . $marker_content . "</p>'));\n";
        }
      }

      $output .= '
      <script type="text/javascript">
      document.write(\'<sc\'+\'ript src="http://maps.google.com/maps?file=api&v=2&key=' . $api . '" type="text/javascript"></scr\'+\'ipt>\');
      function mapload() {
      	 if (GBrowserIsCompatible()) {
      		var gmap = new GMap2(document.getElementById("mh_branchenbuch_map"), {mapTypes:[G_NORMAL_MAP, G_SATELLITE_MAP, G_HYBRID_MAP]});
      		gmap.addControl(new GSmallMapControl());
      		gmap.addControl(new GMapTypeControl());
      		gmap.setCenter(new GLatLng(' . $lat . ',' . $lng . '),' . $zoom . ');
      		' . $marker . '
      	 }
      }
      
      function createMarker(point, mtext) {
      	var marker = new GMarker(point);
      	GEvent.addListener(marker, "click", function() {
      		marker.openInfoWindowHtml( mtext );
      	});
      	return marker;
      }
      window.setTimeout(mapload,1000);
      </script>';
      
      $markerArray['###GENERATED_CODE###'] = $output;
      
      return $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray);
      
    } else {
      return 'Error, no API-Key found!';
    }
  }
  
  
  
  /**
  * Helper-Function!
  * Get the number of entries which a user have
  * 
  * @param int $uid: User-Id    
  *  
  * @return	count  
  */
  function getEntriesPerUser($uid = FALSE) {
    if($uid) {
      $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
        'uid', 
        $this->dbTable1,
        '`cruser_id` = ' . $uid . ' ' . $this->cObj->enableFields($this->dbTable1)
      );
      $count = $GLOBALS['TYPO3_DB']->sql_num_rows($res) ? $GLOBALS['TYPO3_DB']->sql_num_rows($res) : '0';
      return $count;
    } else {
      return FALSE;
    }
  }
  
  
  
  /**
  * Helper-Function!
  *     
  *  
  * @return	JS & CSS Inlcude  
  */
  function sprintf2($str='', $vars=array(), $char='%') {
    if (!$str) return '';
    if (count($vars) > 0) {
      foreach ($vars as $k => $v)
      {
        $str = str_replace($char . $k, $v, $str);
      }
    }
    return $str;
  }
  
  
  
  /**
  * Helper-Function!
  *      
  *  
  * @return	JS  
  */
  function includeHeaderData() {
    // Some JavaScript and a CSS-File for Style!!
    $headerData = '
    <link rel="stylesheet" type="text/css" href="' . t3lib_extMgm::siteRelPath($this->extKey). 'res/feForm.css" />
    <script type="text/javascript">
    <!--
    
      function MM_jumpMenu(targ,selObj,restore) {
        eval(targ+".location=\'"+selObj.options[selObj.selectedIndex].value+"\'");
        if (restore) selObj.selectedIndex=0;
      }
      
      var typ       = new Array();
      typ[0]        = "'  . trim($this->feForm_fields_s) . '";
      typ[1]        = "'  . trim($this->feForm_fields_m) . '";
      typ[2]        = "'  . trim($this->feForm_fields_l) . '";
      typ[3]        = "'  . trim($this->feForm_fields_xl) . '";
      typ[4]        = "'  . trim($this->feForm_fields_xxl) . '";
      typ[5]        = "'  . trim($this->feForm_fields_xxl2) . '";
      typ[6]        = "nothing";
      typ[7]        = "'  . trim($this->feForm_fields_xs) . '";
      
      var keywords  = new Array();
      keywords[0]   = "'  . $this->feForm_keywords_s . '";
      keywords[1]   = "'  . $this->feForm_keywords_m . '";
      keywords[2]   = "'  . $this->feForm_keywords_l . '";
      keywords[3]   = "'  . $this->feForm_keywords_xl . '";
      keywords[4]   = "'  . $this->feForm_keywords_xxl . '";
      keywords[5]   = "'  . $this->feForm_keywords_xxl2 . '";
      keywords[6]   = "1";
      keywords[7]   = "'  . $this->feForm_keywords_xs . '";
      
      function getElementsByClassName(myName) {
        var tags = ["div", "span", "fieldset"];
        var result = [];
        var searchExpression = new RegExp("\\b" + myName + "\\b");
        for (var i = 0; i < tags.length; i++ ) {
          var objects = document.getElementsByTagName( tags[ i ] );
          for (var j = 0; j < objects.length; j++ )
          if ( objects[ j ].className.match( searchExpression ) )
            result.push( objects[ j ] );
          }
        return result;
      }
  
      function tx_mhbranchenbuch_hideAll() {
        document.getElementById("tx_mhbranchenbuch_keywordsFieldset").className = "hidden";
        document.getElementById("tx_mhbranchenbuch_uploadFieldset").className = "hidden";
        document.getElementById("tx_mhbranchenbuch_detailedFieldset").className = "hidden";
        
        //var disableObjRes = getElementsByClassName("unhide");
        //if(disableObjRes) {
        //  for (var x = 0; x < disableObjRes.length; x++) {
        //    disableObjRes[x].className = "hidden";
        //  }
        //}
      }
      
      function tx_mhbranchenbuch_getFields(val) {
        var enableFields = typ[val].split(",");
        for (var i = 0; i < enableFields.length; i++) {
          var enableObj = document.getElementById("tx_mhbranchenbuch_" + enableFields[i]);
          if (enableObj) {
            enableObj.className = "unhide";
          }
        }
      }
      
      function tx_mhbranchenbuch_checkKeywords() {
        var tempStr     = document.tx_mhbranchenbuch_feForm.keywords.value;
        var strLength   = tempStr.split(" ").length;
        var validLength = keywords[document.getElementById("mhbranchenbuch_typ").value];
        var tempWords   = (validLength-strLength)+1;
        
        document.getElementById("tx_mhbranchenbuch_words").innerHTML = tempWords;
        
        if(strLength <= validLength) {
          tempMemory = tempStr;
        } else {
          document.tx_mhbranchenbuch_feForm.keywords.value = tempMemory;
        } 
      }
      
      function tx_mhbranchenbuch_resetKeywords() {
        document.getElementById("keywords").disabled = false;
      }
      
      function tx_mhbranchenbuch_selCat(value,text)  {
        var a = _$("tempCats").options;
        for(var i=0;i<a.length;i++) { 
          if(a[i].selected) {
            newVal = new Option(text);
            document.getElementById("selectedCats").options[document.getElementById("selectedCats").length] = newVal;
            newVal.value = value;
          }
        }
      }
      
      function _$(e) {
        if(document.getElementById(e))
          return document.getElementById(e);
        else
          alert (e + " gibts net");
      }
    -->
    </script>';
    
    return $GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = $headerData;
  }
  
  
  
  function getMailBody($uid) {
  
    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc(
      $GLOBALS['TYPO3_DB']->exec_SELECTquery(
        '*', 
        $this->dbTable1, 
        'uid = ' . intval($uid), 
        '', 
        '', 
        '1'
      )
    );
    
    $tempCategory = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
      'name', 
      $this->dbTable2, 
      'FIND_IN_SET(uid,"' . $row['kategorie'] . '")', 
      '', 
      '', 
      ''
    );
    
    $categories = ''; #init;
    while($category = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($tempCategory)) {
      $categories .= $category['name']. ' ';
    }
    
    $tempImage = ($row['bild'] == '') ? '' : 'uploads/tx_mhbranchenbuch/' . $row['bild'];
    
    if($tempImage != '')  {
      $image = $_SERVER['REMOTE_HOST'].$tempImage;
    }
    
    $federal = $GLOBALS['TYPO3_DB']->sql_fetch_assoc(
      $GLOBALS['TYPO3_DB']->exec_SELECTquery(
        'name', 
        $this->dbTable3, 
        'uid = ' . $row['bundesland'], 
        '', 
        '', 
        '1'
      )
    );
    
    $admin  = $GLOBALS['TYPO3_DB']->sql_fetch_assoc(
      $GLOBALS['TYPO3_DB']->exec_SELECTquery(
        'name', 
        $this->dbTable4, 
        'uid = ' . $row['landkreis'], 
        '', 
        '', 
        '1'
      )
    );
    
    $city   = $GLOBALS['TYPO3_DB']->sql_fetch_assoc(
      $GLOBALS['TYPO3_DB']->exec_SELECTquery(
        'name', 
        $this->dbTable5, 
        'uid = ' . $row['ort'], 
        '', 
        '', 
        '1'
      )
    );
    
    // Mailbody
    $mailBody = "";
    $mailBody .= $this->pi_getLL('mailbody_federalstate') . " " . $federal['name'] . "\n";
    $mailBody .= $this->pi_getLL('mailbody_administrativedistrict') . " " . $admin['name'] . "\n";
    $mailBody .= $this->pi_getLL('mailbody_city') . " " . $city['name'] . "\n\n";
    $mailBody .= $this->pi_getLL('mailbody_category') . " " . $categories . "\n\n";
    $mailBody .= $this->pi_getLL('mailbody_company') . " " . $row['firma'] . "\n";
    $mailBody .= $this->pi_getLL('mailbody_address') . "\n" . $row['adresse'] . "\n\n";
    $mailBody .= $this->pi_getLL('mailbody_tel') . " " . $row['telefon'] . "\n";
    $mailBody .= $this->pi_getLL('mailbody_fax') . " " . $row['fax'] . "\n";
    $mailBody .= $this->pi_getLL('mailbody_mobile') . " " . $row['handy'] . "\n\n";
    $mailBody .= $this->pi_getLL('mailbody_www') . " " . $row['link'] . "\n";
    $mailBody .= $this->pi_getLL('mailbody_mail') . " " . $row['email'] . "\n\n";
    $mailBody .= $this->pi_getLL('mailbody_keywords') . " " . $row['keywords'] . "\n\n";
    $mailBody .= $this->pi_getLL('mailbody_detail') . "\n" . $row['detail'] . "\n\n";
    $mailBody .= $this->pi_getLL('mailbody_logo') . " " . $image . "\n";
    
    return $mailBody;
  }
  
  
  
  function getCategories($pid,$bid,$lid,$oid,$catID = FALSE,$root_uid = '0') {
  
    $template = $this->cObj->getSubpart($this->template,"###OVERVIEW_CATEGORIES###");
    
    $markerArray          = array(); #init
    $wrappedSubpartArray  = array(); #init
    $output               = '';
    
    if(strlen($catID) > 0) { $catID  = explode(',',$catID); } else { $catID = FALSE; }
    
    $i              = 0; #init
    $query          = ''; #init
    
    if($catID) {
      $query    = 'AND ';
      $x_query  = 'AND (';
      foreach($catID AS $value) {
        if($i>0) { $query .= ' OR '; $x_query .= ' OR '; $i=0; }
        $query      .= 'FIND_IN_SET(' . $value . ',uid)';
        $x_query    .= 'FIND_IN_SET(' . $value . ',f.kategorie)';
        $i++;
      }
      $x_query .= ')';
    } else {
      $query    = FALSE;
      $x_query  = FALSE;
    }
    
    $query .= $query ? ' AND root_uid = ' . $root_uid : 'AND root_uid = ' . $root_uid;
    
    $getCats = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
      SELECT
        uid,
        name,
        image,
        description,
        root_uid
      FROM
        " . $this->dbTable2 . "
      WHERE
        deleted = 0
        AND 
        hidden = 0
        " . $query . "
      ORDER BY " . $this->conf['cat_sortBy']
    );
  
    if($GLOBALS['TYPO3_DB']->sql_num_rows($getCats)) {
      while($row = mysql_fetch_assoc($getCats)) {
  
        $getCount = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
          SELECT
            *
          FROM
            " . $this->dbTable1 . "
          WHERE
              FIND_IN_SET(" . $row['uid'] . ", kategorie)
            AND
              ort = " . intval($oid) . "
            AND
              hidden = 0
            AND
              deleted = 0
            AND
              pid IN (" . $pid . ")
        ");
  
        if($this->show_empty_cats == 0 && mysql_numrows($getCount) <= 0) {
          continue;
        }
        
        $urlConf = array(
          $this->prefixId . '[bid]' => $bid, 
          $this->prefixId . '[lid]' => $lid, 
          $this->prefixId . '[oid]' => $oid,  
          $this->prefixId . '[kid]' => $row['uid']
        );
        
        $name     = htmlentities($row['name']);
        
        $catCount = $this->show_cat_count == '1' ? mysql_numrows($getCount) : FALSE;
        
        $markerArray['###NAME###']  = $this->pi_linkTP($name, $urlConf, 1, $this->single_pid);
        $markerArray['###COUNT###'] = $catCount;
        
        // Image Settings
        if($row['image']) {
          $file                         = 'uploads/tx_mhbranchenbuch/'. $row['image'];
          $imgTSConfig                  = Array();
          $imgTSConfig['file']          = $file;
          $imgTSConfig['file.']['maxW'] = $this->catImgMaxWidth;
          $imgTSConfig['file.']['maxH'] = $this->catImgMaxHeight;
          $imgTSConfig['altText']       = $name;
          $imgTSConfig['titleText']     = $name;
          $imgTSConfig['params']        = $this->catImageParams;
          
          $markerArray['###IMAGE###']  = $this->pi_linkTP($this->cObj->IMAGE($imgTSConfig), $urlConf, 1, $this->single_pid);
        } else {
          $markerArray['###IMAGE###']  = '';
        }
        
        $markerArray['###DESCRIPTION###'] = $this->pi_RTEcssText($row['description']);
        
        $subCategories = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,"
          SELECT
            `uid`
          FROM
            `" . $this->dbTable2 . "`
          WHERE
            `root_uid` = " . $row['uid']
        );

        if($GLOBALS['TYPO3_DB']->sql_num_rows($subCategories) > 0) {
          $markerArray['###SUBCATEGORY###'] = '<div class="tx_mh_branchenbuch-subCategory>' . $this->getCategories($pid,$bid,$lid,$oid,'',$row['uid']) . '</div>';
        } else {
          $markerArray['###SUBCATEGORY###'] = FALSE;
        }
        
        $output .= $this->cObj->substituteMarkerArrayCached($template,$markerArray,array(),$wrappedSubpartArray);
      }
      
    }
    return $output;
  }
  
} // END CLASS


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mh_branchenbuch/pi1/class.tx_mhbranchenbuch_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mh_branchenbuch/pi1/class.tx_mhbranchenbuch_pi1.php']);
}
?>
