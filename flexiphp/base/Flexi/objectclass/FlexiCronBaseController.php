<?php

class FlexiCronBaseController extends FlexiBaseController {
  public $sInformEmail = "";
  public $iStartTime = 0;
  public $sLogType = "debug";

  public function onInit() {
    //cron doesnt have layout or view
    $this->disableView();
    $this->iStartTime = time();
  }

  public function getElapsedTime() {
    return time() - $this->iStartTime;
  }

  public function getPeakMemory() {
    return memory_get_peak_usage(true)/1024/1024;
  }

  public function methodDefault() {
    return true;
  }
  
  public function showStatus($sMethod, $sMsg) {
    $sLog = "(" . $this->getPeakMemory() . "MB:" .
     "". $this->getElapsedTime() . "s)" . (empty($sMsg) ? "": ":" . $sMsg);
    $this->log($sMethod, $sLog, $this->sLogType);
  }

  public function log($sMethod, $sMsg, $sType) {
    $sLog = $sMethod . ":" . gmdate("Ymd|H:i:s") . ":" . $sMsg;
    FlexiLogger::$sType($sMethod, $sMsg);
    echo $sLog . "<br/>\n";
  }

  public function informException(Exception $e) {
    $sMsg = "msg:\n" . $e->getMessage();
    $sMsg .= "\nStack:\n" . $e->getTraceAsString();
    return $this->informError($sMsg);
  }

  public function informError($sMsg) {
    //echo, write to file, and mail?
    $aLine = explode("\n", $sMsg);
    $sResult = "";
    foreach($aLine as $sLine) {
      $sResult .= "[" . get_class($this) . "]:" . $sLine . "<br/>\n";
    }
    
    echo $sResult;
    FlexiLogger::error(__METHOD__, $sResult);
    if (!empty($this->sInformEmail)) {
      $sHeader = "From: " . FlexiConfig::$sSupportEmail . "\n".
        "Reply-to: " . FlexiConfig::$sSupportEmail;
      $oMail = FlexiMailer::getInstance();
      $oMail->mail(FlexiConfig::$sBaseURLDir . ":Error", $sResult, $this->sInformEmail, "text");
    }
  }
  
}