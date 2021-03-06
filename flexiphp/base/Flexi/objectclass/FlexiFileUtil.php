<?php

/**
 * Description of FlexiFileUtil
 *
 * @author james
 */
class FlexiFileUtil {
  public static $aLocks = array();

  public static function getMediaURL($asPath, $sRole="", $sName="", $aParam = array()) {
    if (empty($asPath)) return "";
    if ($asPath[0] != "/" && substr($asPath,1,2) != ":\\" && substr($asPath,1,2) != ":/") {
      $sTempPath = FlexiConfig::$sRootDir . "/" . $asPath;
    } else {
      $sTempPath = $asPath;
    }
    $sTempPath = str_replace("//", "/", $sTempPath);
    $sPath = realpath($sTempPath);
    if ($sPath===false) throw new Exception("File missing: " . $sTempPath);
    //echo "path: " . $sPath;
    //FlexiLogger::info(__METHOD__, "isadmin: " . (FlexiConfig::$bIsAdminPath? "yes": "no") . ": " . $sPath);
    $oControl = FlexiController::getCurrentController();
    $sQuery = "r=" . $sRole . "&path=" . $sPath . "&name=" . $sName . 
      (count($aParam) < 1? "": "&" . http_build_query($aParam));
    
    $sFilePath=FlexiCryptUtil::b64URLEncrypt($sQuery);
    
    $sURL = $oControl->url(array("p"=>$sFilePath), "GetFile", "media", true);
    return $sURL;
  }
  
  public static function getFullPathFrom($asPath, $sRelativePath="") {
    $sBasePath = empty($sRelativePath) ? self::getBasePath(): $sRelativePath;
    return $sBasePath . "/" . $asPath;
  }
  
  public static function getRelativePathFrom($asPath, $asBasePath="") {
    $sPath = realpath($asPath);
    $sPath = str_replace("\\", "/", $sPath);
    if (empty($asBasePath)) {
      $sBasePath = self::getBasePath() . "/";
    } else {
      $sBasePath = substr($asBasePath, -1) == "/" ? $asBasePath: $asBasePath . "/";
    }
    //fix issue with mispath \\ with /
    $sBasePath = str_replace("\\", "/", $sBasePath);
    //echo "real path: " . $sPath . ", base: " . $sBasePath;
    return str_replace($sBasePath, "", $sPath);
  }
  //deprecated soon
  public static function getRelativePathFromBase($asPath) {
    $sPath = realpath($asPath);
    $sBasePath = self::getBasePath();
    //FlexiLogger::info(__METHOD__, "base: " . $sBasePath . "|" . $sPath);
    //FlexiLogger::info(__METHOD__, "result: " . str_replace($sBasePath . "/", "", $sPath));
    return str_replace($sBasePath . "/", "", $sPath);
  }

  public static function getBasePath() {
    if (FlexiConfig::$sFramework=="modx2" || FlexiConfig::$sFramework=="modx") {
      return substr(MODX_BASE_PATH,0, strlen(MODX_BASE_PATH)-1); //REMOVE TRAILING /
    }
    return realpath(FlexiConfig::$sRootDir . "/" . (FlexiConfig::$bIsAdminPath ? "/.." : ""));
  }

  public static function getBaseUploadPath() {
    return self::getBasePath() . "/assets";
  }

  public static function getUploadPath($sSubPath="") {
    $sPath = self::getBaseUploadPath() . (empty($sSubPath) ? "" : "/" . $sSubPath);
    return $sPath;
  }
  public static function getFullUploadPath($sSubPath="") {
    $sPath = self::getUploadPath($sSubPath);
    if (! is_dir($sPath)) {
      if (! @mkdir($sPath, 0777, true)) {
        throw new Exception("Unable to create: " . $sPath);
      }
    }
    return $sPath;
  }
  public static function getIsUploaded($sFormName) {
    if (!isset($_FILES[$sFormName])) return false;
    $sTempFile = $_FILES[$sFormName]['tmp_name'];
    if (empty($sTempFile)) { return false; }
    //check for cracking
    if (!is_uploaded_file($sTempFile)) { return false; }
    return true;
  }
  public static function getUploadedFileSize($sFormName) {
    if (! self::getIsUploaded($sFormName)) { return -1; }
    $sTempFile = $_FILES[$sFormName]['tmp_name'];
    return filesize($sTempFile);
  }
  public static function getUploadedFileExtension($sFormName) {
    if (! self::getIsUploaded($sFormName)) { return -1; }
    $aInfo = pathinfo($_FILES[$sFormName]["name"]);
    return $aInfo["extension"];
  }

