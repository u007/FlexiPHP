<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

?>
Logged-in. Redirecting... <?//=$vars["url"]?>
<script type="text/javascript">
  function redirectme() {
    document.location.href="<?=$vars["url"]?>";
  }

  setTimeout("redirectme()", 500);
</script>