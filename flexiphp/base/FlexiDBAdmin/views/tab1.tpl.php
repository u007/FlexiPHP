<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<h2 class="tab">List</h2>
<form id="frmList" name="frmList" method="GET">
  <?=$this->renderFormHiddenRaw("mod", $vars["#module"])?>
  <?=$this->renderFormHiddenRaw("method", "op")?>
  <?=$this->renderFormHiddenRaw("op", "")?>
  <? if (FlexiConfig::$sFramework == "modx" || FlexiConfig::$sFramework == "modx2") { ?>
  <?=$this->renderFormHiddenRaw("a", FlexiConfig::$aQueryString["a"])?>
  <?=$this->renderFormHiddenRaw("id", FlexiConfig::$aQueryString["id"])?>
  <?
  }
  ?>
  <div style="display: block;">
    <div style="float:left; height: 25px; margin-bottom: 3px; overflow: hidden;">
      <img src="flexiphp/assets/templates/default/img/arrow-down-small.jpg"> Action:
      <?=$this->renderLink("javascript:doFormOperation('delete', '#frmList')", "Delete")?>
    </div>
    <div class="clear"></div>
  </div>
  <?=$vars["list"]?>

  <div>
    <?=$this->renderPartial("pagination",
      array("page" => $vars["page"], "max" => $vars["totalrecords"], "rowsperpage" => $vars["rowsperpage"],
        "params" => $vars["params"], "url" => $vars["url"] ),
      $vars["#modulepath"]); ?>
  </div>
</form>