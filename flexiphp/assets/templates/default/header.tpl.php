<!-- place holder for header of CSS and JS -->
<? foreach($vars["#css"] as $aPath) { 
	$sMedia = isset($aPath["media"]) ? " media=\"" . $aPath["media"] . "\"" : "";
	?>
<link rel="stylesheet" href="<?=$aPath["path"]?>" type="<?=$aPath["type"]?>"<?=$sMedia?>/>
<? } ?>
<? foreach($vars["#js"] as $aPath) { 
	?>
<script type="text/javascript" src="<?=$aPath["path"]?>"></script>
<? } ?>
