<?php

class FlexiLogger
{
	public static function general($sStatus, $sMsg)
	{
		if (FlexiConfig::$iLogLevel >= 1)
		{
			self::log("general", $sStatus . "(g): " . $sMsg, $sStatus);
		}
	}

	public static function info($sStatus, $sMsg)
	{
		if (FlexiConfig::$iLogLevel >= 1)
		{
			self::log("info", $sStatus . "(i): " . $sMsg, $sStatus);
		}
	}
	
	public static function error($sStatus, $sMsg)
	{
		if (FlexiConfig::$iLogLevel >= 1)
		{
			self::log("error", $sStatus . "(e): " . $sMsg, $sStatus);
		}
	}
	
	public static function warn($sStatus, $sMsg)
	{
		if (FlexiConfig::$iLogLevel >= 2)
		{
			self::log("warn", $sStatus . "(w): " . $sMsg, $sStatus);
		}
	}
	
	public static function debug($sStatus, $sMsg)
	{
		if (FlexiConfig::$iLogLevel >= 3)
		{
			self::log("debug", $sStatus . "(d): " . $sMsg, $sStatus);
		}
	}
	
	public static function log($asType, $sMessage, $sEventId="")
	{
    $iType = -1;
    switch($asType) {
      case "warn":
        $iType = 2;
        break;
      case "error":
        $iType = 3;
        break;
      case "info":
      case "notice":
        $iType = 1;
        break;
      case "debug":
        $iType = 1;
        break;
      default:
        $iType = 0;
    }
    if (strlen($sEventId)>50 ) {
      $sEventId = substr($sEventId, -50);
    }
    switch(FlexiConfig::$sFramework) {
      case "modx":
      case "modx2":
        global $modx;
        
        //echo "$sType:$sMessage<br/>\n";
        $modx->logEvent(0, $iType, $sMessage, substr($sMessage, 0, 47) . (strlen($sMessage) > 47 ? "..." : ""));
        break;
      case "wirenet":
        $aLog = array(
          "source" => $sEventId,
          "type" => $iType,
          "createdon" => gmmktime(),
          //"user" => FlexiConfig::getLoginHandler()->getLoggedInUserId(),
          "user" => !empty($_SESSION['screenname']) ? $_SESSION['screenname']: (isset($_SESSION['activenodeid'])? $_SESSION['activenodeid']: 0),
          "description" => $sMessage
        );
        FlexiModelUtil::getInstance()->insertXPDO("modx_event_log", $aLog);
        break;
      default:
        file_put_contents(FlexiConfig::$sLogFile, date("Ymd.H:m:s") . ":" . $sMessage . "\r\n", FILE_APPEND);
    }
	}
	
	
}
