<?php

$mValue = isset($vars["#value"]) ?
	$vars["#value"] : ( isset($vars["#default_value"]) ? $vars["#default_value"] : null);

$bDisabled = isset($vars["#disabled"]) ? $vars["#disabled"] : false;

$bMultiple = isset($vars["#multiple"]) ? $vars["#multiple"] : false;

$sRequired = isset($vars["#required"]) ?
	($vars["#required"] ? "<span class=\"required\">*</span>": "") : "";

?><select name="<?=$vars["#name"]?>" 
		<?=empty($vars["#id"]) ? "" : " id=\"" . $vars["#id"] . "\""?><?=$bDisabled ? " disabled=\"disabled\"" : ""?>
		<?=isset($vars["#size"]) ? " size=\"" . $vars["#size"] . "\"": ""?><?
		?><?=$bMultiple ? " multiple" : "" ?>
		<? if (isset($vars["#attributes"])) { echo FlexiStringUtil::attributesToString($vars["#attributes"]); } ?>>
	<? 
	
	if (isset($vars["#options"])) {
		foreach($vars["#options"] as $sKey => $sOption) { 
			$sKeyValue = FlexiParser::parseHTMLInputValue($sKey);
			
			$bSelected = false;
			if($bMultiple && is_array($mValue)) {
				if (in_array($sKeyValue, $mValue))
				{ $bSelected = true; } 
			} else { 
				if ($sKeyValue == $mValue) { $bSelected = true; }
			}
			?>
		<option value="<?=$sKeyValue?>" <?=$bSelected? " selected" : "" ?>><?=FlexiParser::parseNoHTML($sOption)?></option>
	<? 
		} 
	} ?>
	</select>
