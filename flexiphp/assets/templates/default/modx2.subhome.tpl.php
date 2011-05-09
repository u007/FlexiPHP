<?php
extract($vars);
//var_dump($vars["bCanEdit"]);
$aTabs = $vars["#tabs"];
$bCanDelete = empty($bCanDelete)? false: true;
$bCanEdit   = empty($bCanEdit)? false: true;
$bCanAdd   = empty($bCanAdd)? false: true;

?>
<script type="text/javascript">
jQuery(document).ready(function() {
  jQuery(document.body).addClass("yui3-skin-sam");
});
</script>
<? if(!empty($vars["#title"])) { ?>
<div class="ctrlHolderTitle" >
  <?=$vars["#title"]?>
</div>
<? } ?>
<?=$this->render("home.header");?>
<div class="clear" style="height: 5px;"></div>
<div id="<?=$sViewDBFormPrefix?>tabs">
  <ul class="tabs">
    <li><a href="#<?=$sViewDBFormPrefix?>tab-list"><span>List</span></a></li>
    <? if ($bCanEdit || $bCanAdd) { ?>
    <li><a href="#<?=$sViewDBFormPrefix?>tab-form"><span>Form</span></a></li>
    <? } ?>
    <? foreach($aTabs as $oTab) { ?>
    <li><a href="#<?=$sViewDBFormPrefix?><?=$oTab["name"]?>"><em><?=$oTab["label"]?></em></a></li>
    <? } ?>
  </ul>
  <div id="<?=$sViewDBFormPrefix?>tab-list">
    <?=$this->render("tab-list");?>
  </div>
  <div id="<?=$sViewDBFormPrefix?>tab-form">
    <?=$this->render("tab-form");?>
  </div>
  <? 
  //var_dump($aTabs);
  foreach($aTabs as $oTab) { ?>
    <div id="<?=$sViewDBFormPrefix?><?=$oTab["name"]?>"><?=empty($oTab["view"])? "": $this->render($oTab["view"]) ?></div>
  <? } ?>
</div>

<script type="text/javascript">
var _<?=$sViewDBFormPrefix?>aLoadedTab = [true,false,false];
var <?=$sViewDBFormPrefix?>tabs;
jQuery(document).ready(function() {
  <?=$sViewDBFormPrefix?>tabs = jQuery("#<?=$sViewDBFormPrefix?>tabs").tabs({
    "select": function(event, ui) {
      _<?=$sViewDBFormPrefix?>aLoadedTab[ui.index] = true;
    }
  });

});

<? if (!$bCanEdit && !$bCanAdd) { ?>
jQuery(document).ready(function() {
  jQuery("#<?=$sViewDBFormPrefix?>tab-form").hide();
});
<? } ?>
</script>

<?=$this->render("home.footer");?>