<?php

class FlexiService {
  static $aService = array();

  public static function getService($sName, $sClass, $aParam=array()) {
    if (isset(self::$aService[$sName]) && !is_null(self::$aService[$sName])) {
      return self::$aService[$sName];
    }

    self::$aService[$sName] = new $sClass($aParam);
    return self::$aService[$sName];
  }

  public static function getWhere($aCond) {
    $sSQL = "";
    foreach($aCond as $sKey => $sValue) {
      $aKey = explode(":", $sKey);
      $sSQL .= empty($sSQL) ? "": " and ";
      if (count($aKey) > 1) {
        $sSQL .= $aKey[0] . " " . $aKey[1] . " :" . $aKey[0];
      } else {
        $sSQL .= $sKey . "=:".$sKey;
      }
      
      
    }
  }

}
