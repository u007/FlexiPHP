<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<div><?=flexiT("sorry, you do not have the needed credential to access this section", "first")?>.</div>
<div><?=flexiT("have you logged in", "first")?>?</div>
<div><a href="<?=FlexiConfig::getLoginHandler()->getLoginURL();?>"><?=flexiT("please click here to login", "first")?></a></div>