<?php

global $modx;

$modx->regClientCSS($vars["#moduleurl"] . "/assets/css/style.css");
//$modx->regClientStartupScript($vars["#moduleurl"].'/assets/js/adminmember.js');
?>
<?=$this->render("tinymce"); ?>
<div id="modx-panel-holder"></div>
<div id="pv-panel-header-div"><div id="ext-comp-1033" class=" modx-page-header"><h2>Repository: List Management</h2></div></div>
<div id="pv-panel-home-div"></div>
<div id="errormsg"></div>

<div class="sectionBody">
  <div class="tab-pane x-tab-panel modx-tabs" id="docManagerPane">
    <!--BEGIN TAB1-->
    <div class="tab-page" id="tabTemplates">
      <?=$this->render("tab1")?>
    </div>
    <!--END TAB1-->
    <!--BEGIN TAB2-->
    <div class="tab-page" id="tabTemplates2">
      <?=$this->render("tab2")?>
    </div>
    <!--END TAB2-->
  </div>
</div>
<script type="text/javascript">
var tabs;

function expandGrid() {
  tabs.expand(true);
  tabs.doLayout();
}
Ext.onReady(function() {
  tabs = new Ext.TabPanel({
      renderTo: 'docManagerPane',
      activeTab: 0,
      border: false,
      bodyborder: true,
      autoScroll: true,
      autoHeight : true,
      items:[
          {contentEl:'tabTemplates', title:'List'},
          {contentEl:'tabTemplates2', title:'New Setting'}
      ]
  });
  //force fix on bugging ext tab
  tabs.setActiveTab(1);
  tabs.items.itemAt(1).setTitle("<span>Edit Setting</span>");
  <? if (empty($vars["editform"])) { ?>
  tabs.setActiveTab(0);
  tabs.items.itemAt(1).setTitle("<span>New Setting</span>");
  <? } ?>
  setTimeout("expandGrid()", 1000);
});

</script>
<?

?>
<div style="heght: 300px"></div>