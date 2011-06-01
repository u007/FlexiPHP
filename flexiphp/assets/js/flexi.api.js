var _targetPickElement = {fieldvalue:"",fieldlabel:""};


function resetFields(target) {
  jQuery("#" + target + " input[type=textfield]").val("");
  jQuery("#" + target + " input[type=file]").val("");
  jQuery("#" + target + " select").val("");
}

function switchTab(target, tabno) {
  var sType = "jQuery";
  var oTarget = jQuery("#" + target);

  if (oTarget.length > 0) {
    if (oTarget.hasClass("yui-navset")) {
      if (!oTarget.hasClass("yui3-tabview-content")) {
        //console.log('not loaded');
        return; //ignore, not loaded
      }
      sType = "YUI";
    } else if (jQuery("ul.idTabs", oTarget).length > 0) {
      oTarget = jQuery("ul.idTabs", oTarget);
      sType = "idTabs";
    }
  }
  switch(sType) {
    case "idTabs":
      oTarget.idTabs(tabno); 
      break;
    case "YUI":
      eval(target).selectChild(tabno);
      break;
    case "jQuery":
      if (oTarget.length > 0) {
        oTarget.tabs( "select" , tabno);
      }
      break;
    default:
      if (console) {
        if (console.log) {
          console.log("Unknown tab: " . target);
        }
      }
  }//switch
}

/**
 * load a url to a div
 *  with prefix and tab name based on prefix of viewname
 */
function openSubLink(sViewPrefix, target, url, tabno) {
  jQuery("#"+sViewPrefix + target).html("Loading...");
  jQuery("#"+sViewPrefix + target).load(url);
  switchTab(sViewPrefix +"tabs", tabno);
}

function numberFormat(iValue, decimal, thousandsep) {
  decimal = !decimal ? 0: decimal;
  iValue = parseFloat(iValue);
  iValue = iValue.toFixed(decimal);
  return addCommas(iValue, thousandsep);
}


function addCommas(nStr, sep)
{
  sep = !sep ? "," : sep;
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + sep + '$2');
	}
	return x1 + x2;
}
//deleteRowAfter index?
//fillRow - single row?

function fillRows(target, aData, tdAttribute) {
  var aRows = jQuery(target + " tbody tr");
  var cnt = 0;

  if (!tdAttribute) {
    tdAttribute = "";
  }
  for(cnt=0; cnt < aRows.length; cnt++) {
    var aCol = jQuery(aRows.selector + " td");
    for(var col=0; col < aCol.length; col++) {
      aCol[0].innerHTML = aData[cnt][col];
    }
  }
  var oTable = jQuery(target + " tbody");
  //adding extra
  for(;cnt < aData.length; cnt++) {
    var sRow = "";
    for(var col=0; col < aData[cnt].length; col++) {
      sRow += "<td " + tdAttribute + ">" + aData[cnt][col] + "</td>";
    }
    oTable.append("<tr>" + sRow + "</tr>");
  }

  //removing extra
  while(aRows.length > aData.length) {
     jQuery(target + " tbody tr:nth-child(" + aData.length + ")").remove();
     aRows = jQuery(target + " tbody tr");
  }
}


function getDateStringFromSQLDateTime(datetime) {
  var aTime = [];
  if (!datetime) return null;
  if (datetime.length < 1) return '0000-00-00';
  aTime = datetime.split(' ');
  if (aTime.length >=1) {
    return aTime[0];
  }
  return "0000-00-00";
}

function getTimeStringFromSQLDateTime(datetime) {
  var aTime = [];
  if (!datetime) return null;
  if (datetime.length < 1) return '00:00:00';
  aTime = datetime.split(' ');
  if (aTime.length >=2) {
    return aTime[1];
  }
  return "00:00:00";
}

function appendGritterNote(msg, title, sticky, duration) {
  var sticky = sticky===undefined ? false: sticky;
  var title = title === undefined ? "Info": title;
  var duration = duration===undefined ? "": duration;
  
  jQuery.gritter.add({
    title: title,
    text: msg,
    sticky: sticky,
    time: duration
  });
}

function appendNotice(msg, type) {
  jQuery(_sNoticeTarget).append(getNoticeHTML(msg,type));
  cleanupNotice(function() {
    jQuery("#divNoticeMsg" + _iNoticeId).fadeIn("slow");
  });
}

