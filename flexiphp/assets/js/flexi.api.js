var _targetPickElement = {fieldvalue:"",fieldlabel:""};

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
