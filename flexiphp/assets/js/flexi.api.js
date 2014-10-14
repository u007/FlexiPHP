var _targetPickElement = {fieldvalue:"",fieldlabel:""};
var _lastnoticetime = new Date().getTime();

function getFormAsArray(target) {
  var aFields = jQuery(target + " input," + target + " select");
  var result = {};
  
  for(var c=0; c < aFields.length; c++) {
    var oField = aFields[c];
    switch(oField.tagName) {
      case "INPUT":
      case "SELECT":
        var sName = oField.getAttribute("name");
        var sValue = jQuery(oField).val();
        break;
      default:
        throw Error("Unknown type: " + oField.tagName)
    }
    
    result[sName] = sValue;
  }//foreach fields
  return result;
}

function getMinuteDiff(startdate, enddate) {
  var diff = enddate.getTime() - startdate.getTime();
  var unit = 1000 * 60;//minute
  
  var result = Math.round(diff / unit);
  return result;
}

function getWeekNo(date) {
  var onejan = new Date(date.getFullYear(),0,1);
  return Math.ceil((((date - onejan) / 86400000) + onejan.getDay()+1)/7);
}

function getDayName(date, shortday) {
  var iDay = date.getDay();
  switch(iDay) {
    case 1:
      return shortday ? "Mon": "Monday";
      break;
    case 2:
      return shortday ? "Tues": "Tuesday";
      break;
    case 3:
      return shortday ? "Wed": "Wednesday";
      break;
    case 4:
      return shortday ? "Thu": "Thursday";
      break;
    case 5:
      return shortday ? "Fri": "Friday";
      break;
    case 6:
      return shortday ? "Sat": "Saturday";
      break;
    case 0:
      return shortday ? "Sun": "Sunday";
      break;
  }
  throw new Error("Invalid date: " + date);
}

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
  var sType = ! type ? "info": type;
  var canclose = sType != "error" && sType != "err";
  cleanOldNotice();
  jQuery(_sNoticeTarget).append(getNoticeHTML(msg,type));
  cleanupNotice(function() {
    jQuery("#divNoticeMsg" + _iNoticeId).fadeIn("slow");
    if (canclose && _iAutoClose > 0) {
      setTimeout('jQuery("#divNoticeMsg' + _iNoticeId + '").hide("slow", function(e) { jQuery("#divNoticeMsg' + _iNoticeId + '").remove(); } )', _iAutoClose*1000);
    }
  });
  
  _lastnoticetime = new Date().getTime();
}

function cleanOldNotice() {
  var time = new Date().getTime();
  if (time - _lastnoticetime > 1000) {
    //not on the same time
    //remove existing
    jQuery(_sNoticeTarget + " div").remove();
    return;
  }
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
  var targetDiv;var iPoint = 0.8;
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

function doFillOptions(target, data) {
	var sLastValue = "";
  if (typeof(target)=="string") {
    sLastValue = jQuery(target).val();
    jQuery(target + " option").remove();
  } else if(typeof(target)=="object") {
    sLastValue = target.val();
    jQuery("option", target).remove();
  }
	//console.log("last value: " + sLastValue);
  var bStartGroup = false;
  var sOption = "";var sLastGroup = "";
  for(var i in data) {
		jQuery(target).append(
    	"<option value='" + i + "'>" + data[i] + "</option>\n"
    );
  }
  
  if (typeof(target)=="string") {
    jQuery(target).val(sLastValue);
  } else if(typeof(target)=="object") {
    target.val(sLastValue);
  }
  
}

function doFillCombo(target, data, bRenderGroup) {
  var sLastValue = jQuery(target).val();
  if (typeof(target)=="string") {
    jQuery(target + " option").remove();
  } else if(typeof(target)=="object") {
    jQuery("option", target).remove();
  }

  var bStartGroup = false;
  var sOption = "";var sLastGroup = "";
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

function doAjaxRenderMultiCheckList(sURL, sTarget, sCheckName, aData, onLoaded, bRenderGroup) {
  doAjaxCall(sURL, aData, "GET", function(sResult) {
    var aResult = eval("(" + sResult + ")");

    if (aResult.status==1) {
      var aData = aResult['return'];
      doFillCheckList(sTarget, sCheckName, aData);
      if (onLoaded) {
        onLoaded();
      }
    }
  });
}

function doFillCheckList(sTarget, sCheckName, aData) {
  var oDiv = jQuery(sTarget);
  var sId = "";
  oDiv.html("");
  for(var c=0; c < aData.length; c++) {
    sId = sCheckName + "_" + (Math.random()*1000);
    oDiv.append("<div><input type=\"checkbox\" value=\"" + aData[c].data + "\" " + 
      "name=\"" + sCheckName + "[]\" id=\"" + sId + "\"/><label for=\"" + sId + "\">" + aData[c].label +
      "</label><div style='clear: both'></div></div>")
  }
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

function showTimeIn12Hours(time) {
  var aTime = time.split(":");
  var iHour = aTime[0]-0;
  var iMin = aTime[1]-0;
  var iSec = aTime.length > 2? aTime[2]-0: 0;
  var sPeriod = iHour >= 12 ? "pm": "am";
  //console.log(aTime[0]);
  //console.log(iHour);
  iHour = iHour > 12 ? iHour-12: iHour;
  //console.log(iHour);
  var sResult = (iHour < 10 ? "0": "") + iHour;
  sResult += ":" + (iMin < 10 ? "0": "") + iMin;
  
  if (aTime.length > 2) {
    sResult += ":" + (iSec < 10 ? "0": "") + iSec;
  }
  sResult += sPeriod;
  
  return sResult;
}

function showDuration(mins) {
  var iHour = parseInt(mins / 60);
  //console.log("hour: " + iHour);
  var iMin = mins - (iHour * 60);
  
  return iHour + "hour" + (iHour > 1 ? "s": "") + ", " +
    iMin + "min" + (iMin > 1 ? "s":"");
}