function cleanupNotice(event) {
  if(jQuery(_sNoticeTarget + " div").length > _iNoticeMax) {
    jQuery(_sNoticeTarget + " div:first").fadeOut(100, function() {
      jQuery(_sNoticeTarget + " div:first").remove();

      if (jQuery(_sNoticeTarget + " div").length > _iNoticeMax) {
        setTimeout("cleanupNotice()", 500);
      } else {
        if (event) {
          event();
        }
      }
    });
  } else {
    if (event) {
      event();
    }
  }
  //fade half all old post
  var aDiv = jQuery(_sNoticeTarget + " div");
  var targetDiv; var iPoint = 0.8;
  for(var c=aDiv.length-1; c >= 0 ; c--) {
    targetDiv = jQuery(aDiv[c]);
    targetDiv.css("opacity", iPoint);
    targetDiv.css("filter", "alpha(opacity=" + (iPoint*10) + ")");
    iPoint -= iPoint <= 0.1 ? 0: 0.2; //min 0.1
  }
}

function getNoticeHTML(msg, type) {
  var sType = ! type ? "info": type;
  sType = sType.substr(0, 1).toUpperCase() + sType.substr(1);
  _iNoticeId++;
  var sHTML = "<div id='divNoticeMsg" + _iNoticeId + "' class='notice" + sType + "' style='display: none;'>" +
    msg +
    "</div>\n";
  return sHTML;
}

function cleanSep(value, sep, replace) {
  if (!value) return value;
  var result = ""
  var aValue = value.split(sep)
  result = aValue.join(replace);
  return result;
}

function strReplace(sFind, sReplace, sTarget) {
  if (!sTarget) return sTarget;

  return sTarget.replace(eval("/" + sFind + "/g"), sReplace)
}

function toogleCheckAll(check, target, sPrefix) {
  //toggle all checkbox based on target.checked, but must start with sprefix input name
  if (check.checked) {
    jQuery(target + " input[name*=check]").attr("checked", "checked");
  } else {
    jQuery(target + " input[name*=check]").removeAttr("checked");
  }
}

function doComboSelect(target, row, fieldlabel, fieldvalue) {
  var aList = jQuery(target + " option");
  
  var bFound=false;
  if (!aList.length) {
    if(jQuery(target).length < 1) {
      showError("doComboSelect: Target not found:" + target);
      return false;
    }
  } else {
    for(var c=0; c < aList.length; c++) {
      if (aList[c].value == row[fieldvalue]) {
        bFound=true;
        break;
      }
    }
  }
  
  if (!bFound) {
    var oOption = jQuery("<option>" + row[fieldlabel] + "</option>");
    jQuery(oOption).attr("value", row[fieldvalue]);
    jQuery(target).append(oOption);
  }
  
  jQuery(target).val(row[fieldvalue]);
  jQuery.colorbox.close();
  return true;
}

function doFillCombo(target, data, bRenderGroup) {
  var sLastValue = jQuery(target).val();
  if (typeof(target)=="string") {
    jQuery(target + " option").remove();
  } else if(typeof(target)=="object") {
    jQuery("option", target).remove();
  }

  var bStartGroup = false;
  var sOption = ""; var sLastGroup = "";
  for(var i=0; i < data.length; i++) {

    if (data[i].label) {
      //contains optgroup info
      if (bRenderGroup && data[i].optgroup) {
        //is not same group, if has existing, close it and append
        if (data[i].optgroup != sLastGroup) {
          if (sOption != "") {
            jQuery(target).append(sOption+"</optgroup>\n");
            sOption="";
          }
          if (data[i].optgroup != "") {
            sOption = "<optgroup label='" + data[i].label + "'>\n";
          }
        } else {
          //same as last group
          sOption +="<option value='" + data[i].data + "'>" + data[i].label + "</option>\n";
        }

        if (data[i].optgroup=="") {
          //is empty group = no group, directlly append
          jQuery(target).append(sOption);
          sOption = ""; //reset group opt
        } else if (i==data.length-1) {
          //is last row
          jQuery(target).append(sOption+"</optgroup>\n");
        }
        sLastGroup = data[i].optgroup;
      } else {
        jQuery(target).append(
          "<option value='" + data[i].data + "'>" + data[i].label + "</option>\n"
        );
      }
    } else {
      jQuery(target).append(
        "<option value='" + i + "'>" + data[i] + "</option>\n"
      );
    }
  }
  jQuery(target).val(sLastValue);
}

