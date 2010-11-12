<?php

class FlexiLogger
{
	
	public static function info($sStatus, $sMsg)
	{
		if (FlexiConfig::$iLogLevel >= 1)
		{
			self::log("info", $sStatus . "(i): " . $sMsg);
		}
	}
	
	public static function error($sStatus, $sMsg)
	{
		if (FlexiConfig::$iLogLevel >= 1)
		{
			self::log("error", $sStatus . "(e): " . $sMsg);
		}
	}
	
	public static function warn($sStatus, $sMsg)
	{
		if (FlexiConfig::$iLogLevel >= 2)
		{
			self::log("warn", $sStatus . "(w): " . $sMsg);
		}
	}
	
	public static function debug($sStatus, $sMsg)
	{
		if (FlexiConfig::$iLogLevel >= 3)
		{
			self::log("debug", $sStatus . "(d): " . $sMsg);
		}
	}
	
	public static function log($asType, $sMessage)
	{
    switch(FlexiConfig::$sFramework) {
      case "modx":
      case "modx2":
        global $modx;
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
          case "debug":
            $iType = 1;
          default:
            $iType = 0;
        }
        //echo "$sType:$sMessage<br/>\n";
        $modx->logEvent(0, $iType, $sMessage, substr($sMessage, 0, 47) . (strlen($sMessage) > 47 ? "..." : ""));
        break;
      default:
        file_put_contents(FlexiConfig::$sLogFile, date("Ymd.H:m:s") . ":" . $sMessage . "\r\n", FILE_APPEND);
    }
	}
	
	
}
