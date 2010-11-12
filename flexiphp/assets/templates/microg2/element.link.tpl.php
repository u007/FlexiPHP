<?php

$sTarget = isset($vars["#target"]) ? " target=\"" . $vars["#target"] . "\"" : "";
?>
<?=isset($vars["#prefix"]) ? $vars["#prefix"] : ""; ?>
	<a href="<?=flexiURL($vars["#path"])?>"<?=$sTarget?><? if (isset($vars["#attributes"])) { echo FlexiStringUtil::attributesToString($vars["#attributes"]); } ?>><?=$vars["#title"]?></a>
<?=isset($vars["#suffix"]) ? $vars["#suffix"] : "" ?>
