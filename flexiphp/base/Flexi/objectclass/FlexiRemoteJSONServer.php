<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FlexiJSONRemoteServer
 *
 * @author james
 */
class FlexiRemoteJSONServer extends FlexiRemoteServer {

  public function init() {
    FlexiController::getInstance()->setHeader("Content-type", "text/x-json");
  }

  /**
   * Called to return request object
   * @return request object
   */
  public function _run() {
    $sRaw = $this->getDecodedData();
    //FlexiLogger::error(__METHOD__, "Running: " . serialize($sRaw));
    FlexiLogger::debug(__METHOD__, "Running2: " . print_r($sRaw, true));
    $oRequest = json_decode($sRaw);
    //FlexiLogger::error(__METHOD__, "JSON: " . serialize($oRequest));
    return $oRequest;
  }

  /**
   * Encode data before output
   * @param Mixed $mData
   * @return String
   */
  public function _returnResult($mData) {
    return json_encode($mData);
  }

  public function getInstance() {
    if (self::$oInstance == null) {
      self::$oInstance = new FlexiRemoteJSONServer();
    }

    return self::$oInstance;
  }
  
}