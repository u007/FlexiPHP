<?php
/* 
 * Form util to perform form related action
 * @author James @ mercstudio
 */
class FlexiFormUtil {
	
	
	public static function getMaxUploadFileSize($maxuploadsize=null) {
		$iMaxUpload = (int)$maxuploadsize;
    if (empty($iMaxUpload)) {
      if (FlexiConfig::$sFramework=="modx") {
        global $modx;
        $iMaxUpload = $modx->config["upload_maxsize"];
      } else if (FlexiConfig::$sFramework=="modx2") {
        global $modx;
        $iMaxUpload = $modx->context->getOption('upload_maxsize',false);
      } else {
				$iMaxUpload = 1024*1024*10;//todo: use flexiconfig variable
			}
    }
		return $iMaxUpload;
	}
	
	public static function checkUploadImage($sFieldName, $maxuploadsize=null, $aExtension=array()) {
		$aExtend = count($aExtension) <= 0? array("jpg","jpeg","pjpeg","pjpg","png"): $aExtension;
		return self::checkUpload($sFieldName, $aExtend, $maxuploadsize);
	}
	/**
	 * @param string $sFieldName
	 * @param int $maxuploadsize
	 * @return boolean true: uploaded, false: not uploaded
	 * 	May throw Exception on error
	*/
	public static function checkUpload($sFieldName, $aExtension=array(), $maxuploadsize=null) {
		$iMaxUpload = self::getMaxUploadFileSize($maxuploadsize);
		if (FlexiFileUtil::getIsUploaded($sFieldName)) {
      $iSize = FlexiFileUtil::getUploadedFileSize($sFieldName);
      if ($iSize > $iMaxUpload) {
        $sNotice = flexiT("File Upload exceed permitted size", "first") . ": " . $iSize . " / " . $iMaxUpload;
        throw new FlexiException($sNotice, ERROR_FILESIZE);
      }
      $sExtension = strtolower(trim(FlexiFileUtil::getUploadedFileExtension($sFieldName)));
      if (!in_array($sExtension, $aExtension)) {
        $sNotice = flexiT("File type not permitted", "first") . ", " 
                  . flexiT("you have uploaded") . ": " . $sExtension;
        throw new FlexiException($sNotice, ERROR_FILETYPE);
      }
			return true;
		}
		return false; //no upload file
	}
	
  public static function processUploadPhoto($sFieldName, $sSavePath, $sSaveName, $iMaxWidth=null, $iMaxHeight=null, $aiMaxUpload=null, $bReplace=true, $sOldPath=null) {
    $aExtension = array("jpg","jpeg","pjpeg","pjpg","png");
    $result = self::processUpload($sFieldName, $sSavePath, $aExtension, $sSaveName, $aiMaxUpload, $bReplace, $sOldPath);
    if ($result===false) { return false; }

    if ($result["status"]) {
      //resize image based on max width, height
      FlexiImageUtil::imageResize($iMaxWidth, $iMaxHeight, $result["path"]);
      
    } else {
      $sNotice = flexiT("Save upload file failed",true);
      throw new FlexiException($sNotice, ERROR_UNKNOWN);
    }

    return $result;
  }
  
  public static function processUpload($sFieldName, $sSavePath, $aExtension, $sSaveName, $aiMaxUpload=null, $bReplace=true, $sOldPath=null) {
    $iMaxUpload = self::getMaxUploadFileSize($aiMaxUpload);
		
    if (FlexiFileUtil::getIsUploaded($sFieldName)) {
      $iSize = FlexiFileUtil::getUploadedFileSize($sFieldName);
      if ($iSize > $iMaxUpload) {
        $sNotice = flexiT("File Upload exceed permitted size", "first") . ": " . $iSize . " / " . $iMaxUpload;
        throw new FlexiException($sNotice, ERROR_FILESIZE);
      }
      $sExtension = strtolower(trim(FlexiFileUtil::getUploadedFileExtension($sFieldName)));
      if (!in_array($sExtension, $aExtension)) {
        $sNotice = flexiT("File type not permitted", "first") . ", " 
                  . flexiT("you have uploaded") . ": " . $sExtension;
        throw new FlexiException($sNotice, ERROR_FILETYPE);
      }
      
      //replace photo if already exists
      if ($bReplace && !empty($sOldPath)) {
        //fix windows path issue
        $sOldFile = substr($sOldPath,0,1) == "/" || substr($sOldPath,1,2) == ":\\" ? 
          $sOldPath : FlexiFileUtil::getBasePath() . "/" . $sOldPath;
        @unlink($sOldFile);
      }
      $aStatus = FlexiFileUtil::doUploadFile($sFieldName, $sSavePath, $sSaveName . ".");
      //
      return $aStatus;
    } //is upload
    
    return false;
  }

  public static function tinyMCERemoveControl($aForm) {
    $sResult = "";
    //$sResult .= "//Removing controls: " . serialize($aForm) . "\r\n";
    foreach($aForm as $sKey => $aValue) {
      //$sResult .= "//Removing control: " . $sKey . ", type: " . $aValue["#type"] . "\r\n";
      if ($sKey[0] != "#" && ($aValue["#type"] == "html" || $aValue["#type"] == "html.raw")) {
        $sResult .= "tinyMCE.execCommand('mceRemoveControl',false,'" . $aValue["#id"] . "');\n";
      }
    }
    return $sResult;
  }

