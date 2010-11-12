<?php
$oFlexi = FlexiController::getInstance();
//based on ?mod=xxxx
$oControl = FlexiController::getCurrentController();
//echo "class: " . get_class($oControl);
//var_dump($oControl->getView()->getVars());
echo $oControl->getView()->getVar("body");

//echo $oControl->renderLayout();

