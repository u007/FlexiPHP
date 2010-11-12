<?
/* 
 * standalone used by admin / manager
 */

require_once("modx_header.php");
$sModuleName = FlexiController::getInstance()->getRequest("mod");

$oFlexi->run($sModuleName, $oFlexi->getRequest(FlexiConfig::$aModuleURL["method"]), true);
echo FlexiController::$sOutput;
