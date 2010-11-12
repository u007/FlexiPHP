<?php

$mValue = isset($vars["#value"]) ?
	$vars["#value"] : ( isset($vars["#default_value"]) ? $vars["#default_value"] : null);

?>
<div id="div-<?=$vars["#id"]?>" class="flexi_field<?=@$vars["#required"]? " required" : ""?> hidden">
<?=isset($vars["#prefix"]) ? $vars["#prefix"] : ""; ?>
<div class="flexiphp_div_input">
	<input type="hidden" name="<?=$vars["#name"]?>" <?=is_null($mValue) ? "" : " value=\"" . $mValue . "\""?>
		<?=empty($vars["#id"]) ? "" : " id=\"" . $vars["#id"] . "\""?>
		<? if (isset($vars["#attributes"])) { echo FlexiStringUtil::attributesToString($vars["#attributes"]); } ?>>
</div>
<?=isset($vars["#suffix"]) ? $vars["#suffix"] : "" ?>
</div>
