function tx_mhbranchenbuch_hideAll() {
  _$("tx_mhbranchenbuch_keywordsFieldset").className = "hidden";
  _$("tx_mhbranchenbuch_uploadFieldset").className = "hidden";
  _$("tx_mhbranchenbuch_detailedFieldset").className = "hidden";
}



function tx_mhbranchenbuch_getFields(val) {
  var enableFields = typ[val].split(",");
  for (var i = 0; i < enableFields.length; i++) {
    var enableObj = _$("tx_mhbranchenbuch_" + enableFields[i]);
    if (enableObj) {
      enableObj.className = "unhide";
    }
  }
}



function tx_mhbranchenbuch_checkKeywords() {
  var tempStr     = document.tx_mhbranchenbuch_feForm.keywords.value;
  var strLength   = tempStr.split(" ").length;
  var validLength = keywords[_$("tx_mhbranchenbuch_typeCount").value];
  var tempWords   = (validLength-strLength)+1;

  _$("tx_mhbranchenbuch_words").innerHTML = tempWords;
  
  if(strLength <= validLength) {
    tempMemory = tempStr;
  } else {
    document.tx_mhbranchenbuch_feForm.keywords.value = tempMemory;
  }
}



function tx_mhbranchenbuch_resetKeywords() {
  _$("keywords").disabled = false;
}



function tx_mhbranchenbuch_TreeviewSelCat(value,text)  {
  var a = _$("tx_mhtreeview-node_" + value);
  var b = _$("selectedCats").options;
  
  var validLength = category[_$("tx_mhbranchenbuch_typeCount").value];
  
  if(b.length >= validLength) {
    return;
  } else {
    for(var x=0;x<b.length;x++) {
      if(b[x].value == value) { return; }
    }
    a.className="tx_mhtreeview_act";
    newVal = new Option(text);
    b[_$("selectedCats").length] = newVal;
    newVal.value = value;
  }
}



function tx_mhbranchenbuch_selCat(value,text)  {
  var a = _$("tempCats").options;
  var b = _$("selectedCats").options;
  
  for(var i=0;i<a.length;i++) { 
    if(a[i].selected) {
      for(var x=0;x<b.length;x++){
        if(b[x].value == value) { return; }
      }
      newVal = new Option(text,value);
      _$("selectedCats").options[_$("selectedCats").length] = newVal;
      newVal.value = value;
    }
  }
}


 
function tx_mhbranchenbuch_submitCat() {
  var a = _$("selectedCats");
  var b = _$("tx_mhbranchenbuch_category");
  
  var selectedArray = new Array();
  var i;
  var count = 0;
  for (i=0; i<a.options.length; i++) {
    selectedArray[count] = a.options[i].value;
    count++;
  }
  
  b.value = selectedArray.toString();
}



function tx_mhbranchenbuch_delCat(index,value)  {
  var a = _$("selectedCats").options[index];
  var b = _$("tx_mhtreeview-node_" + value);
  
  if(a) {
    b.className = "tx_mhtreeview_no";
    document.getElementById("selectedCats").options[index] = null;
  }
}



function _$(e) {
  if(document.getElementById(e))
    return document.getElementById(e);
  else
    alert (e + " is not available");
}



function MM_jumpMenu(targ,selObj,restore) {
  eval(targ+".location=\'"+selObj.options[selObj.selectedIndex].value+"\'");
  if (restore) selObj.selectedIndex=0;
}
    
    

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
