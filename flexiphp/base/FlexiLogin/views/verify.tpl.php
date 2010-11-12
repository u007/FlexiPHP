<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<h3>Account Verification</h3>
<?
if ($vars["verified"]) {
?>
<?=flexiT("Thank you","first")?>.<br/>
<?=flexiT("You account has been verified", "first")?>.<br/>
<a href=""><?=flexiT("Please click here to login", "first")?></a>...
<? } else { ?>
<?=flexiT("Sorry, invalid verification request", "first")?>!<br/>
<?
}
?>