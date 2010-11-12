<?php

/**
 * Description of FlexiEventHandler
 *
 * @author james
 */
class FlexiCalendarHandler {
  
  protected static $oInstance = null;
  public $iMonth = null;
  public $iYear = null;
  public $oWebCal = null;
  public $aEvent = array();

  protected function __construct() {

  }

  public function getCalendar() {
    return $this->oWebCal;
  }

  public function getEvents() {
    return $this->aEvent;
  }
  /**
   *
   * @param array() $aEvents, keys: eventDate, title, link
   * @param <type> $sCatId
   */
  public function process($aEvents = array(), $sCatId=null, $aiDay=null,$aiMonth=null,$aiYear=null) {
		$aTypeId = empty($iCatId) ? array() : explode(",", $iCatId);

		$iDay	= $aiDay == null ? date('d'): $aiDay;
		$iMonth = $aiMonth == null ? date("m") : $aiMonth;
		$iYear 	= $aiYear == null ? date('Y') : $aiYear;

		$sStartTimeStamp = mktime(0, 0, 0, $iMonth, 1, $iYear);
		$sStartDate		 = date('Y-m-d', $sStartTimeStamp);

		$sEndTimeStamp 	 = mktime(0, 0, 0, $iMonth+1, -1, $iYear);
		$sEndDate 		 = date('Y-m-d', $sEndTimeStamp);

		$published 	   = true;
		$eventsPerPage = 10;

		$oCal = new WebCalendar();
    $this->oWebCal = $oCal;
    $this->iMonth = $iMonth;
    $this->iYear = $iYear;
    $this->aEvent = $aEvents;
  }

  public static function getInstance($bNew = false) {
    if (self::$oInstance == null || $bNew) {
      self::$oInstance = new FlexiCalendarHandler();
    }

    return self::$oInstance;
  }
}


?>
