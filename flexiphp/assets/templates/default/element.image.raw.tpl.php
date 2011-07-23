<?php

$mValue = isset($vars["#value"]) ?
	$vars["#value"] : ( isset($vars["#default_value"]) ? $vars["#default_value"] : null);
$sMaxLen = isset($vars["#maxlength"]) ? " maxlength=\"" . $vars["#maxlength"] . "\"" : "";

$bDisabled = isset($vars["#disabled"]) ? $vars["#disabled"] : false;
$sRequired = isset($vars["#required"]) ?
	($vars["#required"] ? "<span class=\"required\">*</span>": "") : "";

if (empty($vars["#maximagewidth"])) {
  throw new Exception("w not set");
}

$sError = "";

try {
  $sURL = !empty($mValue) ? FlexiFileUtil::getMediaURL($mValue): "";
  $sThumbURL = !empty($mValue) ? FlexiFileUtil::getMediaURL($mValue, null, null, array("maxwidth" => $vars["#maximagewidth"])): "";
} catch (Exception $e) {
  $sError = $e->getMessage();
}


?>
<?=isset($vars["#prefix"]) ? $vars["#prefix"] : ""; ?>
  <div class="fieldUploadDisplay">
  <?=empty($mValue)? "(No file uploaded)" : "" ?>
  <? if(!empty($mValue) && empty($sError)) { ?>
  <br/><a href="<?=$sURL?>" target='_blank'><img src="<?=$sThumbURL?>" /></a>
  <? } ?>
  <? if (!empty($sError)) { ?>
  <?=$sError?>
  <? } ?>
  </div>
	<input type="file" name="<?=$vars["#name"]?>" <?=is_null($mValue) ? "" : " value=\"" . $mValue . "\""?><?=$sMaxLen?>
		<?=empty($vars["#id"]) ? "" : " id=\"" . $vars["#id"] . "\""?><?=$bDisabled ? " disabled=\"disabled\"" : ""?>
		<?//=isset($vars["#size"]) ? " size=\"" . $vars["#size"] . "\"": ""?>
		<? if (isset($vars["#attributes"])) { echo FlexiStringUtil::attributesToString($vars["#attributes"]); } ?>>
  
	<? if(isset($vars["#description"])) { ?>
	<br/>
	<div class="flexiphp_div_description"><?=$vars["#description"]?></div>
	<? } ?>
<?=isset($vars["#suffix"]) ? $vars["#suffix"] : "" ?>