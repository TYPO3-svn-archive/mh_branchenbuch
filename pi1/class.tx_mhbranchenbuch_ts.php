<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Martin Hesse <mail@martin-hesse.info>
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

class tx_mhbranchenbuch_ts extends tslib_pibase {

  function getArray() {
    return array(
      'search_tables',
      'search_showXS',
      'limitLatest',
      'display_not_found',
      'single_pid',
      'search_pid',
      'mail_from',
      'mail_header',
      'mailTyp',
      'count_entrys',
      'maxFontSize',
      'minFontSize',
      'CloudColors',
      'countEntrys',
      'TagCloudLink',
      'feForm_required',
      'admin',
      'feForm_report',
      'feForm_maxsize',
      'feForm_createCity',
      'feForm_fields_xs',
      'feForm_fields_s',
      'feForm_fields_m',
      'feForm_fields_l',
      'feForm_fields_xl',
      'feForm_fields_xxl',
      'feForm_fields_xxl2',
      'feForm_keywords_xs',
      'feForm_keywords_s',
      'feForm_keywords_m',
      'feForm_keywords_l',
      'feForm_keywords_xl',
      'feForm_keywords_xxl',
      'feForm_keywords_xxl2',
      'FEdelete',
      'FEedit',
      'imgMaxHeight',
      'imgMaxWidth',
      'imageParams',
      'overviewMode',
      'overviewPathSeperator',
      'overviewPathStart',
      'overviewID',
      'overviewSort',
      'resultsPerPage',
      'maxPages',
      'showRange',
      'showFirstLast',
      'showResultCount',
      'dontLinkActivePage',
      'pagefloat',
      'rotationBGColor',
      'rotationLimit',
      'rotationExclude',
      'rotationTarget',
      'redirectTime',
      'countTimeout',
      'linkTitle',
      'linkType',
      'directRedirect',
      'show_empty_cats',
      'show_cat_count',
      'catImgMaxHeight',
      'catImgMaxWidth',
      'catImageParams',
      'map_api',
      'map_zoom1',
      'map_zoom2',
      'map_zoom3',
      'map_zoom4',
      'map_showImage',
      'letterAll',
      'maxEntriesPerUser'
    );
  }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mh_branchenbuch/pi1/class.tx_mhbranchenbuch_ts.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mh_branchenbuch/pi1/class.tx_mhbranchenbuch_ts.php']);
}
?>
