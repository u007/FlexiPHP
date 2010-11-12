<?php

/**
 * Description of FlexiRedBeanValidator
 *
 * @author james
 */
class FlexiRedBeanEvent implements RedBean_Observer {
  public static $aValidator = array();

  public static function addEvent($sTable, $oClass) {
    self::$aValidator[$sTable] = & $oClass;
  }
  public function onEvent( $event, $oModel ) {
    FlexiLogger::debug(__METHOD__, $sTable . ", Event: " . $event);
    $sTable = $oModel->getMeta("type");
    $sMethod = "on" . $event;
    FlexiLogger::debug(__METHOD__, $sTable . ", Method: " . $sMethod);
    if (isset(self::$aValidator[$sTable])) {
      if (is_array(self::$aValidator[$sTable])) {
        foreach(self::$aValidator[$sTable] as $oClass) {
          if (method_exists($oClass, $sMethod)) {
            FlexiLogger::debug(__METHOD__, "Calling: " . $sTable . ": " . $sMethod);
            $oClass->$sMethod($oModel);
          }
        }//is array
      } else {
        if (method_exists(self::$aValidator[$sTable], $sMethod)) {
          FlexiLogger::info(__METHOD__, "Calling: " . $sTable . ": " . $sMethod);
          self::$aValidator[$sTable]->$sMethod($oModel);
        }
        
      }//if is direct
    } //if exists table event

  } //onevent
}

?>
