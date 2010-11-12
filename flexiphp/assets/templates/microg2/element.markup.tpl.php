<?php
$sContent = isset($vars["#value"]) ? $vars["#value"] : "";
?>
<div id="div-<?=$vars["#id"]?>" <? if (isset($vars["#attributes"])) { echo FlexiStringUtil::attributesToString($vars["#attributes"]); } ?>>
<?=isset($vars["#prefix"]) ? $vars["#prefix"] : ""; ?>
<?=$sContent?>
<?=$vars["#childs"]?>
<?=isset($vars["#suffix"]) ? $vars["#suffix"] : "" ?>
</div>
