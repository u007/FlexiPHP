<?php
/* 
 * Used to trigger event
 * Example: FlexiEvent::addEvent("type", "function");
 *          to trigger, FlexiEvent::triggerEvent("type", args:array);
 */
class FlexiEvent {
  public static $aEvents = array();

  public static function addEvent($sName, $sFunc) {
    if (!isset(self::$aEvents[$sName])) {
      self::$aEvents[$sName] = array();
    }
    self::$aEvents[$sName][] = $sFunc;
  }

  public static function getEvent($sName) {
    if(isset(self::$aEvents[$sName])) {
      return self::$aEvents[$sName];
    }
    return array();
  }

  public static function triggerEvent($sName, & $args=null) {
    FlexiLogger::debug(__METHOD__, "Name: " . $sName );
    $aFunc = self::getEvent($sName);
    foreach($aFunc as $sFunc) {
      FlexiLogger::debug(__METHOD__, "Function: " . $sFunc);
      $bIsClass = strpos($sFunc, "::")!==false ? true : false;
      if ($bIsClass) {
        list($sClass, $sMethod) = explode("::", $sFunc);
        FlexiLogger::debug(__METHOD__, "Class: " . $sClass . ", method: " . $sMethod);
        call_user_func(array($sClass, $sMethod), $args);
      } else {
        call_user_func($sFunc, $args);
      }
    } //end foreach
  }//end triggerevent

}