  public static function tinyMCEInitControl($aForm) {
    $sResult = "";
    foreach($aForm as $sKey => $aValue) {
      if ($sKey[0] != "#" && ($aValue["#type"] == "html" || $aValue["#type"] == "html.raw")) {
        $sResult .= "tinyMCE.execCommand('mceAddControl',false,'" . $aValue["#id"] . "');\n";
      }
    }
    $sResult .= "tinyMCE.execCommand('mceRepaint');\n";
    return $sResult;
  }

  public static function mergeFormWithDataArray(& $aForm, $aData = null, $sExclude = null) {
		$aNotice = FlexiConfig::$aFormMessage;
    $aExclude = empty($sExclude) ? array(): explode(",", $sExclude);
		foreach($aForm as $sField => & $mValue)
		{
			//is a form field, and is not already set value
			if ($sKey[0] != "#" && ! isset($mValue["#value"]))
			{
        //FlexiLogger::info(__METHOD__, $sField . ", type: " . $mValue["#type"]);
				//form value 1st,
				if (array_key_exists($sField, $aData) && ! in_array($sField, $aExclude))
				{
          $sValue = $aData[$sField];
          $mValue["#value"] = is_array($sValue) ? implode(",", $sValue) : $sValue;
				} //if #dbfield
			}//if is field

			//notice msg
			if (isset($aNotice[$sKey]))
			{
				$mValue["#notice"] = $aNotice[$sKey];
			}
		}
  }

  public static function mergeFormWithModel(& $aForm, $aModel = null) {
		$aNotice = FlexiConfig::$aFormMessage;
		foreach($aForm as $sKey => & $mValue)
		{
			//is a form field, and is not already set value
			if ($sKey[0] != "#" && ! isset($mValue["#value"]))
			{
        //FlexiLogger::debug(__METHOD__, $sKey . ", type: " . $mValue["#type"]);
				//form value 1st,
				if (array_key_exists("#dbfield", $mValue))
				{
          $sField = $mValue["#dbfield"];
					if (array_key_exists($sField, $aModel))
					{
						$sValue = $aModel[$sField];
						if ($mValue["#type"] == "date" || $mValue["#type"] == "date.raw")
						{
							$sFormat = isset($mValue["#format"]) ? $mValue["#format"] : FlexiConfig::$sInputDateFormat;
							$sSep = "-";
							if (strpos($sFormat, "/") !== false) { $sSep = "/"; }
							else if (strpos($sFormat, ".") !== false) { $sSep = "."; }
							$sFuncName = str_replace("-", "", strtoupper($sFormat));
							$sFuncName = str_replace("/", "", $sFuncName);
							$sFuncName = str_replace(".", "", $sFuncName);
							$sFuncName = "get" . $sFuncName . "FromISODate";
							//echo "calling : " . $sFuncName;
							$sValue = FlexiStringUtil::$sFuncName($sValue, $sSep);
						}
            $mValue["#value"] = is_array($sValue) ? implode(",", $sValue) : $sValue;
					} //if model field exists
					
				} //if #dbfield
			}//if is field

			//notice msg
			if (isset($aNotice[$sKey]))
			{
				$mValue["#notice"] = $aNotice[$sKey];
			}
		}
  }

  public static function mergeFormWithData(& $aForm, $asValues=null) {
    //if no value specified or null, will get all get+post+request (custom)
		$aValues = is_null($asValues) ? FlexiController::getInstance()->getAllRequest() : $asValues;
		$aNotice = FlexiConfig::$aFormMessage;

		foreach($aForm as $sKey => & $mValue)
		{
			//is a form field, and is not already set value
			if ($sKey[0] != "#" && ! isset($mValue["#value"]))
			{
        //FlexiLogger::info(__METHOD__, $sKey . ", type: " . $mValue["#type"]);
				//form value 1st,
				if (array_key_exists($sKey, $aValues))
				{
					$mValue["#value"] = is_array($aValues[$sKey]) ? implode(",", $aValues[$sKey]) : $aValues[$sKey];
				}
				//then dbfield value
				else if (isset($mValue["#dbfield"]))
				{
					$sField = $mValue["#dbfield"];

					if (isset($aValues[$sField]))
					{
						$sValue = $aValues[$sField];
            
						if ($mValue["#type"] == "date" || $mValue["#type"] == "date.raw")
						{
							$sFormat = isset($mValue["#format"]) ? $mValue["#format"] : FlexiConfig::$sInputDateFormat;

							$sSep = "-";
							if (strpos($sFormat, "/") !== false) { $sSep = "/"; }
							else if (strpos($sFormat, ".") !== false) { $sSep = "."; }

							$sFuncName = str_replace("-", "", strtoupper($sFormat));
							$sFuncName = str_replace("/", "", $sFuncName);
							$sFuncName = str_replace(".", "", $sFuncName);

							$sFuncName = "get" . $sFuncName . "FromISODate";
							//echo "calling : " . $sFuncName;
							$sValue = FlexiStringUtil::$sFuncName($sValue, $sSep);
						}

						$mValue["#value"] = $sValue;
					}
				}
			}
			//notice msg
			if (isset($aNotice[$sKey]))
			{
				$mValue["#notice"] = $aNotice[$sKey];
			}
		}
  }

}
