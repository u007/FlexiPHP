<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class FlexiModxPlatformHandler extends FlexiPlatformHandler {

  
  private function __construct() {

  }
  /**
   * dummy function to ensure function is not called directly
   */
  protected function checkMe() {

  }

  public function getUploadSize() {
    global $modx;
    return $modx->config["upload_maxsize"];
  }

  public function checkIfExistsUserName($sUserName, $iId=null) {
    FlexiLogger::debug(__METHOD__, "checking user: " . $sUserName);
    if (empty($sUserName)) { return false; }

    $oQuery = FlexiModelUtil::getDBQuery("ModxWebUsers u", "flexiphp/base/FlexiAdminUser");

    if (!empty($iId)) {
      $oModel = $oQuery->where("u.username=? and u.id=?", array($sUserName, $iId))->fetchOne();
      //FlexiLogger::debug(__METHOD__, "user: " . print_r($oModel, true));
      //var_dump($oModel);
      return $oModel != null && $oModel !== false;
    } else {
      $oModel = $oQuery->where("u.username=?", array($sUserName))->fetchOne();
      //FlexiLogger::debug(__METHOD__, "user: " . print_r($oModel, true));
      //var_dump($oModel);
      return $oModel != null && $oModel !== false;
    }
  }

  public function checkIfExistsUserEmail($sEmail, $iId=null) {
    FlexiLogger::debug(__METHOD__, "checking email: " . $sEmail);
    if (empty($sEmail)) { return false; }
    
    $oQuery = FlexiModelUtil::getDBQuery("ModxWebUsers u", "flexiphp/base/FlexiAdminUser")
            ->leftJoin("u.Attributes a");
    
    if (!empty($iId)) {
      $oModel = $oQuery->where("a.email=? and u.id=?", array($sEmail, $iId))->fetchOne();
      return $oModel != null && $oModel !== false;
    } else {
      $oModel = $oQuery->where("a.email=?", array($sEmail))->fetchOne();
      return $oModel != null && $oModel !== false;
    }
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
  public function createUser($sUserName, $sPassword, $sFullName="", $aGroups=array(), $aOptions=array(), $bSendEmail = true) {
    //var_dump($aOptions);
    $this->checkMe();

    $bVerifyEmail = isset($aOptions["verifyemail"]) ? $aOptions["verifyemail"] : FlexiConfig::$bRequireEmailVerification;

    if ($bVerifyEmail && empty(FlexiConfig::$sSupportEmail)) {
      throw new FlexiException("Support e-mail not set: " . FlexiConfig::$sSupportEmail, ERROR_CONFIGURATION);
    }

    $oModel = FlexiModelUtil::getModelInstance("ModxWebUsers", "flexiphp/base/FlexiAdminUser");
    $oModel->username = $sUserName;
    $oModel->password = md5($sPassword);
    $oModel->Attributes->fullname = $sFullName;
    if (isset($aOptions["email"])) {
      $oModel->Attributes->email = $aOptions["email"];
      //$oModel->Attributes->replace();
    }

    $oModel->save();
    
    $iUserId = $oModel->id;
    $oGroupNameQuery = FlexiModelUtil::getDBQuery("ModxWebgroupNames", "flexiphp/base/FlexiAdminGroup");
    
    foreach($aGroups as $sGroup) {
      $oGroupNameModel = $oGroupNameQuery->where("name=?", array($sGroup))->fetchOne();

      if ($oGroupNameModel === false) {
        throw new FlexiException("No group by: " . $sGroup, ERROR_EOF);
      }

      $iGroupNameId = $oGroupNameModel->id;
      $oGroupModel = FlexiModelUtil::getModelInstance("ModxWebGroups", "flexiphp/base/FlexiAdminGroup");
      $oGroupModel->webuser = $iUserId;
      $oGroupModel->webgroup = $iGroupNameId;
      $oGroupModel->save();
    }
    
    //echo "verify: " . ($bVerifyEmail ? "true" : "false");
    if (!empty($oModel->Attributes->email) && $bVerifyEmail) {
      $oView = new FlexiView();
      $sMessage = $oView->render("user.verify", array("fullname" => $sFullName,
        "module" => "FlexiLogin" , "method" =>"verify", "params" => array("id" => $oModel->id, "r" => $oModel->Extend->verifycode)));

      $sSubject = "Verification required";
      $oMail = FlexiMailer::getInstance();
      $oMail->setFrom(FlexiConfig::$sSupportEmail);
      $oMail->mail($sSubject, $sMessage, $oModel->Attributes->email);

     // sendMailMessage($oModel->Attributes->email, $oModel->username, $sPassword, $oModel->Attributes->fullname);
    }

    return $oModel;
  }

  public function updateUser($iId, $sUserName, $sFullName = null, $sPassword = null, $aOptions=array(), $aGroups = array()) {
    $this->checkMe();

    $oModel = $this->getUserById($iId);
    $oModel->username = $sUserName;
    if ($sFullName != null) {
      $oModel->Attributes->fullname = $sFullName;
    }

    if ($sPassword != null) {
      $oModel->password = md5($sPassword);
    }

    if (isset($aOptions["email"])) {
      $oModel->Attributes->email = $aOptions["email"];
      //$oModel->Attributes->replace();
    }

    $oModel->replace();
    $oGroupNameQuery = FlexiModelUtil::getDBQuery("ModxWebgroupNames", "flexiphp/base/FlexiAdminGroup");
    $aGroupId = array();
    foreach($aGroups as $sGroup) {
      $oGroupNameModel = $oGroupNameQuery->where("name=?", array($sGroup))->fetchOne();

      if ($oGroupNameModel === false) {
        throw new FlexiException("No group by: " . $sGroup, ERROR_EOF);
      }

      $iGroupNameId = $oGroupNameModel->id;
      $aGroupId[] = $iGroupNameId;

      $oGroupModel = FlexiModelUtil::getDBQuery("ModxWebGroups", "flexiphp/base/FlexiAdminGroup")
              ->where("webgroup=? and webuser=?", array($iGroupNameId, $iId))->fetchOne();

      if ($oGroupModel == null || $oGroupNameModel === false) {
        $oGroupModel = new ModxWebGroups();
        $oGroupModel->webuser = $iId;
        $oGroupModel->webgroup = $iGroupNameId;
        $oGroupModel->save();
      }
    }

    if (count($aGroups) > 0) {
      //clearing all other groups
      $sGroupId = implode(",", $aGroupId);
      FlexiModelUtil::getDBQuery("ModxWebGroups", "flexiphp/base/FlexiAdminGroup")
              ->delete("ModxWebGroups")
              ->where("webgroup not in (" . $sGroupId . ")")->execute();
    }

    return $oModel;
  }

  public function verifyUser($iId, $sCode) {
    $oModel = FlexiModelUtil::getDBQuery("ModxWebUsers", "flexiphp/base/FlexiAdminUser")
            ->where("id=?", array($iId))->fetchOne();

    if ($oModel === false) {
      return false;
    }
    //echo "Comparing: " . $sCode . " vs " . $oModel->Extend->verifycode;
    try {
      //echo "same :)";
      if ($oModel->Extend->verifycode == $sCode) {
        $oModel->Extend->verified = 1;
        $oModel->Extend->replace();
        return true;
      }
    } catch (Exception $e) {
      //echo "error: " . $e->getMessage();
      FlexiLogger::error(__METHOD__, "Unable to save User verification");
      return false;
    }
  }

  public function getUserById($iId) {
    $oModel = FlexiModelUtil::getDBQuery("ModxWebUsers", "flexiphp/base/FlexiAdminUser")
            ->where("id=?", array($iId))->fetchOne();

    if ($oModel === false) {
      throw new FlexiException("User not found: " . $iId, ERROR_EOF);
    }

    return $oModel;
  }

  public function removeUser($iId) {
    $oModel = $this->getUserById($iId);
    $oModel->delete();
  }

  public function forceDie() {
    die();
  }

  public function getPageEncoding() {
    //[()]
    global $modx;
    return $modx->config['modx_charset'];
  }

  public function getAllAccessGroup() {
    $aList = FlexiModelUtil::getInstance()->getRedBeanFetchAll(
            "select name from modx_documentgroup_names order by name asc");
    return $aList;
  }
  public function getAllRoles() {
    $aList = FlexiModelUtil::getInstance()->getRedBeanFetchAll(
            "select name from modx_webgroup_names order by name asc");
    return $aList;
  }
  
  public static function getInstance() {
    if (is_null(self::$oInstance )) {
      self::$oInstance = new FlexiModxPlatformHandler();
    }
    return self::$oInstance;
  }
}

?>
