<?php


?>
<?=$this->render("home.header");?>
<?=$this->render("jquery.tinymce")?>
<div id="divNotice" class="flexiNotice"></div>
<div class="clear" style="height: 5px;"></div>

<div id="tabs" class="yui-navset">
  <ul class="yui-nav">
    <li class="selected"><a href="#tab1"><em>List</em></a></li>
    <li><a href="#tab2"><em>Form</em></a></li>
  </ul>
  <div class="yui-content">
    <div id="<?=$sViewDBFormPrefix?>tab-list">
      <?=$this->render("tab-list");?>
    </div>
    <div id="<?=$sViewDBFormPrefix?>tab-form">
      <?=$this->render("tab-form");?>
    </div>
  </div>
</div>

<script type="text/javascript">
//var mytabs = new YAHOO.widget.TabView("tabs");
var mytabs;

YUI().use('tabview', function(Y) {
    mytabs = new Y.TabView({
        srcNode: '#tabs'
    });
    mytabs.render();
});

</script>

<?=$this->render("home.footer");?>