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

//reformat for jquery datetime input
$sFormat = isset($vars["#format"]) ? $vars["#format"] : FlexiConfig::$sInputDateTimeFormat;
$sFormat = str_replace(":ss", ":00", $sFormat);

?>
<?=isset($vars["#prefix"]) ? $vars["#prefix"] : ""; ?>
	<input type="text" name="<?=$vars["#name"]?>label" <?=is_null($mValue) ? "" : " value=\"" . $mValue . "\""?><?=$sMaxLen?>
		<?=empty($vars["#id"]) ? "" : " id=\"" . $vars["#id"] . "label\""?><?=$bDisabled ? " disabled=\"disabled\"" : ""?>
		<?=isset($vars["#size"]) ? " size=\"" . $vars["#size"] . "\"": ""?>  
		<? if (isset($vars["#attributes"])) { echo FlexiStringUtil::attributesToString($vars["#attributes"]); } ?>>
  <input type="hidden" name="<?=$vars["#name"]?>" id="<?=$vars["#id"]?>" value="<?=$mValue?>" />
	<? if(isset($vars["#notice"])) { ?>
	<div class="flexiphp_div_notice"><?=$vars["#notice"]["msg"]?></div>
	<? } ?>
	<? if(isset($vars["#description"])) { ?>
	<div class="flexiphp_div_description"><?=$vars["#description"]?></div>
	<? } ?>
	<script type="text/javascript">
	jQuery(document).ready( function() {
    var aConfig = {
    showWeek: true,
    format: '<?=$sFormat ?>',
    altFormat: 'yy-mm-dd hh:ii',
    altField: '#<?=$vars["#id"]?>',
    changeMonth: true,
    changeYear: true
    <?=$sMinDate?>
    <?=$sMaxDate?>
    };

		jQuery("#<?=$vars["#id"]?>label").datetime(aConfig);

    jQuery("#<?=$vars["#id"]?>label").change(function() {
      //aConfig.value = jQuery("#<?=$vars["#id"]?>label").val();
      //jQuery("#<?=$vars["#id"]?>label").datetime(aConfig);
      jQuery("#<?=$vars["#id"]?>label").datetime({value: jQuery("#<?=$vars["#id"]?>label").val()});
    });
	});
	</script>
<?=isset($vars["#suffix"]) ? $vars["#suffix"] : "" ?>