  /**
   * Validate file upload
   * @param String $sFormName
   * @param int $iMaxUploadSize in bytes
   * @param String $sAllowedExtension, comma separated supported file
   * @return <type>
   */
  public static function validateFileUpload($sFormName, $aiMaxUploadSize=null, $sAllowedExtension=null) {
    $iMaxUploadSize = $aiMaxUploadSize;
    if (is_null($iMaxUploadSize) && (FlexiConfig::$sFramework=="modx" || FlexiConfig::$sFramework=="modx2")) {
      global $modx;
      $iMaxUploadSize = $modx->config["upload_maxsize"];
    }
    $bUploadErr = false;
    $iSize = self::getUploadedFileSize($sFormName);
    if (!empty($iMaxUploadSize) && $iSize > $iMaxUploadSize) {
      $sNotice = flexiT("File Upload exceed permitted size", "first") . ": " . $iSize . " / " . $iMaxUploadSize;
      return array("status" => false, "msg" => $sNotice);
    }
    $sExtension = strtolower(trim(FlexiFileUtil::getUploadedFileExtension($sFormName)));
    if (!empty($sAllowedExtension)) {
      $sAllowed = "";
      switch($sAllowedExtension) {
        case "image":
        case "media":
          $sAllowed = "jpg,jpeg,png,gif";
        case "video":
        case "media":
          $sAllowed .= "mp4,wma,avi,mpeg,mov,flv";
        case "sound":
        case "media":
          $sAllowed .= "mp3,wav";
      }
      $sAllowed = empty($sAllowed) ? $sAllowedExtension: $sAllowed;
      $aAllowed = explode(",", $sAllowed);
      if (! in_array($sExtension, $aAllowed)) {
        $sNotice = flexiT("Only of type", "first") . ": jpeg or png " .
                  flexiT("are permitted") . ", " . flexiT("you have uploaded") . ": " . $sExtension;
        return array("status" => false, "msg" => $sNotice);
      } //if not in allowed extension
    } //allowed extension is set
    return array("status" => true, "msg" => "");
  }
  /**
   * Save upload file
   * @param String $sFormName
   * @param String $sMovePath: path to move, null for not moving,
   * @return false / array("status:bool", "path:String", "size:number", "type(extension):String")
   */
  public static function _doUploadFile($sFormName, $asMovePath="") {
    if (!self::getIsUploaded($sFormName)) { return array("status" => false); }
    //FlexiLogger::error(__METHOD__, $sFormName . ", path: " . $asMovePath);
    $sTempFile = $_FILES[$sFormName]['tmp_name'];
    $sMovePath = $asMovePath;
    $aInfo = pathinfo($_FILES[$sFormName]["name"]);
    $aReturn = array("status" => false, "path" => $sTempFile, "size" => filesize($sTempFile), "type" => $aInfo["extension"]);
    if (!empty($sMovePath) && $sTempFile != $sMovePath) {
      $aReturn["path"] = $sMovePath;
      if (is_file($sMovePath)) { unlink($sMovePath); }
      if(@move_uploaded_file($sTempFile, $sMovePath)) {
        FlexiLogger::info(__METHOD__, "Moved file from: " . $sTempFile . " to " . $sMovePath);
        $aReturn["status"] = true;
      } else {
        throw new FlexiException("Error moving file from: " . $sTempFile . " to " . $sMovePath, ERROR_FILE_MOVE);
      }
    }
    return $aReturn;
  }

