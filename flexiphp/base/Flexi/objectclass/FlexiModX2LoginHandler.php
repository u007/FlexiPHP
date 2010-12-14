<?php

class FlexiModX2LoginHandler extends FlexiLoginBaseHandler
{
	public function  init() {
    
  }

  /**
   * Check if login session is same as last login in db
   */
  public function checkLoginSession() {
    global $modx;
    $iUserId = $this->getLoggedInUserId();
    $oModel = $modx->getObject("modUser", $iUserId);
    if (is_null($iModel)) {
      return false;
    }

    $oProfile = $oModel->getOne("Profile");
    if (!is_null($oProfile)) {
      if ($oProfile->get("sessionid")) {
        $sSessionId = session_id();
        if ($sSessionId != $oModel->get("sessionid")) {
          FlexiLogger::error(__METHOD__, "Login expired: " . $sSessionId . " vs " . $oModel->get("sessionid"));
          $this->onLogout();
          return false;
        }
      }
    }
    return true;
  }

  /**
   * change password by id
   * @param int $iUserId
   * @param String $sPassword
   * @return boolean
   */
  public function changePassword($iUserId, $sPassword) {
    //echo "id: " . $iUserId . ", pwd: " . $sPassword;
    if (empty($iUserId) || empty($sPassword)) { return false; }
    global $modx;
    $oModel = $modx->getObject("modUser", $iUserId);
    if (is_null($oModel)) {
      return false;
    }
    $oModel->set("password", md5($sPassword));
    $oModel->save();
    return true;
  }

  public function getLostPassword($asLoginId) {
    //$oRow = FlexiModelUtil::getDBQuery("ModxWebUsers", "flexiphp/base/FlexiAdminUser")->where("username=?", array($asLoginId))->fetchOne();

//    if ($oRow !== false && $oRow != null) {
//      //TODO
//
//    }

    return true;
  }

  public function getIsVerified($asLoginId) {
    $oUser = $this->getUserByLoginId($asLoginId);
    if(is_null($oUser)) {
      return false;
    }
    $oExtend = $oUser->getOne("Profile")->get("extend");
    if (empty($oExtend)) {
      return false;
    }
    return $oExtend->get("verified") == 1;
  }

  public function existsUser($asLoginId, $sUserType="user") {
    $oUser = $this->getUserByLoginId($asLoginId);

    if ($oUser == null || $oUser === false) {
      return false;
    }

    return true;
  }

  /**
   * will not trigger event
   *  TODO:trigger event?
   */
  public function forceLogin($iUserId, $sUserType="user", $sContext="web") {
    global $modx;
    $modx->getObject("modUser", $iUserId);
    $bStatus = !is_null($oUser);

    if($bStatus){
      FlexiLogger::debug(__METHOD__, "Login Success: " . $asLoginId);
      //success
      $_SESSION[$sContext . "Validated"] = 1;
      $_SESSION[$sContext . "InternalKey"] = $oUser->id;
      $_SESSION[$sContext . 'Shortname'] = $oUser->username;
      $_SESSION['userid'] = $oUser->id;

      //getting group names
      $aGroupModel = $oUser->getMany("UserGroupMembers");
      $aGroup = array();
      foreach($aGroupModel as $oGroup) {
        foreach($oGroup->GroupAccess as $oAccess) {
          $aGroup[] = $oGroup->getOne("UserGroup")->get("name");
        }
      }
      $_SESSION['webDocgroups'] = $aGroup;
    } //if login ok
  }

  /**
   * Called from modx plugin onlogin
   * @global modx $modx
   * @param modUser $oUser
   */
  public function onLoggedIn($oUser) {
    global $modx;
    FlexiLogger::info(__METHOD__, "user: " . serialize($oUser->get("id")));
    $aGroupModel = $oUser->getMany("UserGroupMembers");
    $aGroup = array();
    FlexiLogger::info(__METHOD__, "Group cnt: " . count($aGroupModel));
    foreach($aGroupModel as $oGroup) {
    
      $aDocGroup = $modx->getCollection("modAccessResourceGroup", array(
          'principal_class' => 'modUserGroup',
          'principal' => $oGroup->get("user_group"),
      ));
      //FlexiLogger::info(__METHOD__, "group id: " . $oGroup->get("user_group") . ", doc cnt: " . count($aDocGroup));
      
      foreach($aDocGroup as $oDocGroup) {
        FlexiLogger::info(__METHOD__, "group id: " . $oDocGroup->get("target"));
        $aGroup[] = $docgroupid = $oDocGroup->get("target");
      }
    }
    $_SESSION['webDocgroups'] = $aGroup;
  }

