<?php

abstract class FlexiBaseService {
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
}