  public static function storeUploadFile($oFile, $sMovePath="", $sPrefix="", $sSuffix="", $iRandomNameSize=10) {
    $sTempFile = $oFile["tmp_name"];
    if (empty($oFile["tmp_name"])) return false;
    $aInfo = pathinfo($oFile["name"]);
    if (is_string($oFile)) throw new Exception("Invalid file: " . $oFile);
    $sNewFile = $sMovePath . "/" . $sPrefix .
      ($iRandomNameSize > 0 ? FlexiStringUtil::createRandomPassword($iRandomNameSize): "") . $sSuffix .
      "." . $aInfo["extension"];
    return self::_storeUploadFile($oFile, $sNewFile);
  }

  public static function _storeUploadFile($oFile, $asMovePath) {
    $sTempFile = $oFile["tmp_name"];
    if (empty($asMovePath)) throw new Exception("New file path not specified");
    if (empty($sTempFile)) return false;
    if (!is_uploaded_file($sTempFile)) { throw new Exception("Crack attempt!"); }
    
    //FlexiLogger::error(__METHOD__, $sFormName . ", path: " . $asMovePath);
    $sMovePath = $asMovePath;
    $aInfo = pathinfo($oFile["name"]);
    $aReturn = array("status" => false, "path" => $sTempFile, "size" => filesize($sTempFile), "type" => $aInfo["extension"]);
    if (!empty($sMovePath) && $sTempFile != $sMovePath) {
      $aReturn["path"] = $sMovePath;
      if (is_file($sMovePath)) { unlink($sMovePath); }
      if(move_uploaded_file($sTempFile, $sMovePath)) {
        FlexiLogger::info(__METHOD__, "Moved file from: " . $sTempFile . " to " . $sMovePath);
        $aReturn["status"] = true;
        $aReturn["path"] = $sMovePath;
      } else {
        throw new FlexiException("Error moving file from: " . $sTempFile . " to " . $sMovePath, ERROR_FILE_MOVE);
      }
    }
    return $aReturn;
  }
  
  public static function getUploadedFile($sFormName) {
    if (!self::getIsUploaded($sFormName)) { throw new Exception("No uploaded: " . $sFormName); }
    return $_FILES[$sFormName]['tmp_name'];
  }
  /**
   * Save upload file
   * @param String $sFormName
   * @param String $sMovePath: path to move, null for not moving,
   * @param String $sPrefix : prefix of name
   * @param String $sSuffix: suffix of name
   * @param int $iRandomNameSize: length of random name to generate, 0 for using only $sPrefix+sSuffix as file name
   * @return false / array("status:bool", "path:String", "size:number", "type(extension):String")
   */
  public static function doUploadFile($sFormName, $sMovePath="", $sPrefix="", $sSuffix="", $iRandomNameSize=10) {
    if (!self::getIsUploaded($sFormName)) { return array("status" => false); }
    $sTempFile = $_FILES[$sFormName]['tmp_name'];
    
    $aInfo = pathinfo($_FILES[$sFormName]["name"]);
    $aReturn = array("status" => false, "path" => $sTempFile, "size" => filesize($sTempFile), "type" => $aInfo["extension"]);
    $aReturn["path"] = $sMovePath . "/" . $sPrefix .
      ($iRandomNameSize > 0 ? FlexiStringUtil::createRandomPassword($iRandomNameSize): "") . $sSuffix .
      "." . $aInfo["extension"];
    return self::_doUploadFile($sFormName, $aReturn["path"]);
  }

