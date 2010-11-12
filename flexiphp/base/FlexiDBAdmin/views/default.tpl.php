<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<h1>Database Management</h1>
<?=$this->render("headerbar");?>
<div class="sectionHeader">Select an action</div>

<div class="sectionBody">
  <div class="tab-pane" id="docManagerPane">

    <!--BEGIN TAB1-->
    <div class="tab-page" >
      <?=$this->render("tab1")?>
    </div>
    <!--END TAB1-->

    <!--BEGIN TAB2-->
    <div class="tab-page" >
      <?=$this->render("tab2")?>
    </div>
    <!--END TAB2-->

    <!--BEGIN TAB3-->
    <div class="tab-page">
      <?=$this->render("tab3")?>
    </div>
    <!--END TAB3-->

  </div> <!--tabs-->
</div>
<script type="text/javascript">
  var tpResources = new WebFXTabPane(document.getElementById('docManagerPane'));
  
</script>