<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Chi Hoang (chibo@gmx.de)
*  
*  Modified for mh_branchenbuch (c) 2008 Martin Hesse <mail@martin-hesse.info>
*  
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


require_once(PATH_t3lib.'class.t3lib_treeview.php');

/**
 * extend class t3lib_treeview to change function wrapTitle().
 *
 */
class tx_mhbranchenbuch_tceFunc_selectTreeView extends t3lib_treeview {

	var $TCEforms_itemFormElName='';
	var $TCEforms_nonSelectableItemsArray=array();

	/**
	 * wraps the record titles in the tree with links or not depending on if they are in the TCEforms_nonSelectableItemsArray.
	 *
	 * @param	string		$title: the title
	 * @param	array		$v: an array with uid and title of the current item.
	 * @return	string		the wrapped title
	 */
	function wrapTitle($title,$v)	{
    if($v['title'] == '') {
      $title = $v['name'];
    }
    
		$hrefTitle = $v['description'];
		$aOnClick = 'setFormValueFromBrowseWin(\''.$this->TCEforms_itemFormElName.'\','.$v['uid'].',\''.$title.'\'); return false;';
		return '<a href="#" onclick="'.htmlspecialchars($aOnClick).'" title="'.htmlentities($v['description']).'">'.$title.'</a>';
	}
	
	/**
	 * Wrap the plus/minus icon in a link
	 *
	 * @param	string		HTML string to wrap, probably an image tag.
	 * @param	string		Command for 'PM' get var
	 * @param	boolean		If set, the link will have a anchor point (=$bMark) and a name attribute (=$bMark)
	 * @return	string		Link-wrapped input string
	 * @access private
	 */
	function PM_ATagWrap($icon,$cmd,$bMark='')	{
		if ($this->thisScript) {
			if ($bMark)	{
				$anchor = '#'.$bMark;
				$name=' name="'.$bMark.'"';
			}
			return '<a href="#" onClick="set'.$this->treeName.'PM(\''.$cmd.'\');TBE_EDITOR_submitForm();"'.$name.'>'.$icon.'</a>';
		} else {
			return $icon;
		}
	}
	
   	function initializePositionSaving()     {
			   // Get stored tree structure:
	   $this->stored=unserialize($this->BE_USER->uc['browseTrees'][$this->treeName]);


			   // PM action
			   // (If an plus/minus icon has been clicked, the PM GET var is sent and we must update the stored positions in the tree):
	   $PM = explode('_',t3lib_div::_POST($this->treeName.'_pm'));        // 0: mount key, 1: set/clear boolean, 2: item ID (cannot contain "_"), 3: treeName

	   if (count($PM)==4 && $PM[3]==$this->treeName)   {
		   if (isset($this->MOUNTS[$PM[0]])) {
			   if ($PM[1])     {       // set
					   $this->stored[$PM[0]][$PM[2]]=1;
					   $this->savePosition();
			   } else {        // clear
					   unset($this->stored[$PM[0]][$PM[2]]);
					   $this->savePosition();
			   }
			}
	   	}
	}
	
