<?php

/**
 * Description of FlexiBaseRemoteController
 *
 * @author james
 */
class FlexiBaseRemoteController extends FlexiBaseController {
  protected $sToken = "";

  public function methodDefault() {
    return true;
  }

  public function serviceLogin($aData) {
    $bResult = FlexiConfig::getLoginHandler()->doLogin($aData->username, $aData->password);
    
    if ($bResult) {
      $oModel = FlexiModelUtil::getInstance()->getRedBeanModel("service_token");
      
      $oModel->userid = FlexiConfig::getLoginHandler()->getLoggedInUserId();
      $oModel->token = FlexiStringUtil::createRandomPassword(20);

      FlexiModelUtil::getInstance()->storeRedBean($oModel);

      return array("login_status" => true, "token" => $oModel->token);
    }

    FlexiLogger::error(__METHOD__, "Invalid login: user: " . $aData->username . "," . $aData->password);

    return array("login_status" => false, "token" => "");
  }

  /**
   * Reset token
   */
  public function unsetToken() {
    $sToken = $this->sToken;

    FlexiModelUtil::getInstance()->getRedBeanExecute("delete from service_token where token=:token",
            array(":token" => $sToken));
  }

  public function checkPermission($asMethod) {
    //if is login, ignore, else check permission
    if (strtolower($asMethod) == "login") {
      return true;
    }

    return parent::checkPermission($asMethod);
  }

  
}
?>
