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
class FlexiJSONRemoteServer extends FlexiRemoteServer {

  public function run() {
    $sJSON = FlexiController::getInstance()->getRawPostContent();
    $oRequest = json_decode($sJSON);
    
    var_dump($oRequest);
  }

  public function getInstance() {
    if (self::$oInstance == null) {
      self::$oInstance = new FlexiJSONRemoteServer();
    }

    return self::$oInstance;
  }
  
}