<?php

/**
 * Base class for remote call of FlexiBaseRemote
 * @author james
 */
abstract class FlexiRemoteServer {
    //put your code here
  protected $sCallType = "";
  protected static $oInstance = null;

  protected $sServiceModule = "";
  protected $sServiceMethod = "";
  
  protected $oRequest = null;
  protected $oResult = null;
  protected $oRequestData = null;

  public $sServiceToken = "";
  
  function __construct() {
    FlexiLogger::debug(__METHOD__, "Initialising");
    $this->init();
  }

  function init() {
    
  }

  public function returnResult($mData) {
    FlexiLogger::info(__METHOD__, "Returning: " . serialize($mData));
    $result = $this->_returnResult($mData);

    if (empty($result)) { return $result; }
    
    return FlexiCryptUtil::b64Encrypt($result);
  }

  public function getDecodedData() {
    $sRaw = FlexiController::getInstance()->getRawPostContent();
    
    if (empty($sRaw)) { return ""; }
    return FlexiCryptUtil::b64Decrypt($sRaw);
  }

  public function run() {

    $this->oRequest = $this->_run();
    $oRequest = & $this->oRequest;
    
    $this->sServiceModule = $oRequest->module;
    $this->sServiceMethod = $oRequest->method;
    $this->sServiceToken  = $oRequest->token;

    FlexiLogger::info(__METHOD__, "Running service: " . $this->sServiceModule . "::" . $this->sServiceMethod);
    
    FlexiLogger::debug(__METHOD__, "Token: " . $this->sServiceToken);
    FlexiConfig::getLoginHandler()->doLoginByToken($this->sServiceToken);

    FlexiLogger::debug(__METHOD__, "Logged in as: " . FlexiConfig::getLoginHandler()->getLoggedInUserId());
    $this->mRequestData   = array();
    
    if (isset($oRequest->data)) {
      $this->mRequestData = $oRequest->data;
    }

    
    $mResult = FlexiController::getInstance()->runService($this->sServiceModule, $this->sServiceMethod, $this->mRequestData);
    unset($mResult["control"]);
    FlexiLogger::debug(__METHOD__, "Done service: " . $this->sServiceModule . "::" . $this->sServiceMethod);

    echo $this->returnResult($mResult);
  }

  abstract public function _returnResult($mData);

  abstract public function _run();
  
  public static function getRemoteServer() {
    $sCallType = FlexiController::getInstance()->getRequest("_type", "json");
    $sCallType = empty($sCallType) ? "json" : $sCallType;
    
    switch($sCallType) {
      case "json":
        return FlexiRemoteJSONServer::getInstance();
        break;

      default:
        throw new FlexiException("Unknown Remote call-type: " . $sCallType, ERROR_UNKNOWNTYPE);
    }

    return null;
  }
  
}