function jsonEncodeObject(target, delimiter) {
  var delimiter = !delimiter ? "\"": delimiter;
  if (typeof(target) == "object") {
    var aResult = [];
    for (i in target) {
      if (target[i] && typeof target[i] == "object") {
        aResult[aResult.length] = delimiter+i+delimiter + ": " + jsonEncodeObject(target[i], delimiter) + "";
      } else aResult[aResult.length] = delimiter+i+delimiter + ": " + delimiter + target[i] + delimiter;
    }
    var result = "{" + aResult.join(",") + "}";
    //console.log(result);
    return result;
  } else {
    return delimiter + target + delimiter;
  }
}

function objectToURL(object) {
  var aResult = [];
  for(i in object) {
    aResult[aResult.length] = i + "=" + escape(object[i]);
  }
  return aResult.join("&");
}

function cloneObject(target) {
  var newObj = (target instanceof Array) ? [] : {};
  for (i in target) {
    if (target[i] && typeof target[i] == "object") {
      newObj[i] = cloneObject(target[i]);
    } else newObj[i] = target[i];
  }
  return newObj;
}

function doAjaxRenderOptionList(sURL, sTarget, aData, onLoaded, bRenderGroup) {
  doAjaxCall(sURL, aData, "GET", function(sResult) {
    var aResult = eval("(" + sResult + ")");

    if (aResult.status==1) {
      var aData = aResult['return'];
      doFillCombo(sTarget, aData, bRenderGroup);
      if (onLoaded) {
        onLoaded();
      }
    }
  });
}

function doAjaxLoadHTML(sURL, aData, sMethod, sTarget) {
  doAjaxCall(sURL, aData, sMethod, function(sResult) {
    jQuery(sTarget).html(sResult);
  });
}

function doAjaxCall(sURL, aData, sMethod, sCallBack) {
   jQuery.ajax({
		type: sMethod,
      data: aData,
    	url: sURL,
    	success: sCallBack
    });
}

function doSubmitAjaxForm(sForm, sURL, sMethod, sCallBack) {
  var aData = "";

  var aInputs = jQuery(sForm + " input, " + sForm + " select, " +
    sForm + " textarea");

  aInputs.each(function(index) {
    if (jQuery(this).attr("name") != null && jQuery(this).attr("name") != "") {
      aData += jQuery(this).attr("name") + "=" + jQuery(this).val() + "&";
    }
  });

  jQuery.ajax({
		type: sMethod,
      data: aData,
    	url: sURL,
    	success: sCallBack
    });
}

function getAjaxErrorHTML(aError) {
  var sResult = "";
  
  for(var c=0; c < aError.length; c++) {
    sResult += "<div class='noticeError'>" +
       aError[c]["msg"] +
       "</div>\n";
  }

  return sResult;
}

function doCheckAllCheckBox(sName, oInput)
{
	jQuery(sName).attr('checked', oInput.checked);
}

function doFormOperation(sOperation, sForm)
{
	jQuery(sForm + " input[name=op]").val(sOperation);
	//todo check if got checked
	
	jQuery(sForm).submit();
}

function doSelectorPick(sValue, sLabel) {
  //_targetPickElement
  //console.log(jQuery(_targetPickElement["fieldvalue"]));
  jQuery(_targetPickElement["fieldvalue"]).val(sValue);
  jQuery(_targetPickElement["fieldlabel"]).html(sLabel);
}

function doApplyPopup(sTargetValueField, sTargetLabelField, sURL, aiWidth, aiHeight) {
  _targetPickElement = {fieldvalue: sTargetValueField,fieldlabel: sTargetLabelField};
  iWidth = !aiWidth ? 600: aiWidth;
  iHeight = !aiHeight ? 450: aiHeight;
  
  jQuery(document).ready(function() {
      jQuery(sTarget).colorbox({iframe:true, href: sURL,
        width: iWidth, height: iHeight});
    })
}

function showError(sError) {
  alert(sError);
}