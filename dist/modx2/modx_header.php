<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("flexiphp/modx2_config.php");

$aFlexiSetting["template"] = "default";
$aFlexiSetting["admin.id"] = 3;
$aFlexiSetting["admin.userid"] = "";
$aFlexiSetting["admin.password"] = "";
$aFlexiSetting["loglevel"] = 1;
$aFlexiSetting["url.login"] = "?mod=default&method=secureform";
$aFlexiSetting["user.emailverify"] = false; //disable email verification
$aFlexiSetting["modulepath"] = dirname(__FILE__) . "/modules";
$aFlexiSetting["templatepath"] = dirname(__FILE__) . "/assets/flexitemplate";

if (isset($bIsAdminModule) && $bIsAdminModule) {
  $aFlexiSetting["bIsAdminPath"] = true;
  if($aFlexiSetting["framework"] == "modx") {
    $aFlexiSetting["admin.template"] = "modx.simple";
    
  } else if($aFlexiSetting["framework"] == "modx2") {
    $aFlexiSetting["admin.template"] = "modx2.admin";
  }
}

require_once("flexiphp/flexi.include.php");
if (file_exists("flexi.event.php")) require_once("flexi.event.php");
FlexiModelUtil::getInstance()->setRedBeanEvent(new FlexiRedBeanEvent());