	public function onLogin(& $asLoginId, & $asLoginPass, & $asConfig=array())
	{
    global $modx;
    
    $sDBPass = md5($asLoginPass);
    $aParams = array("loginid" => & $asLoginId,
        "password" => & $asLoginPass, "config" => & $asConfig);
    FlexiEvent::triggerEvent("preLogin", $aParams);
    
    $aWhere = array("username" => $asLoginId, "password" => $sDBPass);
    $oQuery = $modx->newQuery("modUser")->where($aWhere);
    $oUser = $modx->getObject("modUser", $oQuery);
    if (is_null($oUser)) {
      return false;
    }
    $bStatus = false;
    if (FlexiConfig::$bRequireEmailVerification) {
      $oProfile = $oUser->getOne("Profile");
      if ($oProfile){
        $oExtend = $oProfile->get("extend");
        if (! empty($oExtend)) {
          if ($oExtend->get("verified") != 1) {
            FlexiLogger::error(__METHOD__, "Account not verified: " . $oUser->get("id"));
            return false;
          }
        } //if extend has value
      } //if profile exists
      $bStatus = true;
    } //if need email verification
    else {
      $bStatus = true;
    }
    FlexiLogger::info(__METHOD__, "after verification");
    $mReturn = array("msg" => "");
    $aParams = array("status" => & $bStatus,
      "user" => & $oUser, "loginid" => & $asLoginId, "password" => & $asLoginPass, "config" => & $asConfig,
      "return" => & $mReturn);

    FlexiEvent::triggerEvent("postLogin", $aParams);
    
    if($bStatus){
      FlexiLogger::info(__METHOD__, "Login Success: " . $asLoginId . ", op context: " . $asConfig["context"]);
      
      $additionalContext = "";
      $loginContext = !empty($asConfig["context"]) ? $asConfig["context"] : "web";
      if (strpos($loginContext,",") !==false) {
        $contexts=explode(",", $loginContext);
        $loginContext = array_shift($contexts);
        $additionalContext = implode(",", $contexts);
      }
      /* set default POST vars if not in form */
      $scriptProperties = array(
        "username" => $asLoginId,
        "password" => $asLoginPass,
        "rememberme" => isset($asConfig["rememberme"]) ? $asConfig["rememberme"]: false,
        "session_cookie_lifetime" => isset($asConfig["session_cookie_lifetime"]) ? $asConfig["session_cookie_lifetime"]: null,
        "login_context" => $loginContext
      );
      if (!empty($additionalContext)) { $scriptProperties["add_contexts"] = $additionalContext; }
      FlexiLogger::info(__METHOD__, "Login: " . print_r($scriptProperties,true));
      $_POST = array_merge($_POST, $scriptProperties);
      FlexiLogger::debug(__METHOD__, "Trying to login: " . $asLoginId . ", " . $asLoginPass . "(" . $sDBPass . ")");
      /* send to login processor and handle response */
      $response = $modx->executeProcessor(array(
          'action' => 'login',
          'location' => 'security',
          'login_context' => $loginContext
      ));
      //echo "url: " . $response['object']['url'];
      if (!empty($response) && is_array($response)) {
          if (!empty($response['success']) && isset($response['object'])) {
              FlexiLogger::info(__METHOD__, "Modx2.Login Success: " . $asLoginId );

              FlexiLogger::info(__METHOD__, "groups: " . implode(",", $modx->getUserDocGroups(true)));
          } else {
              FlexiLogger::error(__METHOD__, "Modx2.Login Failed: " . $asLoginId );
          }
      }
      
      //success
//      $_SESSION["webValidated"] = 1;
//      $_SESSION["webInternalKey"] = $oUser->get("id");
//      $_SESSION['webShortname'] = $oUser->get("username");
//      $_SESSION['userid'] = $oUser->get("id");
//
//      //getting group names
//      FlexiLogger::info(__METHOD__, "Logged in: " . $_SESSION['webShortname']);
//      $aGroupModel = $oUser->getMany("UserGroupMembers");
//      FlexiLogger::info(__METHOD__, "Doc groups: " . count($aGroupModel));
//      $aGroup = array();
//      foreach($aGroupModel as $oGroup) {
//        foreach($oGroup->GroupAccess as $oAccess) {
//          $aGroup[] = $oGroup->getOne("UserGroup")->get("name");
//        }
//      }
//      $_SESSION['webDocgroups'] = $aGroup;

      return true;
    }

    FlexiLogger::debug(__METHOD__, "Login Failed: " . $asLoginId );
    
		return false;
	}

