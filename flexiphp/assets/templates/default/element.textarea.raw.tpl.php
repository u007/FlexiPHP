<?php

$mValue = isset($vars["#value"]) ?
	$vars["#value"] : ( isset($vars["#default_value"]) ? $vars["#default_value"] : null);

$bDisabled = isset($vars["#disabled"]) ? $vars["#disabled"] : false;
$sRequired = isset($vars["#required"]) ?
	($vars["#required"] ? "<span class=\"required\">*</span>": "") : "";
?>
<?=isset($vars["#prefix"]) ? $vars["#prefix"] : ""; ?>
	<textarea name="<?=$vars["#name"]?>"<?=empty($vars["#id"]) ? "" : " id=\"" . $vars["#id"] . "\""?><?=$bDisabled ? " disabled=\"disabled\"" : ""?><?
	?><?=isset($vars["#cols"]) ? " cols=\"" . $vars["#cols"] . "\"": ""?><?=isset($vars["#rows"]) ? " rows=\"" . $vars["#rows"] . "\"": ""?>
	<? if (isset($vars["#attributes"])) { echo FlexiStringUtil::attributesToString($vars["#attributes"]); } ?>><?=is_null($mValue) ? "" : $mValue ?></textarea>
	<br/>
	<? if(isset($vars["#notice"])) { ?>
	<div class="flexiphp_div_notice"><?=$vars["#notice"]["msg"]?></div>
	<? } ?>
	<? if(isset($vars["#description"])) { ?>
	<div class="flexiphp_div_description"><?=$vars["#description"]?></div>
	<? } ?>
<?=isset($vars["#suffix"]) ? $vars["#suffix"] : "" ?>
