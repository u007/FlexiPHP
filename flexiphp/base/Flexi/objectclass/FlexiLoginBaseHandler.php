<?php

abstract class FlexiLoginBaseHandler
{
	private $oUser = null;
  protected $aLoginForm = null;
  
  protected $sLoggedInId = null;
  public static $sUserType = "";

  public function __construct() {
    //restore session
    //echo "init flexilogin";
    $this->sLoggedInId = FlexiController::getInstance()->getSession("flexi.userid");
    $this->init();
  }

  //abstract, but not required
  public function init() {}
	/**
	 * DoLogin
	 * @param string userid
	 * @param string pwd
	 * @param array('key'=>'value')
	 * @return boolean
	 */
  public function getLoggedInUserType() {
    return self::$sUserType;
  }
	abstract public function onLogin(& $asLoginId, & $asLoginPass, & $asConfig=array());
  abstract public function getLoginURL($sUserId="", $sPassword="", $aOptions=null);
  abstract public function getIsVerified($asLoginId);
  abstract public function getUserByLoginId($asLoginId, $sUserType="user");
  abstract public function existsUser($asLoginId, $sUserType="user");
  abstract public function getLostPassword($asLoginId);
  abstract public function changePassword($iUserId, $sPassword);
  
  abstract public function checkLoginSession();

  abstract public function forceLogin($iUserId, $sUserType="user", $sContext="web");
  
  public function doLogin($asLoginId, $asLoginPass, $asConfig=array()) {
    
    $bResult = $this->doFlexiAdminLogin($asLoginId, $asLoginPass);
    FlexiLogger::debug(__METHOD__, "FlexiAdminLogin: " . ($bResult ? "success" : "fail"));
    
    if (!$bResult) {
      $bResult = $this->onLogin($asLoginId, $asLoginPass, $asConfig);
    }

		return $bResult;
  }

	/**
	 * triggered by logout,
   *  if return true, proceed with logout,
   *    otherwise, not
	 * @return boolean
	 */
	abstract public function onLogout($context="web");

  /**
   * will trigger onLogout,
   *  and do unset of session
   * @return booelan
   */
  public function logout() {
    if(! $this->onLogout()) { return false; }

    $this->sLoggedInId = null;
    FlexiController::getInstance()->unsetSession("flexi.userid");
    return true;
  }
	
	/**
	 * get currently logged in user language / guest
	 * @return string
	 */
	abstract public function getUserLanguage($context="web");
	
	/**
	 * Has role?
	 * @param String role, separated by comma
	 * @return boolean
	 */
	abstract public function hasRole($sRole);
	
	/**
	 * Has permission?
   *  To check if user have access to certain document
   *  direct access to function might be deprecated in later release
	 * @param String title
	 * @return boolean
	 */
	abstract public function hasPermission($sTitle);
	/**
	 * if current user is logged in
	 * @return boolean
	 */
	abstract public function isLoggedIn($sContext="web");
	
	/**
	 * Get logged in userid
	 * @return int
	 */
	abstract public function getLoggedInUserId($context="web");
	/**
	 * get logged in user
	 * @return FlexiLoginUserModel
	 */
	abstract public function getLoggedInUser($context="web");
	
	/**
	 * get if user is super user / admin, modx: manager
	 * @return boolean
	 */
	abstract public function isSuperUser();

  /**
   * Getting logged in user login name
   * @return String
   */
  abstract public function getUserLoginId($context="web");
  /**
   * Getting logged in user full name
   * @return String
   */
  abstract public function getUserName($context="web");
	
	protected function setUser($oUser)
	{
		$this->oUser = $oUser;
	}
	
	protected function getUser()
	{
    return $this->oUser;
	}

  /**
   * Get Login form
   * @return array
   */
  abstract public function getLoginForm();

  public function doLoginByToken($sToken) {
     //restoring based on token
    if (empty($sToken)) { return; }
    $oToken = FlexiModelUtil::getInstance()->getRedBeanFetchOne("select userid from service_token where token=:token",
            array(":token" => $sToken));

    if ($oToken != null && $oToken !== false) {
      FlexiConfig::getLoginHandler()->forceLogin($oToken["userid"]);
    } else {
      FlexiLogger::error(__METHOD__, "Invalid token: " . $sToken);
    }
  }

  public function doFlexiAdminLogin($asUserId, $asPassword) {
    if (empty(FlexiConfig::$sAdminUserId) || empty(FlexiConfig::$sAdminPassword)) {
      throw new FlexiException(flexiT("Admin login configuration not set", "first"), ERROR_CONFIGURATION);
    }

    if ($asUserId == FlexiConfig::$sAdminUserId && $asPassword == FlexiConfig::$sAdminPassword) {
      FlexiController::getInstance()->setSession("flexi.userid", FlexiConfig::$iAdminId);
      $this->sLoggedInId = FlexiConfig::$iAdminId;

      $this->forceLogin(FlexiConfig::$iAdminId);
      return true;
    }
    return false;
  }

  public function isFlexiAdminLoggedIn() {
    return $this->sLoggedInId == FlexiConfig::$iAdminId;
  }

  /**
   * Check if user have access policy
   * @param $sPolicy : Policy name, example "settings"
   */
  abstract public function hasAccessToPolicy($sPolicy);

  public function hasAccessToContent($sTitle) {
    return $this->hasPermission($sTitle);
  }

  public function checkPermission($sTitle) {
    if (! $this->hasAccessToContent($sTitle)) {
      FlexiLogger::error(__METHOD__, "Permission denied: " . get_class($this) . ":" . $sTitle);
      FlexiController::getInstance()->redirectURL(FlexiConfig::$sBaseURL . "?" .
        FlexiConfig::getRequestModuleVarName(). "=FlexiLogin&" . FlexiConfig::getRequestMethodVarName() . "=denied");
    }
    FlexiPlatformHandler::getPlatformHandler()->forceDie();
  }

}
