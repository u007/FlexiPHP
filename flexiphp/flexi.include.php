<?php
global $aFlexiSetting;

require_once($aFlexiSetting["basedir"] . "/lib/Doctrine/Doctrine.php");
spl_autoload_register(array('Doctrine', 'autoload'));
require_once($aFlexiSetting["basedir"] ."/base/Flexi/objectclass/FlexiConfig.php");
FlexiConfig::configure($aFlexiSetting);

//include this file to integrate flexi controller
require_once(FlexiConfig::$sBaseDir ."/functions.php");
spl_autoload_register(array("FlexiConfig","autoLoad"));

require_once(FlexiConfig::$sBaseDir ."/base/Flexi/controller.php");
FlexiController::$iLogLevel = FlexiConfig::$iLogLevel;
//setting up DB Module
$oFlexiModelUtil = FlexiModelUtil::getInstance();
$oFlexiModelUtil->setDBSetting(FlexiConfig::$sDBType, FlexiConfig::$sDBHost,
	FlexiConfig::$iDBPort, FlexiConfig::$sDBUser, FlexiConfig::$sDBPass, FlexiConfig::$sDBName);
FlexiConfig::finalize();

//initialising main controller
$oFlexi = FlexiController::getInstance();

