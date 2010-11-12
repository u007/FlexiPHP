<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<h3>Request for Lost Password</h3>
<script type="text/javascript">
  function doGetPassword() {
    var sEmail = jQuery("#frmLostPassword input[name=txtEmail]").val();
    document.location.href="<?=$this->url(null, "forgotpassword2")?>&email=" + sEmail;
  }
</script>
<div id="onManagerLoginFormRender">
  <? if (FlexiConfig::$sFramework == "modx" || FlexiConfig::$sFramework == "modx2") {
    global $modx;

    //$sURL = $modx->makeURL("");
    //$sURL = flexiURL("");
    $sURL = "index.php";
    ?>
  <div style="margin-top: 10px;">Please fill in your registered e-mail, and we will send your lost password to your e-mail:</div>
  <form name="loginreminder" id="frmLostPassword" action="<?=$sURL?>" target="_parent" method="post">
    <input type="hidden" name="txtpwdrem" value="1" />
    <label for="FMP_email" id="FMP-email_label">E-Mail:</label>
    <input type="text" name="txtwebemail">
    <input name="cmdweblogin" type="submit" id="FMP-email_button" value="Retrieve"/>
  </form>
  <? } ?>
</div>