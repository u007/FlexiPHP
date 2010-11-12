<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class FlexiIScriptPlatformHandler extends FlexiPlatformHandler {

  
  private function __construct() {
    //SETUP users.userid as primary
    FlexiModelUtil::getInstance()->setRedBeanTableIdField(FlexiConfig::$sDBPrefix . "users", "user_id");
  }
  /**
   * dummy function to ensure function is not called directly
   */
  protected function checkMe() {

  }

  public function checkIfExistsUserName($sUserName, $iId=null) {
    FlexiLogger::debug(__METHOD__, "checking user: " . $sUserName);
    if (empty($sUserName)) { return false; }
    
    if (!empty($iId)) {
      $oModel = FlexiModelUtil::getInstance()->getRedbeanFetchOne("select * from " .
            FlexiConfig::$sDBPrefix . " where user_id=:id and user_name=:username",
              array(":id" => $iId, ":username"=> $sUserName));
      if (!empty($oModel["user_id"])) { return $oModel; }
      return false;
    } else {
      $oModel = FlexiModelUtil::getInstance()->getRedbeanFetchOne("select * from " .
            FlexiConfig::$sDBPrefix . " where user_name=:username", array(":username", $sUserName));
      if (!empty($oModel["user_id"])) { return $oModel; }
      return false;
    }
  }

  public function checkIfExistsUserEmail($sEmail, $iId=null) {
    FlexiLogger::debug(__METHOD__, "checking email: " . $sEmail);
    if (empty($sEmail)) { return false; }

    if (!empty($iId)) {
      $oModel = FlexiModelUtil::getInstance()->getRedbeanFetchOne("select * from " .
            FlexiConfig::$sDBPrefix . " where user_id=:id and email=:email",
              array(":id" => $iId, ":email"=> $sEmail));
      if (!empty($oModel["user_id"])) { return true; }
      return false;
    } else {
      $oModel = FlexiModelUtil::getInstance()->getRedbeanFetchOne("select * from " .
            FlexiConfig::$sDBPrefix . " where email=:email", array(":email", $sEmail));
      if (!empty($oModel["user_id"])) { return true; }
      return false;
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

    $oModel = FlexiModeUtil::getRedbeanModel(FlexiConfig::$sDBPrefix . "users");
    
    $oModel->user_name = $sUserName;
    $oModel->password = md5($sPassword);
    $oModel->first_name = $sFullName;
    $oModel->email = $aOptions["email"];
    $oModel->date_registered = FlexiDateUtil::getSQLDateNow();
    $oModel->deleted = "N";

    FlexiModelUtil::getInstance()->insertRedBean($oModel);
    
    if (!empty($oModel->email) && $bVerifyEmail) {
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

      $oGroupModel = FlexiModelUtil::getDBQuery("ModxWebgroups", "flexiphp/base/FlexiAdminGroup")
              ->where("webgroup=? and webuser=?", array($iGroupNameId, $iId))->fetchOne();

      if ($oGroupModel == null || $oGroupNameModel === false) {
        $oGroupModel = new ModxWebgroups();
        $oGroupModel->webuser = $iId;
        $oGroupModel->webgroup = $iGroupNameId;
        $oGroupModel->save();
      }
    }

    if (count($aGroups) > 0) {
      //clearing all other groups
      $sGroupId = implode(",", $aGroupId);
      FlexiModelUtil::getDBQuery("ModxWebgroups", "flexiphp/base/FlexiAdminGroup")
              ->delete("ModxWebgroups")
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
    $oModel = FlexiModelUtil::getInstance()->getRedBeanModel(FlexiConfig::$sDBPrefix."users", $iId);
    if (empty($oModel->user_id)) {
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
    return "UTF-8";
  }

  public function getAllAccessGroup() {
    return array();
  }
  public function getAllRoles() {
    return array();
  }
  
  public static function getInstance() {
    if (is_null(self::$oInstance )) {
      self::$oInstance = new FlexiIScriptPlatformHandler();
    }
    return self::$oInstance;
  }
}

?>
