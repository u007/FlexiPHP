<?php

$sTitle = empty($vars["#title"]) ? "" : $vars["#title"];
?>
<?=isset($vars["#prefix"]) ? $vars["#prefix"] : ""; ?>
<div id="<?=$vars["#id"]?>" <? if (isset($vars["#attributes"])) { echo FlexiStringUtil::attributesToString($vars["#attributes"]); } ?>>
  <? if(!empty($sTitle)): ?>
  <div class="title"><h3><?=$sTitle?></h3></div>
  <? endif; ?>
    <?=isset($vars["#value"]) ? $vars["#value"] : "" ?>
		<?=$vars["#childs"]?>
</div>
<?=isset($vars["#suffix"]) ? $vars["#suffix"] : "" ?>