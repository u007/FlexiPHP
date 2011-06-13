<?php

$mValue = isset($vars["#value"]) ?
	$vars["#value"] : ( isset($vars["#default_value"]) ? $vars["#default_value"] : null);
$sMaxLen = isset($vars["#maxlength"]) ? " maxlength=\"" . $vars["#maxlength"] . "\"" : "";

$bDisabled = isset($vars["#disabled"]) ? $vars["#disabled"] : false;
$sRequired = isset($vars["#required"]) ?
	($vars["#required"] ? "<span class=\"required\">*</span>": "") : "";
	
$sMinDate = "";
$sMaxDate = "";

if (isset($vars["#mindate"]))
{
	$aDate = explode("-", $vars["#mindate"]);
	$sMinDate = ", minDate: new Date(" . $aDate[0] . ", " . $aDate[1] . ", " . $aDate[2] . ")";
}

if (isset($vars["#maxdate"]))
{
	$aDate = explode("-", $vars["#maxdate"]);
	$sMaxDate = ", maxDate: new Date(" . $aDate[0] . ", " . $aDate[1] . ", " . $aDate[2] . ")";
}

$sFormat = isset($vars["#format"]) ? $vars["#format"] : FlexiConfig::$sInputDateFormat;
$sPHPFormat = "F Y"; //fix double i, which only 1 i(min) in php
$sDisplayValue = "";
if (!empty($mValue)) {
  if (substr($mValue,0, 4)=="0000") {
    //empty date
    $sDisplayValue = "";
  } else {
    $iDatetime = strtotime($mValue);
    $sDisplayValue = date($sPHPFormat, $iDatetime);
  }
}

?>
<?=isset($vars["#prefix"]) ? $vars["#prefix"] : ""; ?>
	<input type="text" name="<?=$vars["#name"]?>" <?=is_null($mValue) ? "" : " value=\"" . $sDisplayValue . "\""?><?=$sMaxLen?>
		<?=empty($vars["#id"]) ? "" : " id=\"" . $vars["#id"] . "\""?><?=$bDisabled ? " disabled=\"disabled\"" : ""?>
		<?=isset($vars["#size"]) ? " size=\"" . $vars["#size"] . "\"": ""?> 
		<? if (isset($vars["#attributes"])) { echo FlexiStringUtil::attributesToString($vars["#attributes"]); } ?>>
  <input type="hidden" name="<?=$vars["#name"]?>" id="<?=$vars["#id"]?>" value="<?=$mValue?>" />
	<? if(isset($vars["#notice"])) { ?>
	<div class="flexiphp_div_notice"><?=$vars["#notice"]["msg"]?></div>
	<? } ?>
	<? if(isset($vars["#description"])) { ?>
	<div class="flexiphp_div_description"><?=$vars["#description"]?></div>
	<? } ?>
  
  <script type="text/javascript" >
  jQuery(document).ready(function($) {
    $(function() {
    jQuery("#<?=$vars["#id"]?>").datepicker( {
    changeMonth: true,
    changeYear: true,
    showButtonPanel: true,
    dateFormat: 'MM yy',
    <?=$sMinDate?>
    <?=$sMaxDate?>
    onClose: function(dateText, inst) {
      var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
      var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
      $(this).datepicker('setDate', new Date(year, month, 1));
    }
    });
    });
  });
  </script>
<?=isset($vars["#suffix"]) ? $vars["#suffix"] : "" ?>
