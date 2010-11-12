<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//#login.form is the rendered html for login
$aLoginForm = !isset($vars["#login.form"]) ?
    FlexiConfig::getLoginHandler()->getLoginForm()
    : $vars["#login.form"];

?>
<?=$this->renderMarkup($aLoginForm, "login_form");?>