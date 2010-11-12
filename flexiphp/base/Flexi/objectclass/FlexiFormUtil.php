<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class FlexiFormUtil {

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
