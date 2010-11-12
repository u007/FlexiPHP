<?php
$oFlexi = FlexiController::getInstance();
//based on ?mod=xxxx
$oControl = FlexiController::getCurrentController();
if (FlexiConfig::$sFramework == "modx2") {
  FlexiController::appendOutput($oControl->getView()->getVar($viewname));
} else {
  echo $oControl->getView()->getVar($viewname);
}
