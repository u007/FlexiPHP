<?php

class FlexiIScriptLoginHandler extends FlexiLoginBaseHandler
{
	public function  init() {
    
  }
  /**
   * Check if login session is same as last login in db
   */
  public function checkLoginSession() {
    return true;
  }

  /**
   * change password by id
   * @param int $iUserId
   * @param String $sPassword
   * @return boolean
   */
  public function changePassword($iUserId, $sPassword) {
    return false;
  }

  public function getLostPassword($asLoginId) {
    return true;
  }

  public function getIsVerified($asLoginId) {
    return false;
  }

  public function existsUser($asLoginId, $sUserType="user") {
    $oUser = $this->getUserByLoginId($asLoginId, $sUserType);

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

    if ($sUserType=="user") {
      $bean = FlexiModelUtil::getInstance()->getRedbeanFetchOne("select * from " . FlexiConfig::$sDBPrefix . "users"
              ." where user_id=:user_id AND deleted = 'N'", array(":user_id" => $iUserId));
      if (empty($bean["user_id"])) {
        throw new FlexiException("User Id: " . $iUserId . "($sUserType) not found", ERROR_EOF);
      }
      $_SESSION["sess_username"] = $bean["user_name"];
      $_SESSION["sess_userid"] = $bean["user_id"];
    } else if($sUserType == "seller") {
      $bean = FlexiModelUtil::getInstance()->getRedbeanFetchOne("select * from " . FlexiConfig::$sDBPrefix . "artists"
              ." where artist_id=:user_id AND deleted = 'N'", array(":user_id" => $iUserId));
      if (empty($bean["artist_id"])) {
        throw new FlexiException("User Id: " . $iUserId . "($sUserType) not found", ERROR_EOF);
      }
      $_SESSION["sess_artistname"] = $bean["artist_name"];
			$_SESSION["sess_artistid"] = $bean["artist_id"];
    }
    
    return true;
  }


	public function onLogin(& $asLoginId, & $asLoginPass, & $asConfig=array())
	{
    global $modx;
    $sDBPass = md5($asLoginPass);
    FlexiLogger::debug(__METHOD__, "Trying to login: " . $asLoginId . ", " . $asLoginPass . "(" . $sDBPass . ")");
    
    $aParams = array("loginid" => & $asLoginId,
        "password" => & $asLoginPass, "config" => & $asConfig);
    FlexiEvent::triggerEvent("preLogin", $aParams);

    $sType = $asConfig["type"];
    if ($sType=="user") {
      $bean = FlexiModelUtil::getInstance()->getRedbeanFetchOne("select * from " . FlexiConfig::$sDBPrefix . "users"
            ." where user_name=:user_name and password=:password AND deleted = 'N'",
            array(":user_name" => $asLoginId, ":password" => $sDBPass));
      if (empty($bean["user_id"])) {
        FlexiLogger::error(__METHOD__, "User Id: " . $iUserId . "($sType) not found");
      } else {
        $bStatus = true;
      }
    } else if ($sType=="seller") {
      $bean = FlexiModelUtil::getInstance()->getRedbeanFetchOne("select * from " . FlexiConfig::$sDBPrefix . "artists"
            ." where artist_name=:user_name and password=:password AND deleted = 'N'",
            array(":user_name" => $asLoginId, ":password" => $sDBPass));
      if (empty($bean["artist_id"])) {
        FlexiLogger::error(__METHOD__, "User Id: " . $iUserId . "($sType) not found");
      } else {
        $bStatus = true;
      }
    }
    
    
    $mReturn = array("msg" => "");

    $aParams = array("status" => & $bStatus,
      "user" => & $bean, "loginid" => & $asLoginId, "password" => & $asLoginPass, "config" => & $asConfig,
      "return" => & $mReturn);

    FlexiEvent::triggerEvent("postLogin", $aParams);
    
    if($bStatus){
      FlexiLogger::debug(__METHOD__, "Login Success: " . $asLoginId . "($sType)");

      if ($sType=="user") {
        $_SESSION["sess_username"] = $bean["user_name"];
        $_SESSION["sess_userid"] = $bean["user_id"];
      } else if($sType=="seller") {
        $_SESSION["sess_artistname"] = $bean["artist_name"];
				$_SESSION["sess_artistid"] = $bean["artist_id"];
      }
      
      if ($bean["user_id"] == FlexiConfig::$iAdminId) {
        $adminbean = FlexiModelUtil::getInstance()->getRedbeanFetchOne(
                "select * from " . FlexiConfig::$sDBPrefix . "settings limit 1");
        $_SESSION["sess_adminname"] = $adminbean["admin_name"];
      }

      return true;
    }

    FlexiLogger::debug(__METHOD__, "Login Failed: " . $asLoginId );
    
		return false;
	}

