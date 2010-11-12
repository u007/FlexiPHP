<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class FlexiTestController extends FlexiBaseController {

  public function methodDefault() {
    return $this->runControl("testremote");
    return true;
  }

  public function methodTestremote() {
    FlexiLogger::debug(__METHOD__, "starting");

    $sURL = FlexiConfig::$sBaseURL . "remote.php";
    $oData = array(
      "testme" => "xyz"
    );
    $oClient = new FlexiRemoteJSONClient();
    $oClient->setContent($oData);
    $bResult = $oClient->callRemote($sURL, "xxx", "test");

    FlexiLogger::debug(__METHOD__, "Result status: " . serialize($bResult));

    var_dump($oClient->getResultReturned());
    
    return true;
  }

}