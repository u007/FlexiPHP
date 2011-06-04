<?php

$mValue = isset($vars["#value"]) ?
	$vars["#value"] : ( isset($vars["#default_value"]) ? $vars["#default_value"] : null);

$bDisabled = isset($vars["#disabled"]) ? $vars["#disabled"] : false;

$bMultiple = isset($vars["#multiple"]) ? $vars["#multiple"] : false;

$sRequired = isset($vars["#required"]) ?
	($vars["#required"] ? "<span class=\"required\">*</span>": "") : "";

$sExtendedURL = isset($vars["#extendedurl"]) ? $vars["#extendedurl"]: "";

$bHasOptions = isset($vars["#options"]) ? count($vars["#options"]) > 0: false;

?><select name="<?=$vars["#name"]?>" 
		<?=empty($vars["#id"]) ? "" : " id=\"" . $vars["#id"] . "\""?><?=$bDisabled ? " disabled=\"disabled\"" : ""?>
		<?=isset($vars["#size"]) ? " size=\"" . $vars["#size"] . "\"": ""?><?
		?><?=$bMultiple ? " multiple" : "" ?>
		<? if (isset($vars["#attributes"])) { echo FlexiStringUtil::attributesToString($vars["#attributes"]); } ?>>
	<? 
	
	if ($bHasOptions) {
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
	} else {
    
    if (!$bMultiple) {
      if (!empty($mValue)) {
      ?>
      <option value="<?=$mValue?>"><?=$mValue?></option>
      <?
      }
    } else {
      //is multiple
      foreach($mValue as $sOption) {
        ?>
        <option value="<?=$mValue?>"><?=$mValue?></option>
        <?
      }
    }
  }//set option accordingly
  
  
  ?>
	</select>
<? if (!empty($sExtendedURL)) { 
  $iInnerWidth = empty($vars["#popupwidth"]) ? 600: $vars["#popupwidth"];
  $iInnerHeight = empty($vars["#popupheight"]) ? 450: $vars["#popupheight"];
  
  ?>
<a href="javascript:" onClick="jQuery.colorbox({href: '<?=$sExtendedURL?>', 
    innerWidth: <?=$iInnerWidth?>, innerHeight: <?=$iInnerHeight?>,})" >More...</a>
<? } ?>


