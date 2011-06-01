<?php

class FlexiLogManager {

  public function __construct($aParam=null) {
    
  }
  
  public function doLog($sMsg, $sType="info") {
    if (empty($this->sLogPath)) throw new Exception("Log path not set");

    $sFile = $this->sLogPath . "/" . $sType . ".log";
    file_put_contents($sFile, $sMsg . "\n", FILE_APPEND);
  }

  public function setLogPath($sPath) {
    if (!is_dir($sPath)) {
      //try make dir
      if (!mkdir($sPath, 0777, true)) {
        throw new FlexiException("Path cannot be created: " . $sPath, ERROR_CREATEERROR);
      }
    }
    $this->sLogPath = realpath($sPath);
  }
}