  public function getUserByLoginId($asLoginId, $sUserType="user") {
    static $oUser = null;
    if ($oUser != null && $this->getUserType() == $sUserType && $this->getUserLoginId() == $asLoginId) {
      return $oUser;
    }
    //echo "Getting: " . $asLoginId . ", type: " . $sUserType;
    if ($sUserType == "seller") {
      $oUser = FlexiModelUtil::getInstance()->getRedbeanFetchOne("select * from " . FlexiConfig::$sDBPrefix . "artists"
            ." where artist_name=:user_id AND deleted = 'N'", array(":user_id" => $asLoginId));
      if (empty($oUser["artist_id"])) {
        return null;
      }
    } else if($sUserType == "user") {
      $oUser = FlexiModelUtil::getInstance()->getRedbeanFetchOne("select * from " . FlexiConfig::$sDBPrefix . "users"
            ." where user_id=:user_id AND deleted = 'N'", array(":user_id" => $asLoginId));
      if (empty($oUser["user_id"])) {
        return null;
      }
    }
    
    return $oUser;
  }
  /**
   * Get Loggin
   * @return <type>
   */
	public function getUserLoginId() {
    if ($this->isLoggedIn()) {
      if ($this->getUserType() == "user") {
        return $_SESSION["sess_username"];
      } else if ($this->getUserType() == "seller") {
        return $_SESSION["sess_artistname"];
      }
    }
    return null;
  }
  public function getUserName() {
    if ($this->isLoggedIn()) {
      $oUser = $this->getLoggedInUser();
      if ($this->getUserType() == "user") {
        return $oUser["first_name"] . (empty($oUser["last_name"]) ? "" : " " . $oUser["last_name"]);
      }
    }
    return null;
  }

	public function onLogout()
	{
    FlexiLogger::debug(__METHOD__, "Logging-out");
    unset($_SESSION["sess_adminname"]);
    unset($_SESSION["sess_username"]);
    unset($_SESSION["sess_userid"]);
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
		
		if (empty($sRole)) { return false; }

    if ($this->isSuperUser()) { return true; }
		//no role, just is logged in
    return true;
	}
	
	public function hasPermission($sTitle)
	{
    if ($this->isSuperUser()) { return true; }
    //no role, just is logged in
    return true;
	}
	
	public function isLoggedIn()
	{
    return !empty($_SESSION["sess_userid"]) || !empty($_SESSION["sess_artistid"]);
	}
	
	/**
	 * get currently logged in user language / guest
	 * @return string
	 */
	public function getUserLanguage()
	{
		return FlexiConfig::$sDefaultLanguage;
	}
	
	public function isSuperUser()
	{
    if (! $this->isLoggedIn()) { return false; }
    if (!empty($_SESSION["sess_adminname"])) { return true; }
    return false;
	}
	
	public function getLoggedInUserId()
	{
		if (! $this->isLoggedIn())
		{
			return null;
		}
    $iUserId = $_SESSION["sess_userid"];
    self::$sUserType = "user";
    if (empty($iUserId)) {
      $iUserId = $_SESSION["sess_artistid"];
      self::$sUserType = "seller";
    }
    
    return $iUserId;
	}
	/**
	 * Get logged in user
	 * @return FlexiLoginUserModel
	 */
	public function getLoggedInUser()
	{
		if (is_null($this->oUser) && $this->isLoggedIn())
		{
      $oUser = $this->getUserByLoginId($this->getUserLoginId(), $this->getUserType());
			$this->setUser($oUser);
		}
    return $this->getUser();
	}
}
