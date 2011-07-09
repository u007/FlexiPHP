<?php

class FlexiService {
  static $aService = array();

  public static function getService($sName, $sClass, $aParam=array()) {
    if (isset(self::$aService[$sName]) && !is_null(self::$aService[$sName])) {
      return self::$aService[$sName];
    }
    
    if (!class_exists($sClass)) throw new Exception("Service class missing: " . $sClass);
    self::$aService[$sName] = new $sClass($aParam);
    return self::$aService[$sName];
  }

  public static function getWhere($aCond) {
    $sSQL = ""; $aValue = array();
    foreach($aCond as $sKey => $sValue) {
      $aKey = explode(":", $sKey);
      $sSQL .= empty($sSQL) ? "": " and ";
      if (count($aKey) > 1) {

        if ($aKey[1] =="is null" || $aKey[1]=="is not null") {
          $sSQL .= self::cleanField($aKey[0]) . " " . $aKey[1];
        } else {
          $sSQL .= self::cleanField($aKey[0]) . " " . $aKey[1] . " :" . $aKey[0];
          $aValue[":".$aKey[0]] = $sValue;
        }
      } else {
        $sSQL .= $sKey . "=:".$sKey;
        $aValue[":".$sKey] = $sValue;
      }
    }
    return array("where" => $sSQL, "params" => $aValue);
  }

  public static function cleanField($sName) {
    if (FlexiConfig::$sDBType == "mysql") {
      return "`" . mysql_escape_string($sName) . "`";
    }
    return $sName;
  }


}