  public function getUserByLoginId($asLoginId, $sUserType="user") {
    static $oUser = null;
    if ($oUser != null && $oUser->username == $asLoginId) {
      return $oUser;
    }

    global $modx;
    $oQuery = $modx->newQuery("modUser")->where(array("username" => $asLoginId));
    $oUser = $modx->getObject("modUser", $oQuery);
    if (is_null($oUser)) {
      return null;
    }
    return $oUser;
  }
  /**
   * Get Loggin
   * @return <type>
   */
	public function getUserLoginId($context="web") {
    global $modx;
    return $modx->getLoginUserName($context);
  }
  public function getUserName($context="web") {
    $oUser = $this->getLoggedInUser($context);
    return $oUser->sUserFullName;
  }

	public function onLogout($context="web")
	{
    FlexiLogger::debug(__METHOD__, "Logging-out");
    unset($_SESSION[$context . "Validated"]);
    //$_SESSION["webInternalKey"] = null;
    $_SESSION[$context . 'Shortname'] = null;
    //$_SESSION['userid'] = null;
		return true;
	}

  public function getLoginURL($sUserId="", $sPassword="", $aOptions=null) {
    $sURL = FlexiConfig::$sLoginURL;
    $sURL .= strpos($sURL, "?") !== false? "&" : "?";
    $sURL .= "username=" . $sUserId;
    $sURL .= !empty($aOptions["error"]) ? "&error=" . $aOptions["error"] : "";

    $bStandAlone = isset($aOptions["standalone"]) ? $aOptions["standalone"] : false;
    if ($bStandAlone) { $sURL .="&standalone=1"; }

    if (!empty($aOptions["url"])) {
      $sURL .= "&refurl=" . FlexiCryptUtil::b64URLEncrypt($aOptions["url"]);
    }
    
    //echo ($bStandAlone) ? "standalone" : "full";
    return flexiURL($sURL, $bStandAlone);
  }

  public function getLoginForm($aRow = null, $bReload = false) {

    if ($this->aLoginForm != null) {
      return $this->aLoginForm;
    }

    if (!empty($aRow["error"])) {
      FlexiPlatformHandler::getPlatformHandler()->addMessage($aRow["error"], "error");
    }

    $iSize = 25;
    $this->aLoginForm = $aForm = array(
		"txtLogin" =>
			array("#type" => "textfield", "#title" => flexiT("User-Id", "first"), "#weight" => 5, "#maxlength" => 100,
				"#required" => true, "#size" => $iSize,
				"#default_value" => ""),

		"txtPassword" =>
			array("#type" => "password", "#title" => flexiT("Password", "first"), "#weight" => 7, "#maxlength" => 50,
				"#required" => true, "#size" => $iSize,
				"#default_value" => ""),

    "txtURL" => array("#type" => "hidden", "#default_value" => FlexiConfig::$sBaseURL),

		"bSubmit" =>
			array("#type" => "submit", "#value" => flexiT("Login", "first"), "#weight" => 57,
			)

		);

		//fill in form value
    FlexiFormUtil::mergeFormWithData($aForm, $aRow);

		$aTheForm = array_merge($aForm, array("#type" => "form", "#upload" => false, "#method" => "post",
			"#action" => flexiURL("mod=FlexiLogin&method=login")));

		$aTheLoginForm = array(
			"#type" => "div",
			"#title" => flexiUCFirstT("login form"),
			"form" => $aTheForm,
      "#attributes" => array("class" => "flexiphp_login_div")
		);

    //todo event for form before output
    $this->aLoginForm = $aTheLoginForm;
    return $this->aLoginForm;
  }
	
