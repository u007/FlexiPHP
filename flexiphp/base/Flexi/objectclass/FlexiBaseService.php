<?php

abstract class FlexiBaseService {
  //use for back port for php5.2 or lower  
  static $i = 0;
  static $fl = null;

  protected $oActiveController = null;

  public function __construct(& $controller, $aParam=array()) {
    $this->oActiveController = & $controller;
    $this->init($aParam);
  }

  /**
   * get table rows
   * @param array $aCond ['field:condition'=>'value'], eg: 'id:=' => 1
   * @param String $sOrderby
   * @param String $sSelect, eg: select id, name
   */
  public function getFetchList($sTable, $aCond=array(), $sOrderby=null, $sSelect=null, $iLimit=null, $iOffset=0) {
    $select = empty($sSelect) ? "select * ": $sSelect;
    $aWhere = FlexiService::getWhere($aCond);
    $sSQL = $select . " from " . FlexiService::cleanField($sTable);
    $aParams = array();
    if (!empty($aWhere["where"])) {
      $aParams = $aWhere["params"];
      $sSQL .= " where " . $aWhere["where"];
    }

    if (!empty($sOrderby)) {
      $sSQL .= " order by " . $sOrderby;
    }

    if ($iLimit > 0 ) {
      $sSQL .= " limit " . $iLimit . " offset " . $iOffset;
    }
    FlexiLogger::info(__METHOD__, "SQL: " . $sSQL . "|" . print_r($aParams,true));
    return FlexiModelUtil::getInstance()->getRedbeanFetchAll($sSQL, $aParams);
  }

  /**
   * get table rows
   * @param array $aCond ['field:condition'=>'value'], eg: 'id:=' => 1
   * @param String $sOrderby
   * @param String $sSelect, eg: select id, name
   */
  public function getFetchOne($sTable, $aCond=array(), $sOrderby=null, $sSelect=null) {
    $select = empty($sSelect) ? "select * ": $sSelect;
    $aWhere = FlexiService::getWhere($aCond);
    $sSQL = $select . " from " . FlexiService::cleanField($sTable);
    $aParams = array();
    if (!empty($aWhere["where"])) {
      $aParams = $aWhere["params"];
      $sSQL .= " where " . $aWhere["where"];
    }
    
    if (!empty($sOrderby)) {
      $sSQL .= " order by " . $sOrderby;
    }
    $sSQL .= " limit 1";

    return FlexiModelUtil::getInstance()->getRedbeanFetchOne($sSQL, $aParams);
  }

  abstract public function init($aParam=array());
  
  public static function getClassName() {
    if (function_exists("get_called_class")) {
      return get_called_class();
    }
    
    return self::get_called_class();
  }
  
  public static function get_called_class() {
    $bt = debug_backtrace();
    if( self::$fl == $bt[2]['file'].$bt[2]['line'] ) {
      self::$i++;
    } else {
      self::$i = 0;
      self::$fl = $bt[2]['file'].$bt[2]['line'];
    }
    $lines = file($bt[2]['file']);
    preg_match_all(
      '/([a-zA-Z0-9\_]+)::'.$bt[2]['function'].'/',
      $lines[$bt[2]['line']-1],
      $matches
    );
    return $matches[1][0];
  }
  
  public static function getInstance() {
    $sClass = self::getClassName();
    $sName = substr($sClass, 0, -7);
    return FlexiService::getService($sName, $sClass);
  }
}