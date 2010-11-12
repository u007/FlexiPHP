<?php
$sContent = isset($vars["#value"]) ? $vars["#value"] : "";
?>
<?=isset($vars["#prefix"]) ? $vars["#prefix"] : ""; ?>
<?=$sContent?>
<?=$vars["#childs"]?>
<?=isset($vars["#suffix"]) ? $vars["#suffix"] : "" ?>