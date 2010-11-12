<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<h1>Repository: List Management</h1>
<?=$this->render("headerbar");?>
<div class="sectionHeader">Select an action</div>

<div class="sectionBody">
  <div class="tab-pane" id="docManagerPane">
    <script type="text/javascript">
        tpResources = new WebFXTabPane(document.getElementById('docManagerPane'));
    </script>

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

  </div> <!--tabs-->
</div>
