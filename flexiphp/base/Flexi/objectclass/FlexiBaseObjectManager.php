<?php

class FlexiBaseObjectManager {
  public $sPath = "";
  public $sLogPath = "log";
  
  public function __construct($aParam=array()) {
    
  }

  public function setPath($sPath) {
    $bDebug = false;
    if ($bDebug) echo __METHOD__ . ": path: " . $sPath . "<br/>\n";
    if (!is_dir($sPath)) {
      //try make dir
      if (!mkdir($sPath, 0777, true)) {
        throw new FlexiException("Path cannot be created: " . $sPath, ERROR_CREATEERROR);
      }
    }
    $this->sPath = realpath($sPath);
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

  public function delete($sFile, $sPrefix="") {
    $sPath = $this->sPath;
    $sDelFile = $sPath . "/" . $sPrefix .$sFile;
    return $this->deleteFile($sDelFile);
  }

  public function deleteFile($sFile) {
    $sFile = realpath($sFile);
    if (!file_exists($sFile)) {
      throw new Exception("No such file: " . $sFile);
    }
    return unlink($sFile);
  }

  public function store($sFile, $data, $sPrefix="") {
    $sPath = $this->sPath;
    $sWriteFile = $sPath . "/" . $sPrefix . $sFile;
    if (!file_put_contents($sWriteFile, serialize($data))) {
      throw new FlexiException("Unable to write: " . $sWriteFile, ERROR_WRITE);
    }
    return $sWriteFile;
  }

  public function exists($sFile, $sPrefix = "") {
    $sPath = $this->sPath;
    $sReadFile = realpath($sPath . "/" . $sPrefix . $sFile);
    return file_exists($sReadFile);
  }

  public function load($sFile, $sPrefix = "") {
    $sPath = $this->sPath;
    $sReadFile = $sPath . "/" . $sFile;
    return $this->loadFile($sReadFile);
  }

  public function loadFile($sFile) {
    if (!file_exists($sFile)) {
      throw new Exception("No such file: " . $sFile);
    }
    return unserialize(file_get_contents($sFile));
  }
  
  
  public function fetchAll($sPrefix = "", $bCached=true) {
    $aList = $this->getList($sPrefix, $bCached);
    $aResult = array();
    foreach($aList as $sFile) {
      $aResult[] = $this->loadFile($sFile);
    }
    return $aResult;
  }
  
  public function getList($sPrefix="", $bCached=true) {
    $aList = flexiDirChildList($this->sPath, $bCached, $sPrefix);
    $aResult = array(); //cleanup list
    foreach($aList as $sPath) {
      if (strpos($sPath, "/.") ===false) {
        $aResult[] = $sPath;
      }
    }
    return $aResult;
  }


}