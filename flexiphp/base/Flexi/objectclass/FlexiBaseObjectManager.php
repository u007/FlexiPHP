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
    file_put_contents($sFile, $sMsg . ".\n", FILE_APPEND);
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

    $sWriteData = serialize($data);
    $sWriteData = str_replace("\"", "\\\"", $sWriteData);
    $sWriteData = "<" . "?\n" . 
      "$" . "_tmp=\"" . $sWriteData . "\";\n" . 
      "";
      
    if (!file_put_contents($sWriteFile, $sWriteData)) {
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

  /**
   * Load a saved file
   *  old version is directly a file, but new version is a php code,
   *  which content $_tmp='[serialized content]';
   * @param <type> $sFile
   * @return <type>
   */
  public function loadFile($sFile) {
    if (!file_exists($sFile)) {
      throw new Exception("No such file: " . $sFile);
    }
    $sContent = trim(file_get_contents($sFile));
    if (empty($sContent)) throw new Exception("File is empty");
    if (substr($sContent,0,2)=="<" . "?") {
      //is new version
      require($sFile);
      if (empty($_tmp)) throw new Exception("Tmp var is empty");
      $oObject = unserialize($_tmp);
      if ($oObject===false) throw new Exception("Unable to unserialize content: " . $_tmp);
      return $oObject;
    } else {
      //old version is plain file
      return unserialize($sContent);
    }
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