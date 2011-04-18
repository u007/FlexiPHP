<?php
/**
 * Description of FlexiDateUtil
 *
 * @author james
 */
class FlexiDateUtil {

  public static function getDisplayDateRange($sSQLDate1, $sSQLDate2) {

    if (empty($sSQLDate2) || (!empty($sSQLDate2) && substr($sSQLDate2,0,10) == "0000-00-00")) {
      //no date 2
      $sSQLDate2 = $sSQLDate1;
    }

    if (substr($sSQLDate1,0,10) == substr($sSQLDate2,0,10)) {
      //same date
      $aDate1 = self::parseSQLDateToTime($sSQLDate1);
      return $aDate1["mday"] . " " . $aDate1["month"] . " " . $aDate1["year"];
    }

    //is different date
    
    $aDate1 = getdate(self::parseSQLDateToTime($sSQLDate1));
    $aDate2 = getdate(self::parseSQLDateToTime($sSQLDate2));

    //same month
    if ($aDate2["mon"] == $aDate1["mon"] && $aDate2["year"] == $aDate1["year"]) {
      return $aDate1["mday"] . " - " . $aDate2["mday"]  . " " . $aDate2["month"] . " " . $aDate2["year"];
    }

    //if only same year
    if ($aDate2["year"] == $aDate1["year"]) {
      return $aDate1["mday"] . " " . $aDate1["month"] . " - " . $aDate2["mday"]  . " " . $aDate2["month"] . " " . $aDate2["year"];
    }

    //BOTH MONTH AND YEAR DIFF
    return $aDate1["mday"] . " " . $aDate1["month"] . " " . $aDate1["year"] . " - " . $aDate2["mday"] . " " . $aDate2["month"] . " " . $aDate2["year"];
  }

  public static function getDisplayDateTime($aiTime = null, $asFormat=null) {
    $iTime = empty($aiTime) ? time() : $aiTime;
    $sFormat = empty($asFormat) ? FlexiConfig::$sDisplayDateFormat : $asFormat;
    return date($sFormat, $iTime);
  }
  /**
   * Get current iso date for sql
   * @return String
   */
  public static function getSQLDateNow() {
    return date("Y-m-d H:i:s");
  }
  
  public static function getDisplaySQLDate($sSQLDate, $asFormat =null) {
    $sDate = self::getDisplaySQLDateTime($sSQLDate, $asFormat);
    $sDate = empty($sDate) ? $sDate: substr($sDate,0, 10);
    return $sDate;
  }

  public static function getDisplaySQLDateTime($sSQLDate, $asFormat =null) {
    $sFormat = empty($asFormat) ? FlexiConfig::$sDisplayDateFormat : $asFormat;
    $iTime = self::parseSQLDateToTime($sSQLDate);

    return date($sFormat, $iTime);
  }

  public static function parseSQLDateToTime($sSQLDate) {
    if (empty($sSQLDate)) { return null; }
    
    $aDate = explode(" ", $sSQLDate);
    $sSep = "-";

    $aDateOnly = explode($sSep, $aDate[0]);
    $aTimeOnly = count($aDate) >= 2? explode(":", $aDate[1]) : array("00","00","00");

    return mktime($aTimeOnly[0], $aTimeOnly[1], $aTimeOnly[2],
            $aDateOnly[1], $aDateOnly[2], $aDateOnly[0]);
  }
  public static function parseFormToSQLDate($sDateValue, $sFormFormat=null) {
    if (empty($sDateValue)) { return null; }

    $sResult = self::parseFormToSQLDateTime($sDateValue, $sFormFormat);
    $aDate = explode(" ", $sResult);
    return $aDate[0];
  }

