<?php

$mValue = isset($vars["#value"]) ?
	$vars["#value"] : ( isset($vars["#default_value"]) ? $vars["#default_value"] : null);

$bDisabled = isset($vars["#disabled"]) ? $vars["#disabled"] : false;


$sLabel = isset($vars["#label"]) ? $vars["#label"] : "";
$sRequired = isset($vars["#required"]) ?
	($vars["#required"] ? "<span class=\"required\">*</span>": "") : "";
?><? 
	$iCnt= 0;
	foreach($vars["#options"] as $sKey => $sValue) { 
		
		$sChecked = in_array($sKey, $mValue) ? " checked=\"checked\"" : false;
		?>
		
		<label <?=isset($vars["#id"]) ? "for=\"" . $vars["#id"] . "-" . $iCnt . "\"": "" ?>>
      <input type="checkbox" name="<?=$vars["#name"]?>[]" <?=is_null($mValue) ? "" : " value=\"" . $sKey . "\""?>
			<?=empty($vars["#id"]) ? "" : " id=\"" . $vars["#id"] . "-" . $iCnt . "\""?><?=$bDisabled ? " disabled=\"disabled\"" : ""?>
			<?=isset($vars["#size"]) ? " size=\"" . $vars["#size"] . "\"": ""?><?=$sChecked?>
			<? if (isset($vars["#attributes"])) { echo FlexiStringUtil::attributesToString($vars["#attributes"]); } ?>>
      <?=$sValue?>
    </label>
	<? 
		$iCnt++;
	} ?>