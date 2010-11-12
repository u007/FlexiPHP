<?php
$oFlexi = FlexiController::getInstance();
//based on ?mod=xxxx
$oControl = FlexiController::getCurrentController();
if (FlexiConfig::$sFramework == "modx2") {
  FlexiController::appendOutput($oControl->renderView($viewname));
  return FlexiController::$sOutput;
} else {
  echo $oControl->renderView($viewname);
}