	/**
	 * has role,
	 * 	in modx its to has group
	 * @param mixed: array / string
	 * @return boolean
	 */
	public function hasRole($sRole)
	{
    if (! $this->isLoggedIn()) { return false; }
		global $modx;
		
		if (empty($sRole)) { return false; }

    if ($this->isSuperUser()) { return true; }
		
		if (is_array($sRole))
		{
			return $modx->isMemberOfWebGroup($sRole);
		}
		
		$aRoles = explode(",", $sRole);
		return $modx->modx->isMemberOfWebGroup($aRoles);
	}

  public function hasAccessToPolicy($sPolicy) {
    global $modx;
    return $modx->hasPermission($sPolicy);
  }

	public function hasPermission($sTitle)
	{
    if ($this->isSuperUser()) { return true; }
    
		global $modx;
		$bResult = $modx->hasPermission($sTitle);
    FlexiLogger::debug(__METHOD__, "Checking permission: " . $sTitle);
    if (! $bResult) {
      $aGroups = $modx->getUserDocGroups(true);
      if ($aGroups != null) {
        foreach($aGroups as $sGroup) {
          //echo "Doc Group: " . $sGroup . " vs " . $sTitle . "\r\n<br/>";
          FlexiLogger::debug(__method__, "Doc Group: " . $sGroup . " vs " . $sTitle);
          if (strtolower(trim($sGroup)) == strtolower(trim($sTitle)) ) {
            $bResult = true;
            break;
          }
        }
      } else {
        //echo "doc group empty";
        FlexiLogger::debug(__METHOD__, "Doc Group empty: " . serialize($aGroups));
      }
    }

    return $bResult;
	}
	
	public function isLoggedIn($context="web")
	{
		global $modx;
    return $modx->user->isAuthenticated($context);
	}
	
	/**
	 * get currently logged in user language / guest
	 * @return string
	 */
	public function getUserLanguage($context="web")
	{
		global $modx;
		$aData = $modx->getAuthenticatedUser($context);
    if (is_null($aData)) {
      return FlexiConfig::$sDefaultLanguage;
    }
    $oProfile = $aData->getOne("Profile");

    if (is_null($oProfile)) {
      return FlexiConfig::$sDefaultLanguage;
    }
    $aExtended = $oProfile->get("extended");
		$sLang = $aExtended["language"];
		if (!empty($sLang))
		{
			$aLang = explode(",", $sLang);
			return $aLang[count($aLang)-1];
		}
		return FlexiConfig::$sDefaultLanguage;
	}
	
	public function isSuperUser()
	{
    if (! $this->isLoggedIn()) { return false; }
		global $modx;
		if ($modx->getLoginUserType() == "manager") { return true; }
    //echo "checking member web group";
    return $modx->isMemberOfWebGroup(array("Site Admins", "Administrator"));
	}
	
	public function getLoggedInUserId($context="web")
	{
		if (! $this->isLoggedIn($context))
		{
			return null;
		}
		global $modx;
    $user = $modx->getAuthenticatedUser($context);
    return $user->get("id");
		//return $modx->getLoginUserID($context);
	}
	/**
	 * Get logged in user
	 * @return FlexiLoginUserModel
	 */
	public function getLoggedInUser($context="web")
	{
    global $modx;
    $oModxUser        = $modx->getAuthenticatedUser($context);
    if (is_null($oModxUser)) {
      return null;
    }
    $oUser->iUserId   = $oModxUser->get("id");
    $oUserInfo 							= $modx->getWebUserInfo($oUser->iUserId);
    //var_dump($oUserInfo);
    $oUser->sUserFullName 	= $oUserInfo["fullname"];
    $oUser->sUserName 			= $oUserInfo["username"];
    $oUser->sPassword				= $oUserInfo["password"];
    $this->setUser($oUser);
    return $this->getUser();
	}
}
