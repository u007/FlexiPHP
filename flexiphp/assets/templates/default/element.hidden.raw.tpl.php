<?php

$mValue = isset($vars["#value"]) ?
	$vars["#value"] : ( isset($vars["#default_value"]) ? $vars["#default_value"] : null);

?>
<?=isset($vars["#prefix"]) ? $vars["#prefix"] : ""; ?>
	<input type="hidden" name="<?=$vars["#name"]?>" <?=is_null($mValue) ? "" : " value=\"" . $mValue . "\""?>
		<?=empty($vars["#id"]) ? "" : " id=\"" . $vars["#id"] . "\""?>
		<? if (isset($vars["#attributes"])) { echo FlexiStringUtil::attributesToString($vars["#attributes"]); } ?>>
<?=isset($vars["#suffix"]) ? $vars["#suffix"] : "" ?>
