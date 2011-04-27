<?php
extract($vars);
//var_dump($vars["bCanEdit"]);
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
  </ul>
  <div id="<?=$sViewDBFormPrefix?>tab-list">
    <?=$this->render("tab-list");?>
  </div>
  <div id="<?=$sViewDBFormPrefix?>tab-form">
    <?=$this->render("tab-form");?>
  </div>
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
</script>

<?=$this->render("home.footer");?>