<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

abstract class FlexiPlatformHandler {

  abstract public function getAllAccessGroup();
  abstract public function getAllRoles();
  abstract public function createUser($sUserName, $sPassword, $sFullName="", $aGroups=array(), $aOptions=array());
  abstract public function updateUser($iId, $sUserName, $sFullName = null, $sPassword = null);

  abstract public function checkIfExistsUserEmail($sEmail, $iId=null);
  abstract public function checkIfExistsUserName($sUserName, $iId=null);

  abstract public function getUserById($iId);
  abstract public function removeUser($iId);
  abstract public function addMessage($asMsg, $asType="info");
  abstract public function forceDie();

  abstract public function getPageEncoding();

  abstract public function getUploadSize();

  abstract public function verifyUser($iId, $sCode);
  protected static $oInstance = null;
  
  public static function getPlatformHandler() {
    switch(FlexiConfig::$sFramework) {
      case "modx":
        return FlexiModxPlatformHandler::getInstance();
        break;
      case "modx2":
        return FlexiModx2PlatformHandler::getInstance();
        break;
      case "iscript":
        return FlexiIScriptPlatformHandler::getInstance();
        break;
      default:
        return FlexiStandAlonePlatformHandler::getInstance();
        //TODO
    }

    return null;
  }

  public static function getReferrerURL() {
    return $_SERVER['HTTP_REFERER'];
  }

  public static function getTempProcessFilePath($sProcessName) {
    if (strlen($sProcessName) > 200) {
      throw new FlexiException("Process name may not exceed lenght of 200", ERROR_CONFIGURATION);
    }
    return FlexiConfig::$sBaseDir . "/assets/temp/process/process_" . $sProcessName;
  }

  public static function checkAndGetLock($sProcessName) {
    $sPath = self::getTempProcessFilePath($sProcessName);
    return FlexiFileUtil::doLockFile($sPath);
  }

  public static function freeLock($sProcessName) {
    $sPath = self::getTempProcessFilePath($sProcessName);
    return FlexiFileUtil::doUnlockFile($sPath);
  }

  /**
   * Do unlock of all file on destruct
   */
  public function  __destruct() {
    FlexiFileUtil::doUnlockAll();
  }
}
