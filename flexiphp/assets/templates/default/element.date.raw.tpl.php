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


?>
<?=isset($vars["#prefix"]) ? $vars["#prefix"] : ""; ?>
	<input type="text" name="<?=$vars["#name"]?>" <?=is_null($mValue) ? "" : " value=\"" . $mValue . "\""?><?=$sMaxLen?>
		<?=empty($vars["#id"]) ? "" : " id=\"" . $vars["#id"] . "\""?><?=$bDisabled ? " disabled=\"disabled\"" : ""?>
		<?=isset($vars["#size"]) ? " size=\"" . $vars["#size"] . "\"": ""?> 
		<? if (isset($vars["#attributes"])) { echo FlexiStringUtil::attributesToString($vars["#attributes"]); } ?>>
	<br/>
	<? if(isset($vars["#notice"])) { ?>
	<div class="flexiphp_div_notice"><?=$vars["#notice"]["msg"]?></div>
	<? } ?>
	<? if(isset($vars["#description"])) { ?>
	<div class="flexiphp_div_description"><?=$vars["#description"]?></div>
	<? } ?>
	<script type="text/javascript">
	jQuery(document).ready( function() {
		jQuery("#<?=$vars["#id"]?>").datepicker({
			showWeek: true,
			dateFormat: '<?=isset($vars["#format"]) ? $vars["#format"] : FlexiConfig::$sInputDateFormat ?>',
			altFormat: 'yy-mm-dd',
			changeMonth: true,
			changeYear: true
			<?=$sMinDate?>
			<?=$sMaxDate?>
			});
	});
	</script>
<?=isset($vars["#suffix"]) ? $vars["#suffix"] : "" ?>
