<?php

extract($vars);
?>
  <input type="hidden" name="txtFormType" value="<?=$formtype?>" />
  <div id="<?=$sViewDBFormPrefix?>divFrmObject">
    <div class="ctrlHolderTitle" >
      New Record
    </div>
<? foreach($aFieldsInput as $sName => $aForm) {
    if (!empty($aForm["label"])) { ?>
      <div class="ctrlHolder">
        <?=$aForm["label"]?>
        <?=$aForm["input"]?>
      </div>
    <?
    } else {
    ?>
      <?=$aForm["input"]?>
    <? } ?>
  <? } ?>
  </div>
  <div class="ctrlHolder">
    <label for="" > </label>
    <div class="subnav_btn_idle" style="float:left;width:70px;text-align:center;">
      <input type="submit" class="btnsubmit" style="width:70px" value="Save">
    </div>
    <div class="subnav_btn_idle" style="margin-left: 5px; float:left;width:70px;text-align:center;">
      <input type="button" class="btnsubmit" style="width:70px" onClick="<?=$sViewDBFormPrefix?>resetForm()" value="Cancel" />
    </div>
  </div>