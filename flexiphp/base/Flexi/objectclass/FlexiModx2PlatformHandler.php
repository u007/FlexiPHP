<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class FlexiModx2PlatformHandler extends FlexiPlatformHandler {

  
  private function __construct() {

  }
  /**
   * dummy function to ensure function is not called directly
   */
  protected function checkMe() {
    
  }
  
  public function assignCustomForm($sNamespace, $sAction, $sName, $sContainer, $sRule, $sValue, $sDescription, $sUserGroup, $sRole="", $sPolicy="") {
    global $modx;
    
    $action = $modx->getObject("modAction", array("namespace" => $sNamespace, "controller" => $sAction));
    if (is_null($action)) { throw new FlexiException("No such action: " . $action, ERROR_EOF); }


    $usergroup = $modx->getObject("modUserGroup", array("name" => $sUserGroup));
    if (is_null($usergroup)) { throw new FlexiException("No such user group: " . $sUserGroup, ERROR_EOF); }

    $iAuthority = 9999;
    if (!empty($sRole)) {
      $role = $modx->getObject("modUserGroupRole", array("name" => $sRole));
      if (is_null($role)) { throw new FlexiException("No such role: " . $sRole, ERROR_EOF); }
      $iAuthority = $role->get("authority");
    }

    $iPolicy = 0;
    if (!empty($sPolicy)) {
      $policy = $modx->getObject("modAccessPolicy", array("name" => $sPolicy));
      if (is_null($policy)) { throw new FlexiException("No such policy: " . $sPolicy, ERROR_EOF); }
      $iPolicy = $policy->get("id");
    }

    $dom = $modx->getObject("modActionDom",
      array("action" => $action->get("id"), "name" => $sName, "container" => $sContainer, "rule" => $sRule,
          "Access.principal" => $usergroup->get("id"), "Access.authority" => $iAuthority, "Access.policy" => $iPolicy, "Access.principal_class" => "modUserGroup"));
    
    if (is_null($dom)) {
      $dom = $modx->newObject("modActionDom",
        array("action" => $action->get("id"), "name" => $sName, "container" => $sContainer, "rule" => $sRule));
    }

    $dom->set("value", $sValue);
    $dom->set("active", 1);
    
    $iDom = $dom->get("id");
    $domaccess = null;
    if (! empty($iDom)) {
      $domaccess = $modx->getObject("modAccessActionDom", array(
        "target"          => $dom->get("id"),
        "principal_class" => "modUserGroup",
        "principal"       => $usergroup->get("id"),
        "authority"       => $iAuthority,
        "policy"          => $iPolicy
      ));
    }

    if (is_null($domaccess)) {
      $domaccess = $modx->newObject("modAccessActionDom", array(
        "principal_class" => "modUserGroup",
        "principal"       => $usergroup->get("id"),
        "authority"       => $iAuthority,
        "policy"          => $iPolicy
      ));
      $dom->addOne($domaccess);
    }

    $domaccess->set("value", $sValue);
    $domaccess->set("description", $sDescription);
    
    if (!$dom->save()) {
      return false;
    }
    $domaccess->set("target", $dom->get("id"));
    return $domaccess->save();
  }


  public function assignResourceGroupAccess($sResourceGroup, $sUserGroup, $sRole, $sPolicy, $sContext) {
    global $modx;

    $resgroup = $modx->getObject("modResourceGroup", array("name" => $sResourceGroup));
    if (is_null($resgroup)) { throw new FlexiException("No such resource group: " . $sResourceGroup, ERROR_EOF); }
    
    $usergroup = $modx->getObject("modUserGroup", array("name"=>$sUserGroup));
    if (is_null($usergroup)) { throw new FlexiException("No such user group: " . $sUserGroup, ERROR_EOF); }
    
    $role = $modx->getObject("modUserGroupRole", array("name" => $sRole));
    if (is_null($role)) { throw new FlexiException("No such role: " . $sRole, ERROR_EOF); }

    $policy = $modx->getObject("modAccessPolicy", array("name" => $sPolicy));
    if (is_null($policy)) { throw new FlexiException("No such policy: " . $sPolicy, ERROR_EOF); }
    
    $access = $modx->getObject("modAccessResourceGroup",
            array("context_key" => $sContext,
                "target" => $resgroup->get("id"),
                "principal_class" => "modUserGroup",
                "authority" => $role->get("authority"),
                "principal" => $usergroup->get("id"),
                "policy" => $policy->get("id")
                ));

    if (is_null($access)) {
      $access = $modx->newObject("modAccessResourceGroup",
          array("context_key" => $sContext,
              "target" => $resgroup->get("id"),
              "principal_class" => "modUserGroup",
              "authority" => $role->get("authority"),
              "principal" => $usergroup->get("id"),
              "policy" => $policy->get("id")
              ));
      if (!$access->save()) { throw new FlexiException("Save Access ", $code); }
      return true;
    }
  }

  public function assignContextAccess($sUserGroup, $sRole, $sContext, $sPolicy) {
    global $modx;
    $usergroup = $modx->getObject("modUserGroup", array("name"=>$sUserGroup));
    if (is_null($usergroup)) { throw new FlexiException("No such user group: " . $sUserGroup, ERROR_EOF); }

    $role = $modx->getObject("modUserGroupRole", array("name" => $sRole));
    if (is_null($role)) { throw new FlexiException("No such role: " . $sRole, ERROR_EOF); }
    
    $policy = $modx->getObject("modAccessPolicy", array("name" => $sPolicy));
    if (is_null($policy)) { throw new FlexiException("No such policy: " . $sPolicy, ERROR_EOF); }
    
    $access = $modx->getObject("modAccessContext",
            array(
                "target" => $sContext,
                "principal_class" => "modUserGroup",
                "authority" => $role->get("authority"),
                "principal" => $usergroup->get("id"),
                "policy" => $policy->get("id")
                ));

    if (is_null($access)) {
      $access = $modx->newObject("modAccessContext",
          array(
              "target" => $sContext,
              "principal_class" => "modUserGroup",
              "authority" => $role->get("authority"),
              "principal" => $usergroup->get("id"),
              "policy" => $policy->get("id")
              ));
      if (!$access->save()) { throw new FlexiException("Save Access ", $code); }
      return true;
    }
  }

  public function assignResourceGroup($iDocId, $sGroupName) {
    global $modx;
    $doc = $modx->getObject("modResource", $iDocId);
    if (is_null($doc)) {
      throw new FlexiException("No such resource: " . $iDocId, ERROR_EOF);
    }
    $group = $modx->getObject("modResourceGroup", array("name" => $sGroupName));
    if (is_null($group)) {
      throw new FlexiException("No such resource group: " . $sGroupName, ERROR_EOF);
    }

    $link = $modx->getObject("modResourceGroupResource", 
            array("document_group" => $group->getName(), "document" => $doc->get("id")));
    if (is_null($link)) {
      //no such link, create it
      $link = $modx->newObject(modResourceGroupResource,
              array("document_group" => $group->getName(), "document" => $doc->get("id")));
      return $link->save();
    }
    return true;
  }

  public function assignUserToGroup($iUserId, $sGroupName, $iRoleLevel = 9999) {
    global $modx;
    $oUser = $modx->getObject("modUser", $iUserId);
    if (is_null($oUser)) {
      throw new FlexiException("No such user: " . $iUserId, ERROR_EOF);
    }
    
    $oGroup = $modx->getObject("modUserGroup", array("name"=> $sGroupName));
    if (is_null($oGroup)) {
      throw new FlexiException("No such user group: " . $sGroupName, ERROR_EOF);
    }
    
    $aGroup = $oUser->getMany("UserGroupMembers");
    $bIsInGroup = false;
    //check if user already in the group
    foreach($aGroup as $oUserGroup) {
      if ($oUserGroup->get("user_group") == $oGroup->get("id")) {
        $bIsInGroup = true;
        break;
      }
    }

    if (! $bIsInGroup) {
      echo "not in group: ";
      $oNewGroup = $modx->newObject("modUserGroupMember", array(
        "user_group" => $oGroup->get("id"),
        "role" => $iRoleLevel,
        "member" => $iUserId
      ));
      var_dump($oNewGroup->toArray());
      $oUser->addOne($oNewGroup);
      var_dump($oNewGroup->toArray());
      return $oNewGroup->save();
    } else { echo "in group"; }

    return true;
  }

  public function createUserGroup($sName, $iParent = 0) {
    global $modx;
    $oGroup = $modx->newObject("modUserGroup", array(
      "name" => $sName, "parent" => $iParent
    ));
    if($oGroup->save()===false) {
      return false;
    }
    return $oGroup;
  }

  public function createResourceGroup($sName) {
    global $modx;
    $oGroup = $modx->newObject("modResourceGroup", array(
      "name" => $sName
    ));
    
    if($oGroup->save()===false) {
      return false;
    }
    return $oGroup;
  }

  public function getUploadSize() {
    global $modx;
    return $modx->context->getOption('upload_maxsize',false);
  }

  public function checkIfExistsUserName($sUserName, $iId=null) {
    FlexiLogger::debug(__METHOD__, "checking user: " . $sUserName);
    if (empty($sUserName)) { return false; }
    global $modx;

    $aWhere = array("username" => $sUserName);
    if (!empty($iId)) { $aWhere["id"] = $iId; }
    $oQuery = $modx->newQuery("modxUser")->where($aWhere);
    $oModel = $modx->getObject("modxUser", $oQuery);
    return !is_null($oModel);
  }

  public function checkIfExistsUserEmail($sEmail, $iId=null) {
    FlexiLogger::debug(__METHOD__, "checking email: " . $sEmail);
    if (empty($sEmail)) { return false; }
    global $modx;
    
    $aWhere = array("Profile.email" => $sEmail);
    if (!empty($iId)) { $aWhere["id"] = $iId; }
    $oQuery = $modx->newQuery("modxUser")->where($aWhere);
    $oModel = $modx->getObject("modxUser", $oQuery);
    return !is_null($oModel);
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

    global $modx;
    $oModel = $modx->newObject("modUser");
    $oModel->set("username",$sUserName );
    $oModel->set("password", md5($sPassword));

    $oProfileModel = $modx->newObject("modUserProfile");
    $oProfileModel->set("fullname", $sFullName);
    $oModel->addOne($oProfileModel);
    
    if (isset($aOptions["email"])) {
      $oProfileModel->set("email", $aOptions["email"]);
    }

    $oModel->save();
    $iUserId = $oModel->get("id");

    //$oGroupQuery = $modx->newQuery("modUserGroup");
    foreach($aGroups as $sGroup) {
      $oGroupNameModel = $modx->getObject("modUserGroup", array("name"=>$sGroup));
      if (is_null($oGroupNameModel)) {
        throw new FlexiException("No group by: " . $sGroup, ERROR_EOF);
      }
      
      $iGroupNameId = $oGroupNameModel->get("id");
      $oGroupModel = $modx->newObject("modWebGroupMember");
      $oGroupModel->set("webuser", $iUserId);
      $oGroupModel->set("webgroup", $iGroupNameId);
      $oGroupModel->save();
    }

    $sEmail = $oModel->getOne("Profile")->get("email");
    if (!empty($sEmail) && $bVerifyEmail) {
      $oView = new FlexiView();

      $oUserExtend = FlexiModelUtil::Instance()->getRedbeanFetchOne(
              "select * from modx_userextend where userid=:userid", array(":userid" => $iUserId));
      $sVerifyCode = "";
      if (!empty($oUserExtend->id)) {
        $sVerifyCode = $oUserExtend->verifycode;
        $sMessage = $oView->render("user.verify", array("fullname" => $sFullName,
          "module" => "FlexiLogin" , "method" =>"verify", "params" => array("id" => $oModel->id, "r" => $sVerifyCode)));
        $oMail = FlexiMailer::getInstance();
        $oMail->setFrom(FlexiConfig::$sSupportEmail);
        $oMail->mail($sSubject, $sMessage, $sEmail);
      }
    }

    return $oModel;
  }

  public function updateUser($iId, $sUserName, $sFullName = null, $sPassword = null, $aOptions=array(), $aGroups = array()) {
    $this->checkMe();
    global $modx;
    
    $oModel = $this->getUserById($iId);
    $oModel->set("username", $sUserName);

    $oProfile = $oModel->getOne("Profile");
    if (is_null($oProfile)) {
      $oProfile = $oModel->addOne("Profile");
    }
    if ($sFullName != null) {
      $oProfile->set("fullname", $sFullName);
    }

    if ($sPassword != null) {
      $oMail = FlexiMailer::getInstance();
      $oMail->setFrom(FlexiConfig::$sSupportEmail);
      $oMail->mail($sSubject, $sMessage, $sEmail);
      $oModel->set("password", md5($sPassword));
    }

    if (isset($aOptions["email"])) {
      $oProfile->set("email", $aOptions["email"]);
    }

    $oModel->save();

    $aGroupId = array();
    foreach($aGroups as $sGroup) {
      $oGroupNameModel = $modx->getObject("modUserGroup", array("name"=>$sGroup));
      if (is_null($oGroupNameModel)) {
        throw new FlexiException("No group by: " . $sGroup, ERROR_EOF);
      }

      $iGroupNameId = $oGroupNameModel->get("id");
      $aGroupId[] = $iGroupNameId;
      $oGroupModel = $modx->getObject("modWebGroupMember",
              array("webgroup" => $iGroupNameId, "webuser" => $iId));
      if (is_null($oGroupModel)) {
        $oGroupModel = $modx->newObject("modWebGroupMember");
        $oGroupModel->set("webuser", $iId);
        $oGroupModel->set("webgroup", $iGroupNameId);
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
    global $modx;
    $oModel = $modx->getObject("modUser", $iId);
    if (is_null($oModel)) {
      return false;
    }

    $oUserExtend = FlexiModelUtil::Instance()->getRedbeanFetchOne(
              "select * from modx_userextend where userid=:userid", array(":userid" => $iId));
    if (!empty($oUserExtend->id)) {
      $sVerifyCode = $oUserExtend->verifycode;

      if ($sCode == $sVerifyCode) {
        return true;
      }
    }
    FlexiLogger::error(__METHOD__, "Unable to save User verification");

    return false;
  }

  public function getUserById($iId) {
    global $modx;
    $oModel = $modx->getObject("modUser", $iId);

    if (is_null($oModel)) {
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
            "select name from modx_membergroup_names order by name asc");
    return $aList;
  }
  
  public static function getInstance() {
    if (is_null(self::$oInstance )) {
      self::$oInstance = new FlexiModx2PlatformHandler();
    }
    return self::$oInstance;
  }
}

?>