		/**
	 * Will create and return the HTML code for a browsable tree
	 * Is based on the mounts found in the internal array ->MOUNTS (set in the constructor)
	 *
	 * @return	string		HTML code for the browsable tree
	 */
	function getBrowsableTree($maxDepth=999)	{

			// Get stored tree structure AND updating it if needed according to incoming PM GET var.
		$this->initializePositionSaving();

			// Init done:
		$titleLen=intval($this->BE_USER->uc['titleLen']);
		$treeArr=array();

			// Traverse mounts:
		foreach($this->MOUNTS as $idx => $uid)	{

				// Set first:
			$this->bank=$idx;
			$isOpen = $this->stored[$idx][$uid] || $this->expandFirst;

				// Save ids while resetting everything else.
			$curIds = $this->ids;
			$this->reset();
			$this->ids = $curIds;

				// Set PM icon for root of mount:
			$cmd=$this->bank.'_'.($isOpen?"0_":"1_").$uid.'_'.$this->treeName;
			$icon='<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/ol/'.($isOpen?'minus':'plus').'only.gif','width="18" height="16"').' alt="" />';
			$firstHtml= $this->PM_ATagWrap($icon,$cmd);

				// Preparing rootRec for the mount
			if ($uid)	{
				$rootRec = $this->getRecord($uid);
				$firstHtml.=$this->getIcon($rootRec);
			} else {
					// Artificial record for the tree root, id=0
				$rootRec = $this->getRootRecord($uid);
				$firstHtml.=$this->getRootIcon($rootRec);
			}

			if (is_array($rootRec))	{
					// Add the root of the mount to ->tree
				$this->tree[]=array('HTML'=>$firstHtml,'row'=>$rootRec,'bank'=>$this->bank);

					// If the mount is expanded, go down:
				if ($isOpen)	{
						// Set depth:
					$depthD='<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/ol/blank.gif','width="18" height="16"').' alt="" />';
					if ($this->addSelfId)	$this->ids[] = $uid;
					$this->getTree($uid,$maxDepth,$depthD);
				}

					// Add tree:
				$treeArr=array_merge($treeArr,$this->tree);
			}
		}
		return $this->printTree($treeArr);
	}
}
	
class tx_mhbranchenbuch_treeview {

