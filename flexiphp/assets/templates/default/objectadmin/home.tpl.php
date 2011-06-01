<?php

//phpinfo();
?>
<script type="text/javascript">
var _aLoadedTab = [true,false,false];

jQuery(document).ready(function() {
  jQuery("#tabs").tabs({
    "select": function(event, ui) {
      //
      _aLoadedTab[ui.index] = true;
    }
  });

  //jQuery('#frmOrderBuy').ajaxForm({
  //    dataType:  'json',
  //    success:   processOrder});
});
</script>
<h1>Objects Manager</h1>
<div id="divNotice" class="flexiNotice"></div>
<div class="clear" style="height: 5px;"></div>
<div id="tabs" class="tabs">
  <ul>
    <li><a href="#tab-list"><span>List</span></a></li>
    <li><a href="#tab-form"><span>Form</span></a></li>
    <li><a href="#tab-import"><span>Import</span></a></li>
    <li><a href="#tab-sqllog"><span>SQL Log</span></a></li>
    <li><a href="#tab-infolog"><span>Info Log</span></a></li>
  </ul>
  <div id="tab-list">
    <?=$this->render("tab-list");?>
  </div>
  <div id="tab-form">
    <?=$this->render("tab-form");?>
  </div>
  <div id="tab-import">
    <?=$this->render("tab-import");?>
  </div>
  <div id="tab-sqllog">
    <?=$this->render("tab-sqllog");?>
  </div>
  <div id="tab-infolog">
    <?=$this->render("tab-infolog");?>
  </div>
</div>
