<?php

class FlexiModXLoginHandler extends FlexiLoginBaseHandler
{
	public function  init() {
    
  }

  /**
   * Check if login session is same as last login in db
   */
  public function checkLoginSession() {

    $iUserId = $this->getLoggedInUserId();
    $oModel = FlexiModelUtil::getDBQuery("ModxWebUserAttributes", "flexiphp/base/FlexiAdminUser")->
            where("internalKey=?", array($iUserId))->fetchOne();
    
    if ($oModel !== false && $oModel != null) {
      //check session
      $sSessionId = session_id();
      if ($sSessionId != $oModel->sessionid) {
        FlexiLogger::error(__METHOD__, "Login expired: " . $sSessionId . " vs " . $oModel->sessionid);
        $this->onLogout();
        return false;
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
    
    $oModel = FlexiModelUtil::getDBQuery("ModxWebUsers", "flexiphp/base/FlexiAdminUser")->where("id=?", array($iUserId))->fetchOne();

    if ($oModel !== false && $oModel != null) {
      $oModel->password = md5($sPassword);
      //throws error on error
      $oModel->replace();
      return true;
    }

    //var_dump($oModel);

    return false;
  }

  public function getLostPassword($asLoginId) {
    $oRow = FlexiModelUtil::getDBQuery("ModxWebUsers", "flexiphp/base/FlexiAdminUser")->where("username=?", array($asLoginId))->fetchOne();

    if ($oRow !== false && $oRow != null) {
      //TODO
      
    }

    return true;
  }

  public function getIsVerified($asLoginId) {
    $oUser = $this->getUserByLoginId($asLoginId);

    if ($oUser == null || $oUser === false) {
      return false;
    }

    if ($oUser->Extend == null ) {
      return false;
    }

    return $oUser->Extend->verified == 1;
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
  public function forceLogin($iUserId, $sUserType="user") {

    $oQuery = FlexiModelUtil::getDBQuery("ModxWebUsers u", "flexiphp/base/FlexiAdminUser")
      ->where("u.id=?", array($iUserId));
//    if (FlexiConfig::$sAdminUserId == $iUserId) {
//      //get first user
//      $oQuery = FlexiModelUtil::getDBQuery("ModxWebUsers u", "flexiphp/base/FlexiAdminUser")
//      ->limit(1);
//    } else {
//      $oQuery = FlexiModelUtil::getDBQuery("ModxWebUsers u", "flexiphp/base/FlexiAdminUser")
//      ->where("u.id=?", array($iUserId));
//    }

    $oUser = $oQuery->fetchOne();
    $bStatus = $oUser != null && $oUser !== false;

    if($bStatus){
      FlexiLogger::debug(__METHOD__, "Login Success: " . $asLoginId);
      //success
      $_SESSION["webValidated"] = true;
      $_SESSION["webInternalKey"] = $oUser->id;
      $_SESSION['webShortname'] = $oUser->username;
      $_SESSION['userid'] = $oUser->id;

      $aGroupModel = FlexiModelUtil::getDBQuery("ModxWebGroups g", "flexiphp/base/FlexiAdminGroup")
        ->where("g.webuser=? ", array($oUser->id))->execute();

      $aGroup = array();
      foreach($aGroupModel as $oGroup) {
        foreach($oGroup->GroupAccess as $oAccess) {
          $aGroup[] = $oAccess->documentgroup;
        }
      }

      $_SESSION['webDocgroups'] = $aGroup;
    }
  }


	public function onLogin(& $asLoginId, & $asLoginPass, & $asConfig=array())
	{
    global $modx;
    $sDBPass = md5($asLoginPass);
    FlexiLogger::debug(__METHOD__, "Trying to login: " . $asLoginId . ", " . $asLoginPass . "(" . $sDBPass . ")");

    $aParams = array("loginid" => & $asLoginId,
        "password" => & $asLoginPass, "config" => & $asConfig);
    FlexiEvent::triggerEvent("preLogin", $aParams);
    
    $oQuery = FlexiModelUtil::getDBQuery("ModxWebUsers u", "flexiphp/base/FlexiAdminUser")
      ->where("u.username=? and u.password=?", array($asLoginId, $sDBPass));

    if (FlexiConfig::$bRequireEmailVerification) {
      $oQuery->leftJoin("u.Extend e");
      $oQuery->addWhere("e.verified=?", array(1));
    }

    $oUser = $oQuery->fetchOne();

    $bStatus = $oUser != null && $oUser !== false;
    $mReturn = array("msg" => "");

    $aParams = array("status" => & $bStatus,
      "user" => & $oUser, "loginid" => & $asLoginId, "password" => & $asLoginPass, "config" => & $asConfig,
      "return" => & $mReturn);

    FlexiEvent::triggerEvent("postLogin", $aParams);
    
    if($bStatus){
      FlexiLogger::debug(__METHOD__, "Login Success: " . $asLoginId);
      //success
      $_SESSION["webValidated"] = true;
      $_SESSION["webInternalKey"] = $oUser->id;
      $_SESSION['webShortname'] = $oUser->username;
      $_SESSION['userid'] = $oUser->id;

      $aGroupModel = FlexiModelUtil::getDBQuery("ModxWebGroups g", "flexiphp/base/FlexiAdminGroup")
        ->where("g.webuser=? ", array($oUser->id))->execute();
      
      $aGroup = array();
      foreach($aGroupModel as $oGroup) {
        foreach($oGroup->GroupAccess as $oAccess) {
          $aGroup[] = $oAccess->documentgroup;
        }
      }

      $_SESSION['webDocgroups'] = $aGroup;

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

    $oUser = FlexiModelUtil::getDBQuery("ModxWebUsers u", "flexiphp/base/FlexiAdminUser")
      ->where("u.username=?", array($asLoginId))->fetchOne();

    if ($oUser == null || $oUser === false) {
      return null;
    }

    return $oUser;
  }
  /**
   * Get Loggin
   * @return <type>
   */
	public function getUserLoginId() {
    global $modx;
    return $modx->getLoginUserName();
  }
  public function getUserName() {
    $oUser = $this->getLoggedInUser();
    return $oUser->sUserFullName;
  }

	public function onLogout()
	{
    FlexiLogger::debug(__METHOD__, "Logging-out");
    unset($_SESSION["webValidated"]);
    //$_SESSION["webInternalKey"] = null;
    $_SESSION['webShortname'] = null;
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
	
	public function hasPermission($sTitle)
	{
    if ($this->isSuperUser()) { return true; }
    
		global $modx;
		$bResult = $modx->hasPermission($sTitle);

    if (! $bResult) {
      $aGroups = $modx->getUserDocGroups(true);
      if ($aGroups != null) {
        foreach($aGroups as $sGroup) {
          //echo "Doc Group: " . $sGroup . " vs " . $sTitle . "\r\n<br/>";
          FlexiLogger::Debug(__method__, "Doc Group: " . $sGroup . " vs " . $sTitle);
          if (strtolower(trim($sGroup)) == strtolower(trim($sTitle)) ) {
            $bResult = true;
            break;
          }
        }
      } else {
        //echo "doc group empty";
        FlexiLogger::debug(__METHOD__, "Doc Group empty");
      }
    }

    return $bResult;
	}
	
	public function isLoggedIn()
	{
		global $modx;
    
		$oUser = $modx->userLoggedIn();
		
		if ($oUser === false) { return false; }
		else
		{ return true; }
	}
	
	/**
	 * get currently logged in user language / guest
	 * @return string
	 */
	public function getUserLanguage()
	{
		global $modx;
		//commented off below, didnt work
		//global $modx_lang_attribute;
		//return $modx_lang_attribute;
		$aData = $modx->getUserData();
		
		$sLang = $aData["language"];
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
    return $modx->isMemberOfWebGroup(array("Site Admins"));
	}
	
	public function getLoggedInUserId()
	{
		if (! $this->isLoggedIn())
		{
			return null;
		}
		global $modx;
		return $modx->getLoginUserID();
	}
	/**
	 * Get logged in user
	 * @return FlexiLoginUserModel
	 */
	public function getLoggedInUser()
	{
		if (is_null($this->oUser))
		{
			$oUser = FlexiModelUtil::getModelInstance("FlexiLoginUserModel", "flexiphp/base/Flexi");
			global $modx;


			$oUser->iUserId 				=	$modx->getLoginUserID();
      //var_dump("userid: " . $oUser->iUserId);
			//TODO demo
			//$oUser->iUserId					= 1;
			$oUserInfo 							= $modx->getWebUserInfo($oUser->iUserId);

      //var_dump($oUserInfo);
			//var_dump($oUserInfo);
			$oUser->sUserFullName 	= $oUserInfo["fullname"];
			$oUser->sUserName 			= $oUserInfo["username"];
			$oUser->sPassword				= $oUserInfo["password"];
			//var_dump($modx->getWebUserInfo(1));
			$this->setUser($oUser);
      return $this->getUser();
		}

    return null;
	}
}
