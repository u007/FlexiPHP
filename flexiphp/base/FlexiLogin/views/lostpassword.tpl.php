<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<script type="text/javascript">
  function doGetPassword() {
    var sEmail = jQuery("#frmLostPassword input[name=txtEmail]").val();
    document.location.href="<?=$this->url(null, "lostpassword2")?>&email=" + sEmail;
  }
</script>
<div id="onManagerLoginFormRender">
  <form id="frmLostPassword">
    <label for="FMP_email" id="FMP-email_label">Account email:</label>
    <input type="text" name="txtEmail">
    <button onclick="doGetPassword()" type="button" id="FMP-email_button">Send</button>
  </form>
</div>