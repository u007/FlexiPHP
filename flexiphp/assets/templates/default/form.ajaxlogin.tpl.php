<?php

//#login.form is the rendered html for login
if (!isset($vars["#login.form"])) {
  //echo "not set!";
}

$aLoginForm = !isset($vars["#login.form"]) ?
    FlexiConfig::getLoginHandler()->getLoginForm()
    : $vars["#login.form"];

?>
<?=$this->renderMarkup($aLoginForm, "login_form");?>