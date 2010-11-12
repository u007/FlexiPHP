<?php

$mValue = isset($vars["#value"]) ? $vars["#value"] : null;

$bDisabled = isset($vars["#disabled"]) ? $vars["#disabled"] : false;
?>
<?=isset($vars["#prefix"]) ? $vars["#prefix"] : ""; ?>
	<input type="image" src="<?=$vars["#src"]?>" name="<?=$vars["#name"]?>" <?=is_null($mValue) ? "" : " value=\"" . $mValue . "\""?>
		<?=empty($vars["#id"]) ? "" : " id=\"" . $vars["#id"] . "\""?><?=$bDisabled ? " disabled=\"disabled\"" : ""?>
		<? if (isset($vars["#attributes"])) { echo FlexiStringUtil::attributesToString($vars["#attributes"]); } ?>>
	<br/>
	<? if(isset($vars["#description"])) { ?>
	<div class="flexiphp_div_description"><?=$vars["#description"]?></div>
	<? } ?>
<?=isset($vars["#suffix"]) ? $vars["#suffix"] : "" ?>