  public static function getDirectoryList($sPath, $aExclude=array(), $aInclude=array()) {
    $bDebug = false;
    $aResult = array();
    if ($handle = opendir($sPath)) {
      while (false !== ($file = readdir($handle))) {
        $sFilePath = $sPath . $file;
        $bFound = false;
        if ($bDebug) echo "checking: " . $sFilePath . "\r\n<br/>";
        foreach($aExclude as $sExclude) {
          if ($bDebug) echo "exclude: " . $sExclude . "\r\n<br/>";
          list($sExcludeName, $sExcludeName2) = explode("/", $sExclude);
          if ($bDebug) echo "checking: " . $file . ", exclude: " . $sExcludeName . "\r\n<br/>";
          if ($file == $sExcludeName && empty($sExcludeName2)) {
            $bFound = true;
            break;
          }
        } //foreach exclude

        if ($bFound) {
          foreach($aInclude as $sInclude) {
            if ($bDebug) echo "include: " . $sInclude . "\r\n<br/>";
            list($sIncludeName, $sIncludeName2) = explode("/", $sInclude);
            if ($bDebug) echo "checking: " . $file . ", include: " . $sIncludeName . "\r\n<br/>";
            if ($file == $sIncludeName && empty($sIncludeName2)) {
              $bFound = false;
              break;
            }
          } //foreach include
        }
        if (! $bFound) { $aResult[] = $file; }
      }//each dir
      
      closedir($handle);
      //die();
    }
    return $aResult;
  }
	
  public static function recursiveDeleteChild($path) {
    if (is_dir($path)) {
      $aList = self::getDirectoryList($path);
      foreach($aList as $sPath) {
        if ($sPath != "." && $sPath != "..") {
          self::recursiveDelete($path . "/" . $sPath);
        }
      }
    }
    return true;
  }
  
  public static function recursiveDelete($path)
  {
    if (is_file($path)) {
      return @unlink($path);
    }
    
    $aList = self::getDirectoryList($path);
    foreach($aList as $sPath) {
      if ($sPath != "." && $sPath != "..") {
        self::recursiveDelete($path . "/" . $sPath);
      }
    }
    return @rmdir($path);
  }

  public static function doLockFile($sPath) {
    $fp = fopen($sPath, "w");
    if (flock($fp, LOCK_EX)) { // do an exclusive lock
        ftruncate($fp, 0); // truncate file
        fwrite($fp, "Lock");
        $bResult = true;

        self::$aLocks[$sPath] = $fp;
        FlexiLogger::error(__METHOD__, "Locked file: " . $sPath);
        //flock($fp, LOCK_UN); // release the lock
    } else {
      FlexiLogger::error(__METHOD__, "Unable to lock file: " . $sPath);
      $bResult = false;
    }

    return $bResult;
  }

  public static function doUnlockFile($sPath) {
    if (!isset(self::$aLocks[$sPath])) {
      throw new FlexiException("No such lock for path: " . $sPath, ERROR_IO_LOCK);
    }

    flock(self::$aLocks[$sPath], LOCK_UN);
    fclose(self::$aLocks[$sPath]);
    unset(self::$aLocks[$sPath]);

    FlexiLogger::debug(__METHOD__, "Unlocked: " . $sPath);
    return true;
  }

  public static function doUnlockAll() {
    foreach(self::$aLocks as $fp) {
      flock($fp, LOCK_UN);
      fclose($fp);
    }
    self::$aLocks = array();
  }

  /**
   * Get tail of a file
   * @param String $sFile
   * @param int $iLines
   * @refer http://tekkie.flashbit.net/php/tail-functionality-in-php
   * @return array of lines
   */
  public static function getTail($sFile, $iLines = 20) {
    //global $fsize;
    $handle = fopen($sFile, "r");
    $linecounter = $iLines;
    $pos = -2;
    $beginning = false;
    $text = array();
    while ($linecounter > 0) {
      $t = " ";
      while ($t != "\n") {
        if(fseek($handle, $pos, SEEK_END) == -1) {
            $beginning = true;
            break;
        }
        $t = fgetc($handle);
        $pos --;
      }
      $linecounter --;
      if ($beginning) {
        rewind($handle);
      }
      $text[$iLines-$linecounter-1] = fgets($handle);
      if ($beginning) break;
    }
    fclose ($handle);
    return array_reverse($text);
  }

}
