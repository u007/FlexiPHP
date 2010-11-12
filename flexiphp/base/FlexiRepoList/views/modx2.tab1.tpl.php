<?php
$aList = $vars["aList"];
$aKeys = $vars["aKeys"];
$aOptions = array("" => "- select -");
foreach($aKeys as $oRow)
{
  $aOptions[$oRow->listkey] = $oRow->listkey;
}
?>
<script type="text/javascript">
  function doFilterForm(oSelect)
  {
    $("#frmList input[name=op]").val("list");
    $("#frmList").submit();
  }
</script>
<form id="frmList" name="frmList" method="GET">
  <?=$this->renderFormHiddenRaw("mod", $vars["#module"])?>
  <?=$this->renderFormHiddenRaw("method", "op")?>
  <?=$this->renderFormHiddenRaw("op", "")?>
  <?=$this->renderFormHiddenRaw("a", FlexiConfig::$aQueryString["a"])?>
  <div style="display: block;">
    <div style="float:left; height: 25px; margin-bottom: 3px; overflow: hidden;">
      <img src="<?=FlexiConfig::$sBaseURLDir?>flexiphp/assets/templates/default/img/arrow-down-small.jpg"> Action:
      <?=$this->renderLink("javascript:doFormOperation('delete', '#frmList')", "Delete")?>
    </div>

    <div style="float: right; height: 26px; padding-top: 4px; vertical-align: bottom;">
      <span>Filter: Key: </span>
      <?=$this->renderFormSelectRaw("filter_listkey", $vars["filter_listkey"], $aOptions, null, array("onChange" => "doFilterForm(this)"));?>
    </div>
    <div class="clear"></div>
  </div>
  <?=$this->render("list")?>

  <div>
    <?=$this->renderPartial("pagination",
      array("page" => $vars["page"], "max" => $vars["totalrecords"], "rowsperpage" => $vars["rowsperpage"],
        "params" => $vars["params"], "url" => $vars["url"] ),
      $vars["#modulepath"]); ?>
  </div>
</form>