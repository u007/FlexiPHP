<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$status = (int)$vars["status"];

if ($status == 1) {
?>
  <?=flexiT("No such user", "first")?>
<? } elseif($status==2) { ?>
  <?=flexiT("Verification e-mail resent","first")?>
<? } elseif($status==3) { ?>
  <?=flexiT("E-mail is missing","first")?>
<?
}
?>

<div>
  <a href=""><?=flexiT("Click here to return","first") ?></a>
</div>