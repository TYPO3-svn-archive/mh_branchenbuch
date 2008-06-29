<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2008 Martin Hesse <mail@martin-hesse.info>
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(t3lib_extMgm::extPath('mydashboard', 'templates/class.tx_mydashboard_template.php'));
require_once(t3lib_extMgm::extPath('mydashboard', 'templates/interface.tx_mydashboard_widgetinterface.php'));

class tx_mhbranchenbuch_overview extends tx_mydashboard_template implements tx_mydashboard_widgetinterface {

    
  /**
  * Get the Widget Content
  *
  * @return    string    The Widget Content
  */
  function getContent(){
    // Generate the List
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_mhbranchenbuch_firmen','hidden=1','','tstamp DESC',10);
    $c .= $this->showDatabaseList($this->getInternalLabel('hidden'),$res,'firma,uid,tstamp');
    
    $res_latest = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_mhbranchenbuch_firmen','','','tstamp DESC',5);
    $c .= $this->showDatabaseList($this->getInternalLabel('latest'),$res_latest,'firma,uid,tstamp');
    
    $url = $this->getConfigVar('feed_url');
    
    $cacheFile = PATH_site.'typo3temp/mydashboard'.substr(md5($url.$this->getConfigVar('feed_title')), 0, 15).'.cache';
		
		// Update rules
		$updateRSS = (isset($_POST['ajax']) && isset($_POST['action']) && $_POST['action'] == 'refresh');
		
		if(file_exists($cacheFile) && !$updateRSS){		
			$fileinfo = fileatime($cacheFile);
			if($fileinfo > (time()-(int)$this->getConfigVar('cache_time_h'))){
				$data = $this->loadRSSData($cacheFile);
				return $this->renderRSSArray($data);		
			}
		}
		
		// Load the rss2array class and fetch the RSS Feed
		require_once(t3lib_extMgm::extPath('mydashboard', 'widgets/helper/rss2array.php'));
		$rss_array = rss2array($url);
		
		// Safe the Feed in a Cache File
		$this->safeRSSData($cacheFile, $rss_array);
		
		$c .= '<h4 style="position:relative;">' . $this->getInternalLabel('rssfeed') . '</h4>' . $this->renderRSSArray($rss_array);
		
    return $c;    
  }
     
  /*
  * initial  the Widget
  */
  function init() {
    
    // Init Parent
    parent::init();
       
    // Add Language File
    $this->addLanguageFile(t3lib_div::getFileAbsFileName('EXT:mh_branchenbuch/widgets/labels.xml'));
    
    // Set title & icon
    $this->setTitle($this->getInternalLabel('title'));
    $this->setIcon(t3lib_extMgm::extRelPath('mh_branchenbuch').'ext_icon.gif');
    
    // Build config
		$config = array(
			'item_limit' => array(
				'default' => 5,
				'type' => 'int',
			),
			'feed_title' => array(
				'default' => 'Martin-Hesse.info DevBlog',
				'type' => 'string',
			),
			'feed_url' => array(
				'default' => 'http://www.martin-hesse.info/feed',
				'type' => 'string',
			),
			'cache_time_h' => array(
				'default' => 12,
				'type' => 'int',
			),
		);
		
		// Set the Default config
		$this->setDefaultConfig($config);
		
    // required
    return true;
  }
  
  /*
  * Render the RSS Array
  *
  * @parm Array $array The RSS Array
  */
	private function renderRSSArray($array){	
		if(!is_array($array) || !isset($array['items'])) return 'RSS fetch or render error!';
		
		$content = '<ul class="rssfeed">';
		$i = 0;
		foreach($array['items'] as $item){
		
			// Limit for the Items
			if($i >= (int)$this->getConfigVar('item_limit')) break;
			
			// The internal Key
			$key = 'k'.substr(md5(implode('',$item)),0,10);
			
			// The Content
			$content .= '<li><h3 onclick="new Effect.toggle($(\''.$key.'\'),\'appear\')">' . utf8_decode($item['title']) . '</h3><span id="'.$key.'" style="display: none;" class="content">' . utf8_decode($item['description']) . '<span class="more"><a href="'.$item['link'].'" target="_blank">'.$this->getInternalLabel('more').'</a></span></span></li>';
			
			// Increment the counter
			$i++;
			
		} # for
		
		// Return the rendered RSS array
		return $content.'</ul>';
	} # function - renderRSSArray
	
	
	/*
	 * Load the RSS Data from the Filesystem
	 *
	 * @parm String $filename the cache filename
	 */
	private function loadRSSData($fileName){
		if ($fd = @fopen ($fileName, 'r')) {
			$length = filesize ($fileName);
			if ($length > 0)
				$out = fread ($fd, $length);
			else
				return false;
			fclose ($fd);
			return unserialize($out);
		} # if
		return false;
	} # function - loadRSSData
	
	
	/*
	 * Safe the RSS Data in the Filesystem
	 *
	 * @parm String $fileName the cache filename
	 * @parm Array $rssArray the RSS Items in a array
	 */
	private function safeRSSData($fileName, $rssArray){
		if(file_exists($fileName))
			unlink($fileName);
		t3lib_div::writeFileToTypo3tempDir($fileName,serialize($rssArray));
	} # function - safeRSSData
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mh_branchenbuch/widgets/class.tx_mhbranchenbuch_overview.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mh_branchenbuch/widgets/class.tx_mhbranchenbuch_overview.php']);
}
?>