  public static function parseFormToSQLDateTime($sDateValue, $sFormFormat=null) {
    $bDebug = false;
    if (empty($sDateValue)) { return null; }

    $sFormFormat = empty($sFormFormat)? FlexiConfig::$sInputDateFormat: $sFormFormat;
    $sPHPFormat = self::getPHPDateTimeFormat($sFormFormat);
    
    preg_match_all("/([-_\/\:i.]+)/i", $sPHPFormat, $match, PREG_OFFSET_CAPTURE);
    $iLastPos = 0;
    $aDate = getdate();
    foreach($match[0] as $aMatch) {
      $sSep = $aMatch[0];
      $iPos = $aMatch[1];
      if ($iPos-$iLastPos > 0) {
        $sDate = substr($sDateValue, $iLastPos, $iPos-$iLastPos);
        $sType = substr($sPHPFormat, $iLastPos, $iPos-$iLastPos);
        switch(strtolower($sType)) {
          case "d":
            $aDate["mday"] = $sDate;
            break;
          case "m":
            $aDate["mon"] = $sDate;
            break;
          case "y":
            $aDate["year"] = $sDate;
            break;
          case "h":
            $aDate["hours"] = $sDate;
            break;
          case "i":
            $aDate["minutes"] = $sDate;
            break;
          case "s":
            $aDate["seconds"] = $sDate;
            break;
        }
      }
      //"d","m","Y","H","i","s"
      $iLastPos = $iPos;
    }
    return $aDate["year"] . "-" . $aDate["mon"] . "-" . $aDate["mday"] .
      " " . $aDate["hours"] . ":" . $aDate["minutes"] . ":" . $aDate["seconds"];
  }

  /**
   * Get duration in unit
   * @param String $sUnit: year/month/day/hour/minute/second
   * @param String $sStart iso date
   * @param String $asEnd iso date / null => now
   * @return int
   */
  public static function getDurationInUnit($sUnit, $sStart, $asEnd = null) {
    $aDuration = self::getDuration($sStart, $asEnd);
    if ($sUnit=="year") { return $aDuration["year"]; }
    
    $iMonth = ($aDuration["year"]*12) + $aDuration["month"];
    if ($sUnit=="month") { return $iMonth; }

    $iDay = ($iMonth*30) + $aDuration["day"];
    if ($sUnit=="day") { return $iDay; }

    $iHour = ($iDay * 24) + $aDuration["hour"];
    if ($sUnit=="hour") { return $iHour; }

    $iMinute = ($iHour * 60) + $aDuration["minute"];
    if ($sUnit=="minute") { return $iMinute; }

    $iSecond = ($iMinute * 60) + $aDuration["second"];
    if ($sUnit=="second") { return $iSecond; }

    throw new FlexiException("Unknown unit: " . $sUnit, ERROR_UNKNOWNTYPE);
  }

  /**
   * Get Duration summary,
   *  duration in highest unit
   * @param String $sStart iso date
   * @param String $asEnd iso date / null => now
   * @return String: example: # year(s) ago
   */
  public static function getDurationSummary($sStart, $asEnd = null) {
    $aDuration = self::getDuration($sStart, $asEnd);

    if ($aDuration["year"] > 0) { return $aDuration["year"] . " year(s)"; }
    if ($aDuration["month"] > 0) { return $aDuration["month"] . " month(s)"; }
    if ($aDuration["day"] > 0) { return $aDuration["day"] . " day(s)"; }
    if ($aDuration["hour"] > 0) { return $aDuration["hour"] . " hour(s)"; }
    if ($aDuration["minute"] > 0) { return $aDuration["minute"] . " minute(s)"; }

    return $aDuration["second"] . " second(s)";
  }
  