	function displayCategoryTree($PA, $fobj)    {        
        
		$table = $PA['table'];
		$field = $PA['field'];
		$row = $PA['row'];

		$this->pObj = $PA['pObj'];

			// Field configuration from TCA:
		$config = $PA['fieldConf']['config'];
      
			// it seems TCE has a bug and do not work correctly with '1'
		$config['maxitems'] = ($config['maxitems']==2) ? 1 : $config['maxitems'];

			// Getting the selector box items from the system
		$selItems = $this->pObj->addSelectOptionsToItemArray($this->pObj->initItemArray($PA['fieldConf']),$PA['fieldConf'],$this->pObj->setTSconfig($table,$row),$field);
		$selItems = $this->pObj->addItems($selItems,$PA['fieldTSConfig']['addItems.']);
		if ($config['itemsProcFunc']) $selItems = $this->pObj->procItems($selItems,$PA['fieldTSConfig']['itemsProcFunc.'],$config,$table,$row,$field);

			// Possibly remove some items:
		$removeItems=t3lib_div::trimExplode(',',$PA['fieldTSConfig']['removeItems'],1);

		foreach($selItems as $tk => $p)	{
			if (in_array($p[1],$removeItems))	{
				unset($selItems[$tk]);
			} else if (isset($PA['fieldTSConfig']['altLabels.'][$p[1]])) {
				$selItems[$tk][0]=$this->pObj->sL($PA['fieldTSConfig']['altLabels.'][$p[1]]);
			}

				// Removing doktypes with no access:
			if ($table.'.'.$field == 'pages.doktype')	{
				if (!($GLOBALS['BE_USER']->isAdmin() || t3lib_div::inList($GLOBALS['BE_USER']->groupData['pagetypes_select'],$p[1])))	{
					unset($selItems[$tk]);
				}
			}
		}		
		
			// Creating the label for the "No Matching Value" entry.
		$nMV_label = isset($PA['fieldTSConfig']['noMatchingValue_label']) ? $this->pObj->sL($PA['fieldTSConfig']['noMatchingValue_label']) : '[ '.$this->pObj->getLL('l_noMatchingValue').' ]';
		$nMV_label = @sprintf($nMV_label, $PA['itemFormElValue']);

			// Prepare some values:
		$maxitems = intval($config['maxitems']);
		$minitems = intval($config['minitems']);
		$size = intval($config['size']);
		
			// build tree selector		
		$item.= '<input type="hidden" name="'.$PA['itemFormElName'].'_mul" value="'.($config['multiple']?1:0).'" />';
	
			// Set max and min items:
		$maxitems = t3lib_div::intInRange($config['maxitems'],0);
		if (!$maxitems)	$maxitems=100000;
		$minitems = t3lib_div::intInRange($config['minitems'],0);
	
			// Register the required number of elements:
		$this->pObj->requiredElements[$PA['itemFormElName']] = array($minitems,$maxitems,'imgName'=>$table.'_'.$row['uid'].'_'.$field);

		if($config['treeView'] AND $config['foreign_table']) {
			global $TCA, $LANG;

			if ($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mh_branchenbuch']) { 
				$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mh_branchenbuch']);
			}

			if($config['treeViewClass'] AND is_object($treeViewObj = &t3lib_div::getUserObj($config['treeViewClass'],'user_',false)))      {
			} else {
				$treeViewObj = t3lib_div::makeInstance('tx_mhbranchenbuch_tceFunc_selectTreeView');
			}
			
      $tempArr = 'tt_content:' . $row['uid'];
			
			$treeViewObj->table = $config['foreign_table'];
			$treeViewObj->clause = $PA['pObj']->cachedTSconfig[$tempArr]['_STORAGE_PID'] > 0 ? ' AND pid=' . $PA['pObj']->cachedTSconfig[$tempArr]['_STORAGE_PID'] : ' AND pid=' . intval($row['pid']);
			$treeViewObj->init($SPaddWhere);
			$treeViewObj->backPath = $this->pObj->backPath;
			$treeViewObj->parentField = $TCA[$config['foreign_table']]['ctrl']['treeParentField'];
			$treeViewObj->maxDepth = $config['treeMaxDepth'];
			$treeViewObj->expandAll = 0;
			$treeViewObj->expandFirst = 1;
			$treeViewObj->fieldArray = array('uid','name'); // those fields will be filled to the array $treeViewObj->tree
			$treeViewObj->ext_IconMode = '1'; // no context menu on icons
      $treeViewObj->title = $LANG->sL($TCA[$config['foreign_table']]['ctrl']['title']);	
			$treeViewObj->thisScript = 'alt_doc.php';
			$treeViewObj->treeName = $config['treeName'];
			$treeViewObj->hiddenField = '<input type="hidden" name="'.$config['treeName'].'_pm" value="">'; 
			$treeViewObj->TCEforms_itemFormElName = $PA['itemFormElName'];
			if ($table==$config['foreign_table']) {
				$treeViewObj->TCEforms_nonSelectableItemsArray[] = $row['uid'];
			}
      
				// get default items
			$defItems = array();
			if (is_array($config['items']) && $table == 'tt_content' && $row['CType']=='list' && $row['list_type']==9 && $field == 'pi_flexform')	{
				reset ($config['items']);
				while (list($itemName,$itemValue) = each($config['items']))	{
					if ($itemValue[0]) {
						$ITitle = $this->pObj->sL($itemValue[0]);
						$defItems[] = '<a href="#" onclick="setFormValueFromBrowseWin(\'data['.$table.']['.$row['uid'].']['.$field.'][data][sDEF][lDEF][categorySelection][vDEF]\','.$itemValue[1].',\''.$ITitle.'\'); return false;" style="text-decoration:none;">'.$ITitle.'</a>';
					}
				}
			}
				
			$treeContent = '<script type="text/javascript">
								function set'.$config['treeName'].'PM(pm) {
									document.editform.'.$config['treeName'].'_pm.value = pm;							
								}
                                function setFormValueFromBrowseWin(fName,value,label)	{	//
                                    var formObj = setFormValue_getFObj(fName)
                                    if (formObj && value!="--div--")	{
                                        fObj = formObj[fName+"_list"];
                                            // Inserting element
                                        var l=fObj.length;
                                        var setOK=1;
                                        if (!formObj[fName+"_mul"] || formObj[fName+"_mul"].value==0)	{
                                            for (a=0;a<l;a++)	{
                                                if (fObj.options[a].value==value)	{
                                                    setOK=0;
                                                }
                                            }
                                        }
                                        if (setOK)	{
                                            fObj.length++;
                                            fObj.options[l].value=value;
                                            fObj.options[l].text=unescape(label);
                    
                                                // Traversing list and set the hidden-field
                                            setHiddenFromList(fObj,formObj[fName]);
                                            TBE_EDITOR_fieldChanged_fName(fName,formObj[fName+"_list"]);
                                        }
                                    }
                                }
                                function setFormValue_getFObj(fName)	{	//
                                    var formObj = document.editform;
                                    if (formObj)	{
                                        if (formObj[fName] && formObj[fName+"_list"] && formObj[fName+"_list"].type=="select-multiple")	{
                                            return formObj;
                                        } else {
                                            alert("Formfields missing:\n fName: "+formObj[fName]+"\n fName_list:"+formObj[fName+"_list"]+"\n type:"+formObj[fName+"_list"].type+"\n fName:"+fName);
                                        }
                                    }
                                    return "";
                                }
                                function setFormValueManipulate(fName,type)	{	//
                                        var formObj = setFormValue_getFObj(fName)
                                        if (formObj)	{
                                            var localArray_V = new Array();
                                            var localArray_L = new Array();
                                            var localArray_S = new Array();
                                            var fObjSel = formObj[fName+"_list"];
                                            var l=fObjSel.length;
                                            var c=0;
                                            if (type=="Remove" || type=="Top" || type=="Bottom")	{
                                                if (type=="Top")	{
                                                    for (a=0;a<l;a++)	{
                                                        if (fObjSel.options[a].selected==1)	{
                                                            localArray_V[c]=fObjSel.options[a].value;
                                                            localArray_L[c]=fObjSel.options[a].text;
                                                            localArray_S[c]=1;
                                                            c++;
                                                        }
                                                    }
                                                }
                                                for (a=0;a<l;a++)	{
                                                    if (fObjSel.options[a].selected!=1)	{
                                                        localArray_V[c]=fObjSel.options[a].value;
                                                        localArray_L[c]=fObjSel.options[a].text;
                                                        localArray_S[c]=0;
                                                        c++;
                                                    }
                                                }
                                                if (type=="Bottom")	{
                                                    for (a=0;a<l;a++)	{
                                                        if (fObjSel.options[a].selected==1)	{
                                                            localArray_V[c]=fObjSel.options[a].value;
                                                            localArray_L[c]=fObjSel.options[a].text;
                                                            localArray_S[c]=1;
                                                            c++;
                                                        }
                                                    }
                                                }
                                            }
                                            if (type=="Down")	{
                                                var tC = 0;
                                                var tA = new Array();
                        
                                                for (a=0;a<l;a++)	{
                                                    if (fObjSel.options[a].selected!=1)	{
                                                            // Add non-selected element:
                                                        localArray_V[c]=fObjSel.options[a].value;
                                                        localArray_L[c]=fObjSel.options[a].text;
                                                        localArray_S[c]=0;
                                                        c++;
                        
                                                            // Transfer any accumulated and reset:
                                                        if (tA.length > 0)	{
                                                            for (aa=0;aa<tA.length;aa++)	{
                                                                localArray_V[c]=fObjSel.options[tA[aa]].value;
                                                                localArray_L[c]=fObjSel.options[tA[aa]].text;
                                                                localArray_S[c]=1;
                                                                c++;
                                                            }
                        
                                                            var tC = 0;
                                                            var tA = new Array();
                                                        }
                                                    } else {
                                                        tA[tC] = a;
                                                        tC++;
                                                    }
                                                }
                                                    // Transfer any remaining:
                                                if (tA.length > 0)	{
                                                    for (aa=0;aa<tA.length;aa++)	{
                                                        localArray_V[c]=fObjSel.options[tA[aa]].value;
                                                        localArray_L[c]=fObjSel.options[tA[aa]].text;
                                                        localArray_S[c]=1;
                                                        c++;
                                                    }
                                                }
                                            }
                                            if (type=="Up")	{
                                                var tC = 0;
                                                var tA = new Array();
                                                var c = l-1;
                        
                                                for (a=l-1;a>=0;a--)	{
                                                    if (fObjSel.options[a].selected!=1)	{
                        
                                                            // Add non-selected element:
                                                        localArray_V[c]=fObjSel.options[a].value;
                                                        localArray_L[c]=fObjSel.options[a].text;
                                                        localArray_S[c]=0;
                                                        c--;
                        
                                                            // Transfer any accumulated and reset:
                                                        if (tA.length > 0)	{
                                                            for (aa=0;aa<tA.length;aa++)	{
                                                                localArray_V[c]=fObjSel.options[tA[aa]].value;
                                                                localArray_L[c]=fObjSel.options[tA[aa]].text;
                                                                localArray_S[c]=1;
                                                                c--;
                                                            }
                        
                                                            var tC = 0;
                                                            var tA = new Array();
                                                        }
                                                    } else {
                                                        tA[tC] = a;
                                                        tC++;
                                                    }
                                                }
                                                    // Transfer any remaining:
                                                if (tA.length > 0)	{
                                                    for (aa=0;aa<tA.length;aa++)	{
                                                        localArray_V[c]=fObjSel.options[tA[aa]].value;
                                                        localArray_L[c]=fObjSel.options[tA[aa]].text;
                                                        localArray_S[c]=1;
                                                        c--;
                                                    }
                                                }
                                                c=l;	// Restore length value in "c"
                                            }
                        
                                                // Transfer items in temporary storage to list object:
                                            fObjSel.length = c;
                                            for (a=0;a<c;a++)	{
                                                fObjSel.options[a].value = localArray_V[a];
                                                fObjSel.options[a].text = localArray_L[a];
                                                fObjSel.options[a].selected = localArray_S[a];
                                            }
                                            setHiddenFromList(fObjSel,formObj[fName]);
                        
                                            '.$this->TBE_EDITOR_fieldChanged_func.'
                                        }
                                    } 
                                    function setHiddenFromList(fObjSel,fObjHid)	{	//
                                        l=fObjSel.length;
                                        fObjHid.value="";
                                        for (a=0;a<l;a++)	{
                                            fObjHid.value+=fObjSel.options[a].value+",";
                                        }
                                    }                                    
							</script>';	
				
				// render tree html
			$treeContent.=$treeViewObj->getBrowsableTree($treeViewObj->maxDepth);
	
			$treeItemC = count($treeViewObj->ids);

			if ($defItems[0]) { // add default items to the tree table. In this case the value [not categorized]
				$treeItemC += count($defItems);
				$treeContent .= '<table border="0" cellpadding="0" cellspacing="0"><tr>
					<td>' . $this->pObj->sL($config['itemsHeader']).'&nbsp;</td><td>'.implode($defItems,'<br />').'</td>
					</tr></table>';
			}

			$width = 320; // default width for the field with the category tree
			if (intval($confArr['categoryTreeWidth'])) { // if a value is set in extConf take this one.
				$width = t3lib_div::intInRange($confArr['categoryTreeWidth'],1,600);
			} elseif ($GLOBALS['CLIENT']['BROWSER']=='msie') { // to suppress the unneeded horizontal scrollbar IE needs a width of at least 320px
				$width = 320;
			}

			$config['autoSizeMax'] = t3lib_div::intInRange($config['autoSizeMax'],0);			
      
            $height = $config['autoSizeMax'] ? t3lib_div::intInRange($treeItemC+2,t3lib_div::intInRange($size,1),$config['autoSizeMax']) : $size;
				// hardcoded: 16 is the height of the icons
			$height=$height*16;

			$divStyle = 'position:relative; left:0px; top:0px; height:'.$height.'px; width:'.$width.'px;border:solid 1px;overflow:auto;background:#fff;margin-bottom:5px;';
			$thumbnails='<div  name="'.$PA['itemFormElName'].'_selTree" style="'.htmlspecialchars($divStyle).'">';
			$thumbnails.=$treeContent.$treeViewObj->hiddenField;
			$thumbnails.='</div>';

		} else {

			$sOnChange = 'setFormValueFromBrowseWin(\''.$PA['itemFormElName'].'\',this.options[this.selectedIndex].value,this.options[this.selectedIndex].text); '.implode('',$PA['fieldChangeFunc']);

				// Put together the select form with selected elements:
			$selector_itemListStyle = isset($config['itemListStyle']) ? ' style="'.htmlspecialchars($config['itemListStyle']).'"' : ' style="'.$this->pObj->defaultMultipleSelectorStyle.'"';
			$size = $config['autoSizeMax'] ? t3lib_div::intInRange(count($itemArray)+1,t3lib_div::intInRange($size,1),$config['autoSizeMax']) : $size;
			$thumbnails = '<select style="width:250px;" name="'.$PA['itemFormElName'].'_sel"'.$this->pObj->insertDefStyle('select').($size?' size="'.$size.'"':'').' onchange="'.htmlspecialchars($sOnChange).'"'.$PA['onFocus'].$selector_itemListStyle.'>';
			foreach($selItems as $p)	{
				$thumbnails.= '<option value="'.htmlspecialchars($p[1]).'">'.htmlspecialchars($p[0]).'</option>';
			}
			$thumbnails.= '</select>';

		}
				
			// Perform modification of the selected items array:
		$itemArray = t3lib_div::trimExplode(',',$PA['itemFormElValue'],1);
		foreach($itemArray as $tk => $tv) {
			$tvP = explode('|',$tv,2);
			if (in_array($tvP[0],$removeItems) && !$PA['fieldTSConfig']['disableNoMatchingValueElement'])	{
				$tvP[1] = rawurlencode($nMV_label);
			} elseif (isset($PA['fieldTSConfig']['altLabels.'][$tvP[0]])) {
				$tvP[1] = rawurlencode($this->pObj->sL($PA['fieldTSConfig']['altLabels.'][$tvP[0]]));
			} else {
				$tvP[1] = rawurlencode($this->pObj->sL(rawurldecode($tvP[1])));
			}
			$itemArray[$tk]=implode('|',$tvP);
		}
		$sWidth = 220; // default width for the left field of the category select
		if (intval($confArr['categorySelectedWidth'])) {
			$sWidth = t3lib_div::intInRange($confArr['categorySelectedWidth'],1,600);
		}
       
		$params=array(
			'size' => $size,
			'autoSizeMax' => t3lib_div::intInRange($config['autoSizeMax'],0),
			'style' => ' style="width:'.$sWidth.'px;"',
			'dontShowMoveIcons' => ($maxitems<=1),
			'maxitems' => $maxitems,
			'info' => '',
			'headers' => array(
				'selector' => $this->pObj->getLL('l_selected').':<br />',
				'items' => $this->pObj->getLL('l_items').':<br />'
			),
			'setValue' => 'append',
			'noBrowser' => 1,
			'thumbnails' => $thumbnails
		);
		$item.= $this->pObj->dbFileIcons($PA['itemFormElName'],'','',$itemArray,'',$params,$PA['onFocus']);
		// Wizards:
		$altItem = '<input type="hidden" name="'.$PA['itemFormElName'].'" value="'.htmlspecialchars($PA['itemFormElValue']).'" />';
		$item = $this->pObj->renderWizards(array($item,$altItem),$config['wizards'],$table,$row,$field,$PA,$PA['itemFormElName'],$specConf);
		
		return $this->NA_Items.$item;
	
	}

}