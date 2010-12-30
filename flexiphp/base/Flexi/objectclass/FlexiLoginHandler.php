<?php

class FlexiLoginHandler extends FlexiLoginBaseHandler
{
  public function  init() {
    
  }

	
  public function forceLogin($iUserId, $sUserType="user", $sContext="web") {
    //TODO
    return false;
  }

	/**
   * do framework login
   * @param String $asLoginId
   * @param String $asLoginPass
   * @param array $asConfig (optional)
   * @return boolean
   */
	public function onLogin(& $asLoginId, & $asLoginPass, & $asConfig=array())
	{
    //TODO DB LOGIN
    //FlexiLogger::debug(__METHOD__, "FrameworkLogin: " . ($bResult ? "success" : "fail"));
    return false;
	}

  public function getUserByLoginId($asLoginId, $sUserType="user") {
    //TODO
    return null;
  }

  public function changePassword($iUserId, $sPassword) {
    //TODO
    return false;
  }

  public function getLostPassword($asLoginId) {
    //TODO
    return false;
  }

  public function checkLoginSession() {
    //TODO
    return false;
  }

  public function existsUser($asLoginId, $sUserType="user") {
    //TODO
    return false;
  }
	
	public function onLogout($context="web") {
		return true;
	}

  /**
   * Getting logged in user login name
   * @return String
   */
  public function getUserLoginId($context="web") {
		$oUser = $this->getLoggedInUser();
    if ($oUser == null) { return null; }
    
    return $oUser->sUserName;
  }
  /**
   * Getting logged in user full name
   * @return String
   */
  public function getUserName($context="web") {
    $oUser = $this->getLoggedInUser();
    if ($oUser == null) { return null; }

    return $oUser->sUserFullName;
  }
	
	public function getLoginURL($sUserId="", $sPassword="", $aOptions=null) {
    $sURL = FlexiConfig::$sLoginURL;
    $sURL .= strpos($sURL, "?") !== false? "&" : "?";
    $sURL .= "txtLogin=" . $sUserId;

    $sURL .= !empty($aOptions["querystring"]) ? "&" . $aOptions["querystring"] : "";
    $sURL .= !empty($aOptions["error"]) ? "&error=" . $aOptions["error"] : "";

    return flexiURL($sURL);
  }
  
	public function hasPermission($sTitle)
	{
		if ($this->isSuperUser()) {
      return true;
    }

    if (!$this->isLoggedIn()) {
      //TODO OTHER DB VALIDATION
    }

    return false;
	}
	
	public function hasRole($sRole)
	{
		
	}
	
	public function isLoggedIn($sContext="web") {
    $oUser = $this->getLoggedInUser();
    if ($oUser == null) {
      return false;
    }

    return true;
	}
	
	public function getLoggedInUser($context="web")	{
    $oUser = null;
    if ($this->isSuperUser()) {
      $oUser = new FlexiLoginUserModel();
      $oUser->iUserId = 1;
      $oUser->sUserFullName = FlexiConfig::$sAdminUserId;
      $oUser->sUserName = FlexiConfig::$sAdminUserId;
      $oUser->sPassword = FlexiConfig::$sAdminPassword;
      $oUser->sLanguage = FlexiConfig::$sDefaultLanguage;
    }

		return $oUser;
	}
	
	public function getUserLanguage($context="web") {
		//TODO
		//$sLang = $aData["language"];
//
//		if (! empty($sLang))
//		{
//			//$aLang = explode(",", $sLang);
//			//return $aLang[count($aLang)-1];
//			return $sLang;
//		}
		
		return FlexiConfig::$sDefaultLanguage;
	}
	
	public function isSuperUser()
	{
		if ($this->isFlexiAdminLoggedIn()) {
      return true;
    }
    //TODO other login type, based on db
	}
  
	public function getIsVerified($asLoginId) {
    //TODO
    return true;
  }

	public function getLoggedInUserId($context="web") {
    $oUser = $this->getLoggedInUser();
    if ($oUser == null) {
      return null;
    }

    return $oUser->iUserId;
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

  public function hasAccessToPolicy($sPolicy) {

  }


  
}
