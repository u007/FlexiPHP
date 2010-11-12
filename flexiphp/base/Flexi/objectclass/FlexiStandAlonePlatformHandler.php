<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class FlexiStandAlonePlatformHandler extends FlexiPlatformHandler {


  private function __construct() {

  }
  /**
   * dummy function to ensure function is not called directly
   */
  protected function checkMe() {

  }

  public function checkIfExistsUserEmail($sEmail, $iId=null) {
    //TODO
  }

  public function checkIfExistsUserName($sUserName, $iId=null) {
    //todo
  }

  public function getUploadSize() {
    //TODO, put in config setting
    return 10*1024*1024; //10MB
  }
  /**
	 * Add msg to display
	 * @param string message
	 * @param string type: "info", "warn", "error"
	 */
	public function addMessage($asMsg, $asType="info")
	{
		FlexiConfig::$aMessage[] = array("msg" => $asMsg, "type" => $asType);
	}

  /**
   * Api to create user
   * @param String $sUserName
   * @param String $sPassword
   * @param String $sFullName
   * @param array of Strings $aGroups
   * @param array $aOptions
   * @return Doctrine_Record
   */
  public function createUser($sUserName, $sPassword, $sFullName="", $aGroups=array(), $aOptions=array()) {
    //TODO
//    $this->checkMe();
//
//    $oModel = FlexiModelUtil::getModelInstance("ModxWebUsers", "flexiphp/base/FlexiAdminUser");
//    $oModel->username = $sUserName;
//    $oModel->password = md5($sPassword);
//    $oModel->Attributes->fullname = $sFullName;
//    $oModel->save();
//    $iUserId = $oModel->id;
//    $oGroupNameQuery = FlexiModelUtil::getDBQuery("ModxWebgroupNames", "flexiphp/base/FlexiAdminGroup");
//
//    foreach($aGroups as $sGroup) {
//      $oGroupNameModel = $oGroupNameQuery->where("name=?", array($sGroup));
//
//      if ($oGroupNameModel === false) {
//        throw new FlexiException("No group by: " . $sGroup, ERROR_EOF);
//      }
//
//      $iGroupNameId = $oGroupNameModel->id;
//      $oGroupModel = FlexiModelUtil::getModelInstance("ModxWebgroups", "flexiphp/base/FlexiAdminGroup");
//      $oGroupModel->webuser = $iUserId;
//      $oGroupModel->webgroup = $iGroupNameId;
//      $oGroupModel->save();
//    }
//
//    return $oModel;
  }

  public function updateUser($iId, $sUserName, $sFullName = null, $sPassword = null, $aOptions=array()) {
    //TODO
//    $this->checkMe();
//
//    $oModel = $this->getUserById($iId);
//    $oModel->username = $sUserName;
//    if ($sFullName != null) {
//      $oModel->Attributes->fullname = $sFullName;
//    }
//
//    if ($sPassword != null) {
//      $oModel->password = md5($sPassword);
//    }
//
//    $oModel->replace();
//
//    return $oModel;
  }

  public function getUserById($iId) {
    //TODO
//    $oModel = FlexiModelUtil::getDBQuery("ModxWebUsers", "flexiphp/base/FlexiAdminUser")
//            ->where("id=?", array($iId))->fetchOne();
//
//    if ($oModel === false) {
//      throw new FlexiException("User not found: " . $iId, ERROR_EOF);
//    }
//
//    return $oModel;
  }

  public function removeUser($iId) {
    //TODO
//    $oModel = $this->getUserById($iId);
//    $oModel->delete();
  }

  public function forceDie() {
    die();
  }

  public static function getInstance() {
    if (is_null(self::$oInstance )) {
      self::$oInstance = new FlexiStandAlonePlatformHandler();
    }
    return self::$oInstance;
  }

}