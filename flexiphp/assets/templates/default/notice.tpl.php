<?
$aMessage = $vars["#message"];

if (count($aMessage) > 0) {
?>
<div id="divNotice" class="flexiNotice">
<? foreach($aMessage as $aMsg) {?>
	<div class="notice<?=ucfirst(strtolower($aMsg["type"]))?>">
		<?=$aMsg["msg"]?>
	</div>
<? } ?>
</div>
<? } ?>
<?
	FlexiConfig::$bRenderedNotice = true;
?>
