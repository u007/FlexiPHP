<?php

class FlexiLoginController extends FlexiBaseController
{
	
	public function methodDefault()
	{
		
		return true;
	}

  public function methodLoginForm() {
    return true;
  }

  public function methodForgotpassword2() {

    $oLogin = FlexiConfig::getLoginHandler();
    $oLogin->getLostPassword($this->getRequest("email"));
    

    return true;
  }

  public function methodForgotpassword() {
    return true;
  }

  public function methodLogout() {
    $sURL = $this->getRequest("url");
    if (empty($sURL)) {
      $sURL = FlexiConfig::getLoginHandler()->getLoginURL("","");
    }
    FlexiConfig::getLoginHandler()->logout();
    $this->redirectURL($sURL);
  }

  public function methodResendVerify() {
    $sUserId = $this->getRequest("txtLogin");
    $oLogin = FlexiConfig::getLoginHandler();
    //var_dump($sUserId);
    $oUser = $oLogin->getUserByLoginId($sUserId);

    if ($oUser == null || $oUser === false) {
      $this->oView->addVar("status", 1);
      return true;
    }

    $sEmail = $oUser->Attributes->email;
    if (! empty($sEmail)) {
      $sFullName = $oUser->Attributes->fullname;
      
      $oView = new FlexiView();
      $sMessage = $oView->render("user.verify", array("fullname" => $sFullName,
        "module" => "FlexiLogin" , "method" =>"verify", "params" => array("id" => $oUser->id, "r" => $oUser->Extend->verifycode)));

      $sSubject = "resend: Verification required";
      $oMail = FlexiMailer::getInstance();
      $oMail->setFrom(FlexiConfig::$sSupportEmail);
      $oMail->mail($sSubject, $sMessage, $oUser->Attributes->email);
      $this->oView->addVar("status", 2);
      return true;
    } else {
      $this->oView->addVar("status", 3);
      return true;
    }
  }


  public function methodLogin() {
    $sUserId      = $this->getPost("txtLogin");
    $sPassword    = $this->getPost("txtPassword");
    $sRedirect    = $this->getRequest("refurl");
    $iStandalone  = $this->getRequest("standalone", 0);
    $sRemember    = $this->getRequest("rememberme", false);
    $sContext    = $this->getRequest("context","");
    
    if(FlexiConfig::$sFramework == "modx") {
      $sLoginMode = $this->getRequest("webloginmode");
      if (!empty($sLoginMode)) {
        //logout of modx core...
        //so we redirect to main
        $this->setViewName("returnhome");
        return false;
      }
    }
    //is logout
    if (FlexiConfig::$sFramework == "modx2") {
      if ($this->getRequest("service") == "logout") {
        $this->setViewName("returnhome");
        return false;
      }
    }
    //FlexiLogger::info(__METHOD__, "Attemptng to login: " . $sUserId . ", pwd: " . $sPassword);
    $sRedirect = empty($sRedirect) ? "" : FlexiCryptUtil::b64Decrypt($sRedirect);
    if (FlexiConfig::$sFramework == "modx2") {
      $sRedirect = str_replace(array('?service=logout','&service=logout','&amp;service=logout'),'',$sRedirect);
    }
    
    $aOption = array("url" => $sRedirect);
    $aOption["standalone"] = $iStandalone == 1 ? true: false;
    $aOption["rememberme"] = empty($sRemember) ? false: true;
    //die("url: " . $sRedirect);
    $oLogin = FlexiConfig::getLoginHandler();

    if (! $oLogin->existsUser($sUserId)) {
      $sMessage = flexiT("Login fail","first");
      $this->addMessage($sMessage, "error");
      $aOption["error"] = $sMessage;
      FlexiLogger::error(__METHOD__, $sMessage);
      $sURL = FlexiConfig::getLoginHandler()->getLoginURL($sUserId, $sPassword, $aOption);
      //return $this->redirectURL(FlexiPlatformHandler::getReferrerURL());
      return $this->redirectURL($sURL);
    }

    //FlexiLogger::error(__METHOD__, "b4");
    if (FlexiConfig::$bRequireEmailVerification && ! $oLogin->getIsVerified($sUserId)) {
      $sMessage = flexiT("Sorry, please verify your email first","first");
      FlexiLogger::error(__METHOD__, $sMessage);
      $this->addMessage($sMessage, "error");

      $this->addMessage("<a href='" . $this->url(array("txtLogin" => $sUserId), "resendVerify") . "'>Click here to resend your verification</a>", "error");
      $aOption["error"] = 1;
      $sURL = FlexiConfig::getLoginHandler()->getLoginURL($sUserId, $sPassword, $aOption);
      //return $this->redirectURL(FlexiPlatformHandler::getReferrerURL());
      return $this->redirectURL($sURL);
    }
    //FlexiLogger::error(__METHOD__, "after");
    $bResult = $oLogin->doLogin($sUserId, $sPassword, $aOption);
    
    if ($bResult) {
      FlexiLogger::error(__METHOD__, "login ok");
      $this->oView->addVar("url", $sRedirect);
      return true;
    } else {
      $sMessage = flexiT("Login fail","first");
      FlexiLogger::error(__METHOD__, $sMessage);
      $this->addMessage($sMessage, "error");
      $aOption["error"] = $sMessage;

      //die("redirect: " . $sRedirect);
      $sURL = FlexiConfig::getLoginHandler()->getLoginURL($sUserId, $sPassword, $aOption);
      //return $this->redirectURL(FlexiPlatformHandler::getReferrerURL());
      return $this->redirectURL($sURL);
    }

  }

  public function methodVerify() {

    $iUserId = $this->getRequest("id");
    $sCode = $this->getRequest("r");
    
    $bVerify = FlexiPlatformHandler::getPlatformHandler()->verifyUser($iUserId, $sCode);

    $this->oView->addVar("verified", $bVerify);

    return true;
  }

  public function methodDenied() {
    FlexiLogger::debug(__METHOD__, "ok");
    return true;
  }

}


