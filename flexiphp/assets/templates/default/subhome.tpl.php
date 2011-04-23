<?php
extract($vars);

$bCanDelete = empty($bCanDelete)? false: true;
$bCanEdit   = empty($bCanEdit)? false: true;
$bCanAdd   = empty($bCanAdd)? false: true;
?>
<?=$this->render("home.header");?>
<div class="clear" style="height: 5px;"></div>
<div id="<?=$sViewDBFormPrefix?>tabs" class="yui-navset">
  <ul class="yui-nav">
    <li class="selected"><a href="#tab1"><em>List</em></a></li>
    <? if ($bCanEdit || $bCanAdd) { ?>
    <li><a href="#tab2"><em>Form</em></a></li>
    <? } ?>
  </ul>
  <div class="yui-content">
    <div id="<?=$sViewDBFormPrefix?>tab-list">
      <?=$this->render("tab-list");?>
    </div>
    <!--still render this below-->
    <div id="<?=$sViewDBFormPrefix?>tab-form">
      <?=$this->render("tab-form");?>
    </div>
  </div>
</div>

<script type="text/javascript">
//var mytabs = new YAHOO.widget.TabView("tabs");
var <?=$sViewDBFormPrefix?>tabs;

YUI().use('tabview', function(Y) {
    <?=$sViewDBFormPrefix?>tabs = new Y.TabView({
        srcNode: '#<?=$sViewDBFormPrefix?>tabs'
    });
    <?=$sViewDBFormPrefix?>tabs.render();
});

</script>

<?=$this->render("home.footer");?>