<?php


function flexiURL($sURL, $bAjax = false)
{
	if (substr(strtolower($sURL),0, 5) == "http:" || 
		substr(strtolower($sURL),0, 6) == "https:" || 
		substr(strtolower($sURL),0, 4) == "ftp:" || 
		substr(strtolower($sURL),0, 11) == "javascript:")
	{
		return $sURL;
	}
	
	//$sep = substr(FlexiConfig::$sBaseURL, -1) == "/" ? "" : "/";
  if ($bAjax) {
    $iPos = strpos(FlexiConfig::$sBaseURL, "?");
    $sQuery = "";
    if ($iPos !== false) {
      //contain query
      //FlexiConfig::$sBaseURL[strlen(FlexiConfig::$sBaseURL)-1] != "/" &&
      //parse_str($sURL, $aInfo);
      $sBaseURL = substr(FlexiConfig::$sBaseURL, 0, $iPos);
      $sQuery = substr(FlexiConfig::$sBaseURL, $iPos+1);
    } else {
      //no query
      if (FlexiConfig::$sFramework == "modx2") {
        $sBaseURL = FlexiConfig::$sBaseURLDir;
      } else {
        $sBaseURL = FlexiConfig::$sBaseURL;
      }
    }


    if (!FlexiConfig::$bIsAdminPath && $sBaseURL[strlen($sBaseURL)-1] == "/") {
      $sBaseURL .= "flexi." . FlexiConfig::$sFramework . ".php";
    } else if(! FlexiConfig::$bIsAdminPath) {
      //else , discard ending file
      $iPos = strrpos($sBaseURL, "/");
      if ($iPos !== false) {
        $sBaseURL = substr($sBaseURL,0, $iPos+1);
      }
      $sBaseURL .= "flexi." . FlexiConfig::$sFramework . ".php";
    } else if(FlexiConfig::$sFramework == "modx2" && FlexiConfig::$bIsAdminPath) {
      global $iActionId;
      $sBaseURL .= "manager/flexi." . FlexiConfig::$sFramework . ".php?a=" . $iActionId;
    }

    if (FlexiConfig::$sFramework != "modx2" && !empty($sQuery)) {
      if (strpos($sBaseURL, "?")===false) {
        $sBaseURL .="?" . $sQuery;
      } else {
        $sBaseURL .="&" . $sQuery;
      }
    }

  } else {
    $sBaseURL = FlexiConfig::$sBaseURL;
  }

  //if (FlexiConfig::$sFramework == "modx2") var_dump($sBaseURL);
  //var_dump(FlexiConfig::$aQueryString);
	
	if (substr($sURL,0,1)=="?") { $sURL = substr($sURL, 1); }
	switch(FlexiConfig::$sFramework) {
		case "modx":
      if (FlexiConfig::$bIsAdminPath) {
        parse_str($sURL, $aInfo);
        //var_dump($sURL);
        $aInfo = array_merge(FlexiConfig::$aQueryString, $aInfo);
        $sResult = $sBaseURL . "?" . FlexiURLUtil::getQueryStringFromArray($aInfo);
      } else {
        $sResult = $sBaseURL . "?" . $sURL;
      }
			break;
    case "modx2":
      if (FlexiConfig::$bIsAdminPath) {
        parse_str($sURL, $aInfo);
        if (!$bAjax) {
          $aInfo = array_merge(FlexiConfig::$aQueryString, $aInfo);
        }

        $sResult = $sBaseURL . (strpos($sBaseURL, "?")!==false ? "&" : "?") . FlexiURLUtil::getQueryStringFromArray($aInfo);
      } else {
        $sResult = $sBaseURL . "?" . $sURL;
      }
			break;
		case "":
			$sResult = $sBaseURL . "?" . $sURL;
			break;
			
		default:
			parse_str($sURL, $aInfo);
			//var_dump($sURL);
			$aInfo = array_merge(FlexiConfig::$aQueryString, $aInfo);
			//var_dump($aInfo);
			
			$sResult = $sBaseURL;
			$sQuery = "";
			foreach($aInfo as $sKey => $sValue)
			{
				$sKey = str_replace("?", "", $sKey);
				$sQuery .= empty($sQuery) ? "" : "&";
				$sQuery .= urlencode($sKey) . "=" . urlencode($sValue);
			}
			
			$sResult .= "?" . $sQuery;
	}
	
	return $sResult;
}

function flexiUCFirstT($sText) {
  return ucfirst(flexiT($sText));
}

