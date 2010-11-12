<?php

$sTitle = empty($vars["#title"]) ? "" : $vars["#title"];
?>
<div id="div-<?=$vars["#id"]?>" <? if (isset($vars["#attributes"])) { echo FlexiStringUtil::attributesToString($vars["#attributes"]); } ?>>
<?=isset($vars["#prefix"]) ? $vars["#prefix"] : ""; ?>
	<fieldset id="<?=$vars["#id"]?>">
		<legend><?=$sTitle?></legend>
		<?=$vars["#childs"]?>
	</fieldset>
<?=isset($vars["#suffix"]) ? $vars["#suffix"] : "" ?>
</div>
