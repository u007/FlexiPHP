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

$aPath = array();
if (!empty($mValue)) {
	$aPath = explode($vars["#uploadseparator"], $mValue);
}

$sBasePath = (empty($oField->savepath)? "": $oField->savepath . "/");
$aPhoto = array();

$c = 1;
foreach($aPath as $sPath) {
	$sPath = trim($sPath);
	if (! empty($sPath)) {
		try {
			$sPhotoPath = (empty($vars["#savepath"])? "": $vars["#savepath"] . "/") . $sPath;
			//echo "path: " . $sPhotoPath . "<br/>\n";
			$sURL = FlexiFileUtil::getMediaURL($sPhotoPath);
			//echo "thumb: " . $sURL . "<br/>\n";
			$sThumbURL = FlexiFileUtil::getMediaURL($sPhotoPath, null, null, array("maxwidth" => $vars["#maximagewidth"]));
			$aPhoto[$c-1] = array(
				"photopath" => $sPhotoPath,
				"thumburl" => $sThumbURL,
				"url" => $sURL,
				"error" => 0
			);
		} catch (Exception $e) {
			$aPhoto[$c-1] = array("error"=> 1, "message" => $e->getMessage());
		}
	} else {
		$aPhoto[$c-1] = array("error"=> 0);
	}
	$c++;
}//for

?>
<?=isset($vars["#prefix"]) ? $vars["#prefix"] : ""; ?>
  <div class="fieldUploadDisplay">
  <?=empty($mValue)? "(No file uploaded)" : "" ?>
  <? if(!empty($mValue) && empty($sError)) { ?>
	<? for ($c=0; $c < $vars["#uploadcount"]; $c++) {
		if (isset($aPhoto[$c]) && $aPhoto[$c]["error"] == 0 && isset($aPhoto[$c]["photopath"])) {
		?>
		<br/><?=$c+1?>) <a href="<?=$aPhoto[$c]["url"]?>" target='_blank'><img src="<?=$aPhoto[$c]["thumburl"]?>" /></a>
		<? } else if (isset($aPhoto[$c]) && $aPhoto[$c]["error"]==0) { ?>
		<br/><?=$c+1?>) No Photo
		<? } else if (isset($aPhoto[$c]) && $aPhoto[$c]["error"]==1) { ?>
		<br/><?=$c+1?>) <?=$aPhoto[$c]["message"]?>
		<? } else {?>
		<br/><?=$c+1?>) No Photo
		<? } ?>
		<br/>
		<input type="file" name="<?=$vars["#name"]?>_<?=$c+1?>" 
		<?=empty($vars["#id"]) ? "" : " id=\"" . $vars["#id"] . "_" . ($c+1) . "\""?><?=$bDisabled ? " disabled=\"disabled\"" : ""?>
		<?//=isset($vars["#size"]) ? " size=\"" . $vars["#size"] . "\"": ""?>
		<? if (isset($vars["#attributes"])) { echo FlexiStringUtil::attributesToString($vars["#attributes"]); } ?>>
  
		<? if(isset($vars["#description"])) { ?>
		<br/>
		<div class="flexiphp_div_description"><?=$vars["#description"]?></div>
		<? } ?>
	
	<? }//for each photo ?>
  <? }//if no error and no empty ?>
  <? if (!empty($sError)) { ?>
  <?=$sError?>
  <? } ?>
  </div>
	
<?=isset($vars["#suffix"]) ? $vars["#suffix"] : "" ?>