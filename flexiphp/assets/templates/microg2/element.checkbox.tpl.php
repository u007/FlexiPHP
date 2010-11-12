<?php

$mValue = isset($vars["#value"]) ?
	$vars["#value"] : ( isset($vars["#default_value"]) ? $vars["#default_value"] : null);

$bDisabled = isset($vars["#disabled"]) ? $vars["#disabled"] : false;

$sChecked = $mValue == $vars["#return_value"] ? " checked=checked" : false;
$sLabel = isset($vars["#label"]) ? $vars["#label"] : "";
$sRequired = isset($vars["#required"]) ?
	($vars["#required"] ? "<span class=\"required\">*</span>": "") : "";
?>
<div id="div-<?=$vars["#id"]?>" class="flexi_field<?=@$vars["#required"]? " required" : ""?>">
<?=isset($vars["#prefix"]) ? $vars["#prefix"] : ""; ?>
<? if (isset($vars["#title"])) { ?>
<div class="flexiphp_div_label">
	
</div>
<? } ?>
<div class="flexiphp_div_input">
	<input type="checkbox" name="<?=$vars["#name"]?>" <?=is_null($mValue) ? "" : " value=\"" . $vars["#return_value"] . "\""?>
		<?=empty($vars["#id"]) ? "" : " id=\"" . $vars["#id"] . "\""?><?=$bDisabled ? " disabled=\"disabled\"" : ""?>
		<?=isset($vars["#size"]) ? " size=\"" . $vars["#size"] . "\"": ""?><?=$sChecked?>
		<? if (isset($vars["#attributes"])) { echo FlexiStringUtil::attributesToString($vars["#attributes"]); } ?>>
		<label <?=isset($vars["#id"]) ? "for=\"" . $vars["#id"] . "\"": "" ?>><?=@$vars["#title"]?><?=$sRequired?></label>
	<br/>
	<? if(isset($vars["#notice"])) { ?>
	<div class="flexiphp_div_notice"><?=$vars["#notice"]["msg"]?></div>
	<? } ?>
	<? if(isset($vars["#description"])) { ?>
	<div class="flexiphp_div_description"><?=$vars["#description"]?></div>
	<? } ?>
</div>
<?=isset($vars["#suffix"]) ? $vars["#suffix"] : "" ?>
</div>