/**
 * return multi-lingual text based on user language
 * @param String $sText
 * @param String $sType: normal,
 *  first: ucfirst,
 *  cap: all capital
 * @return String
 */
function flexiT($sText, $sType = "normal")
{
	$sLang = FlexiConfig::getLoginHandler()->getUserLanguage();
	
  $sPath = FlexiConfig::$sLanguagePath . "/" . $sLang . ".lang.php";

  $sResult = "";
	if (is_file($sPath)) {
    require($sPath);
    if(isset($aLanguage[$sText])) {
      $sResult = $aLanguage[$sText];
    } else if( isset($aLanguage[strtolower($sText)])) {
      $sResult = $aLanguage[strtolower($sText)];
    }
    else {
      $sResult = $sText;
    }
  } else {
    $sResult = $sText;
  }

  if ($sType == "first") {
    $sResult = ucfirst($sResult);
  }
  if ($sType == "cap") {
    $sResult = strtoupper($sResult);
  }
  if ($sType == "lower") {
    $sResult = strtolower($sResult);
  }
  
	return $sResult;
}

function flexiSortByWeight($aValue1, $aValue2)
{
  if (!is_array($aValue1) || !is_array($aValue2)) {
    return 1;
  }
	if (!isset($aValue1["#weight"]) || !isset($aValue2["#weight"]))
	{
		return 1;
	}

  //var_dump($aValue1);
	
	if ($aValue1["#weight"] == $aValue2["#weight"] ) {
			return 0;
	}

  $iResult = ($aValue1["#weight"] < $aValue2["#weight"]) ? -1 : 1;
//  echo "comparing: " . $aValue1["#name"] . ", weight: " . $aValue1["#weight"] .
//    " vs " . $aValue2["#name"] . ", weight: " . $aValue2["#weight"] . "=" . $iResult . "\r\n<br/>";

	return $iResult;
}

/**
 * Get if class exists
 * @param <type> $sClass
 * @return <type>
 */
function flexiClassExists($sClass) {
  if (class_exists($sClass)) { return true; }

  $sPath = flexiGetClassPath($sClass);
  return $sPath == null ? false: true;
}

/**
 * Get Class path
 * @staticvar String $aPath
 * @param String $sClass
 * @return String
 *  null=> not found
 */
function flexiGetClassPath($sClass) {
	$sBaseDir = FlexiConfig::$sBaseDir;
  static $aPath = array();
//  if (FlexiConfig::$sFramework == "modx2")
//    echo "Getting: " . $sClass . " \r\n<br/>";
  
  if (count($aPath) < 1) {
		$aPath = array(
		"PHPMailer" => $sBaseDir . "/lib/phpmailer/class.phpmailer.php",
		"SMTP" 			=> $sBaseDir . "/lib/phpmailer/class.smtp.php",
    "Crypt_Blowfish"  => $sBaseDir . "/lib/Blowfish/Blowfish.php",
    "FlexiLogger" => $sBaseDir . "/base/Flexi/objectclass/FlexiLogger.php",
    "PEAR" => $sBaseDir . "/lib/PEAR/PEAR.php",
    "R" => $sBaseDir . "/lib/redbean/rb.php"
		);
	}

  if(substr(strtolower($sClass),0, 7) == "redbean") {
    return $sBaseDir . "/lib/redbean/rb.php";
  }
  
  if (isset($aPath[$sClass])) {
    //echo "already exists: " . $sClass;
    return $aPath[$sClass];
  }
  
  //if (FlexiConfig::$sFramework == "modx2")
  //  echo "checking: " . FlexiConfig::$sBaseDir . "/../" . FlexiConfig::$sModulePath . "\r\n<br/>";
//  if (FlexiConfig::$sFramework == "modx2")
//    echo "module path: " . FlexiConfig::$sModulePath . "\r\n<br/>";
  if (is_dir(FlexiConfig::$sModulePath))
	{
		$aModuleList = flexiDirList(FlexiConfig::$sModulePath);
		foreach($aModuleList as $sPath)
		{
//      if (FlexiConfig::$sFramework == "modx2")
//        echo "Find: " . $sClass . ", try: " . $sFilePath . "\r\n<br/>";
      
			$sFilePath = FlexiConfig::$sModulePath . "/" . $sPath . "/objectclass/" . $sClass . ".php";
      //FlexiLogger::debug(__METHOD__, "Find: " . $sClass . ", try: " . $sFilePath);
			if (is_file($sFilePath))
			{
        $aPath[$sClass] = $sFilePath;
				return $sFilePath;
			}
		}
	}

	$aModuleList = flexiDirList($sBaseDir . "/modules");
	foreach($aModuleList as $sPath)
	{
		$sFilePath = $sBaseDir . "/modules/" . $sPath . "/objectclass/" . $sClass . ".php";
    //FlexiLogger::debug(__METHOD__, "Find: " . $sClass . ", try: " . $sFilePath);
		if (is_file($sFilePath))
		{
			$aPath[$sClass] = $sFilePath;
			return $sFilePath;
		}
	}

	$aBaseList = flexiDirList($sBaseDir . "/base");
	foreach($aBaseList as $sPath)
	{
		$sFilePath = $sBaseDir . "/base/" . $sPath . "/objectclass/" . $sClass . ".php";
    //FlexiLogger::debug(__METHOD__, "Find: " . $sClass . ", try: " . $sFilePath);
		if (is_file($sFilePath))
		{
      //FlexiLogger::debug(__METHOD__, "found!");
			$aPath[$sClass] = $sFilePath;
			return $sFilePath;
		}
	}

  return null;
}

