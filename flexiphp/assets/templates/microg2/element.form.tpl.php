<?php

$sMethod = empty($vars["#method"]) ? "" : " method=\"" . $vars["#method"] . "\"";
$sAction = empty($vars["#action"]) ? "" : " action=\"" . $vars["#action"] . "\"";
$sEncode = empty($vars["#enctype"]) ? $sEncode : " enctype=\"" . $vars["#enctype"] . "\"";
?>
<div id="div-<?=$vars["#id"]?>" class="">
	<form id="<?=$vars["#id"]?>"<?=$sMethod?><?=$sAction?><?=$sEncode?> <? if (isset($vars["#attributes"])) { echo FlexiStringUtil::attributesToString($vars["#attributes"]); } ?>>
<?=isset($vars["#prefix"]) ? $vars["#prefix"] : ""; ?>
<?=$vars["#childs"]?>
<?=isset($vars["#suffix"]) ? $vars["#suffix"] : "" ?>
	</form>
</div>