  /**
   * Get Duration summary by timestamp,
   *  duration in highest unit
   * @param String $iStart iso date
   * @param String $aIEnd iso date / null => now
   * @return String: example: # year(s) ago
   */
  public static function getDurationSummaryByTime($iStart, $aIEnd = null) {
    $aDuration = self::getDurationByTime($iStart, $aIEnd);

    if ($aDuration["year"] > 0) { return $aDuration["year"] . " year(s)"; }
    if ($aDuration["month"] > 0) { return $aDuration["month"] . " month(s)"; }
    if ($aDuration["day"] > 0) { return $aDuration["day"] . " day(s)"; }
    if ($aDuration["hour"] > 0) { return $aDuration["hour"] . " hour(s)"; }
    if ($aDuration["minute"] > 0) { return $aDuration["minute"] . " minute(s)"; }

    return $aDuration["second"] . " second(s)";
  }

  /**
   * Get duration
   * @param String $sStart iso date
   * @param String $asEnd iso date / null => now
   * @return array("year", "month", "day", "hour", "minute", "second)
   */
  public static function getDuration($sStart, $asEnd = null) {

    $aResult = array(
      "year" => 0,
      "month" => 0,
      "day" => 0,
      "hour" => 0,
      "minute" => 0,
      "second" => 0
    );

    if (! empty($sStart)) {
      $iTime = strtotime($sStart);
      $iEndTime = empty($asEnd) ? time() : strtotime($asEnd);
	  
	  return $this->getDurationByTime($iTime, $iEndTime);
    } //if time is valid
    
    return $aResult;
  }
  
  /**
   * Get duration
   * @param int $iStart timestamp
   * @param int $aiEnd timestamp
   * @return array("year", "month", "day", "hour", "minute", "second)
   */
  public static function getDurationByTime($iStart, $aiEnd = null) {

    $aResult = array(
      "year" => 0,
      "month" => 0,
      "day" => 0,
      "hour" => 0,
      "minute" => 0,
      "second" => 0
    );

    if (! empty($iStart)) {
      $iTime = $iStart;
      $iEndTime = $aiEnd;

      $iDiff = $iEndTime - $iTime;

      $iMin = 60;
      $iHour = $iMin * 60;
      $iDay = $iHour * 24;
      $iMonth = $iDay * 30;
      $iYear = $iMonth * 12;

      if ($iDiff >= $iYear)
      {
        $aResult["year"] = floor($iDiff / $iYear);
        $iDiff -= $aResult["year"] * $iYear;
      }

      if ($iDiff >= $iMonth)
      {
        $aResult["month"] = floor($iDiff / $iMonth);
        $iDiff -= $aResult["month"] * $iMonth;
      }

      if ($iDiff >= $iDay)
      {
        $aResult["day"] = floor($iDiff / $iDay);
        $iDiff -= $aResult["day"] * $iDay;
      }

      if ($iDiff >= $iHour)
      {
        $aResult["hour"] = floor($iDiff / $iHour);
        $iDiff -= $aResult["hour"] * $iHour;
      }

      if ($iDiff >= $iMin)
      {
        $aResult["minute"] = floor($iDiff / $iMin);
        $iDiff -= $aResult["minute"] * $iMin;
      }

      $aResult["second"] = $iDiff;
      
    } //if time is valid

		

//		if ($iDiff >= $iYear)
//		{
//			return floor($iDiff / $iYear) . " year(s) ago";
//		}
//		else if($iDiff >= $iMonth)
//		{
//			return floor($iDiff / $iMonth) . " month(s) ago";
//		}
//		else if($iDiff >= $iDay)
//		{
//			return floor($iDiff / $iDay) . " day(s) ago";
//		}
//		else if($iDiff >= $iHour)
//		{
//			return floor($iDiff / $iHour) . " hour(s) ago";
//		}
//		else if($iDiff >= $iMin)
//		{
//			return floor($iDiff / $iMin) . " minute(s) ago";
//		}
//		else
//		{
//			return $iDiff . " second(s) ago";
//		}

    return $aResult;
  }

  public static function getPHPDateTimeFormat($sFormat) {
    return str_replace(array("dd","mm","yy","hh","ii","ss"), array("d","m","Y","H","i","s"), $sFormat);
  }

}