function flexiInclude($sClass)
{
	if (class_exists($sClass)) { return true; }
	$sPath = flexiGetClassPath($sClass);
  //echo "class: " . $sClass . ", path:" . $sPath . "\r\n<br/>";
	if ($sPath != null) {
    require_once($sPath);
    return true;
  }

  return false;

	//autoload files
//	if (is_dir(FlexiConfig::$sModulePath))
//	{
//		$aModuleList = flexiDirList(FlexiConfig::$sModulePath);
//		foreach($aModuleList as $sPath)
//		{
//			$sFilePath = FlexiConfig::$sModulePath . "/" . $sPath . "/autoload.php";
//			if (is_file($sFilePath))
//			{
//				require_once($sFilePath);
//				if (call_user_func ($sPath . "_autoload", array($sClass))) { return true; }
//			}
//		}
//	}
//
//	$aModuleList = flexiDirList($sBaseDir . "/modules");
//	foreach($aModuleList as $sPath)
//	{
//		$sFilePath = $sBaseDir . "/modules/" . $sPath . "/autoload.php";
//		if (is_file($sFilePath))
//		{
//			require_once($sFilePath);
//			if (call_user_func ($sPath . "_autoload", array($sClass))) { return true; }
//		}
//	}
//
//	$aModuleList = flexiDirList($sBaseDir . "/base");
//	foreach($aBaseList as $sPath)
//	{
//		$sFilePath = $sBaseDir . "/base/" . $sPath . "/autoload.php";
//		if (is_file($sFilePath))
//		{
//			require_once($sFilePath);
//			if (call_user_func ($sPath . "_autoload", array($sClass))) { return true; }
//		}
//	}
//
//	return false;
}

function flexiDirList($sPath, $bCached = true)
{
  static $aList = array();
  if (isset($aList[$sPath]) && $bCached) { return $aList[$sPath]; }
  unset($aList[$sPath]); //reset cache

  //var_dump($sPath);
  //var_dump(getcwd());
  
	if ($handle = opendir($sPath))
	{
		$aResult = array();
    while (false !== ($file = readdir($handle))) {
      $aResult[] = $file;
    }

    closedir($handle);
    $aList[$sPath] = $aResult;
    
    return $aResult;
  }
  
  return array();
}

function flexiDirListGetDirInfo($sPath, $bCached = true) {
  static $aList = array();
  if (isset($aList[$sPath]) && $bCached) { return $aList[$sPath]; }
  unset($aList[$sPath]); //reset cache
  
  $aDir = flexiDirList($sPath);
  
  foreach($aDir as $sDir) {
    if (is_dir($sDir)) {
      $aList[$sPath][] = array("name" => $sDir, "path" => $sPath);
    }
  }
  return $aList[$sPath];
}

function hex2bin($hexdata) {
  $bindata="";

  for ($i=0;$i<strlen($hexdata);$i+=2) {
    $bindata.=chr(hexdec(substr($hexdata,$i,2)));
  }

  return $bindata;
}

if (! function_exists("http_parse_headers")) {
	function http_parse_headers( $header )
	{
		$retVal = array();
		$fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
		foreach( $fields as $field ) {
			if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
				$match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
				if( isset($retVal[$match[1]]) ) {
					$retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
				} else {
					$retVal[$match[1]] = trim($match[2]);
				}
			}
		}
		return $retVal;
	}
}