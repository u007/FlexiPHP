<?php
$sContent = isset($vars["#title"]) ? $vars["#title"] : "";

if (!isset($vars["#attributes"])) { $vars["#attributes"] = array(); } 

$sClass = isset($vars["#attributes"]["class"]) ? $vars["#attributes"]["class"] : "";
$sClass = empty($sClass) ? "flexiphp_div_title" : " flexiphp_div_title";

$vars["#attributes"]["class"] = $sClass;

?>
<h3 id="div-<?=$vars["#id"]?>" <? if (isset($vars["#attributes"])) { echo FlexiStringUtil::attributesToString($vars["#attributes"]); } ?>>
<?=isset($vars["#prefix"]) ? $vars["#prefix"] : ""; ?>
<?=$sContent?>
<?=isset($vars["#suffix"]) ? $vars["#suffix"] : "" ?>
</h3>
