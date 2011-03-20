<?php

class FlexiObjectListManager extends FlexiLogManager {
  protected $oManager = null;
  protected $sRepoPath = "";
  protected $oObject = null;
  
  public function __construct($aParam) {
    parent::__construct($aParam);
    $this->setPath(FlexiConfig::$sRepositoryDir);
    $this->setLogPath(FlexiConfig::$sAssetsDir . "_logs");
  }

  public function setPath($sPath) {
    $this->sRepoPath = $sPath;
  }
  
  public function setObjectName($sName) {
    $this->oObject = $this->getManager()->load($sName);
  }

  public function validateFieldData(&$oRow, &$sType) {
    $oObject = $this->getObject();
    return $this->_validateFieldData($oObject, $oRow, $sType);
  }

  public function _validateFieldData(&$oObject, &$oRow, &$sType) {
    $this->onBeforeCheckValid($oRow, $sType);
    $oObject->checkValid($oRow, $sType);
    //will not pass here if checkvalid fail as it will throw exception
    $bResult = true;
    $this->onCheckValid($bResult, $oRow, $sType);

    return $bResult;
  }

  public function onBeforeCheckValid(& $oRow, &$sType) {}
  public function onCheckValid(&$bResult, & $oRow, & $sType) {}

  public function doTableQuery(& $aCond=array(), & $aGroupBy=null, & $aOrderby=null, & $sSelect=null, & $iLimit=null, & $iOffset=0) {
    $sTable = $this->oObject->getTableName();
    return $this->doQuery($sTable, $aCond, $aGroupBy, $aOrderby, $sSelect, $iLimit, $iOffset);
  }

  /**
   * Load a single row data of object
   * @param array $aCond
   * @param String $sSelect
   * @param array $aGroupBy
   * @param array $aOrderby
   * @return array
   */
  public function loadObjectRow($aCond, & $sSelect=null, & $aGroupBy=null, & $aOrderby=null) {
    $sTable = $this->oObject->getTableName();
    $stmt = $this->_doQuery($sTable, $aCond, $aGroupBy, $aOrderby, $sSelect);
    $aResult = $stmt->fetch(PDO::FETCH_ASSOC);
    return $aResult;
  }

  public function getFieldsName() {
    $oObject = $this->oObject;
    return array_keys($oObject->aChild["field"]);
  }
  /**
   * Get active object schema
   * @return FlexiTableObject
   */
  public function getObject() {
    return $this->oObject;
  }
  
  public function getNewObjectRow() {
    return $this->oObject->getNewRow();
  }
  /**
   * Pass in parameter to get query SQL
   * @param String $sTable
   * @param array $aCond
   * @param array $aGroupBy
   * @param array $aOrderby
   * @param String $sSelect
   * @param int $iLimit
   * @param int $iOffset
   */
  public function getQuery($sTable="", & $aCond=array(), & $aGroupBy=null, & $aOrderby=null, & $sSelect=null, & $iLimit=null, & $iOffset=0) {
    $bDebug = false;
    $xpdo = FlexiModelUtil::getInstance()->getXPDO();

    //any auto condition changes here
    
    //pass to event handler, throw exception to stop process
    //$this->onQueryParam($sTable, $aCond, $aGroupBy, $aOrderby, $sSelect, $iLimit, $iOffset);
    $sSQL = "";
    if (empty($sSelect)) {
      $sSQL = "select * from " . $sTable;
    } else {
      $sSQL = $sSelect;
      if (!empty($sTable)) {
        $sSQL .= " from " . FlexiModelUtil::getSQLName($sTable);
      }
    }

    $aWhere = FlexiModelUtil::parseSQLCond($aCond);

    if (!empty($aWhere["sql"])) {
      $sSQL .= " where " . $aWhere["sql"];
      $aParam = array_merge($aParam, $aWhere["param"]);
    }
    
    //if ($bDebug) var_dump($aCond);
    //if ($bDebug) var_dump($aWhere);
    //if ($bDebug) var_dump($aParam);
    $sGroupSQL = "";
    if (!is_null($aGroupBy)) {
      foreach($aGroupBy as $sGroup) {
        $sGroupSQL .= empty($sGroupSQL) ? "": ",";
        $sGroupSQL .= FlexiModelUtil::parseSQLName($sGroup);
      }
      $sSQL .= !empty($sGroupSQL)? " group by " . $sGroupSQL: "";
    }
    
    $sOrderbySQL = "";
    foreach($aOrderby as $sOrderBy) {
      $sOrderbySQL .= empty($sOrderbySQL) ? "": ",";
      $aOrder = explode(" " , $sOrderBy);
      $sOrderbySQL .= FlexiModelUtil::parseSQLName($aOrder[0]) . " " . $sOrderType;
    }
    $sSQL .= !empty($sOrderbySQL) ? " order by " . $sOrderbySQL : "";
    
    if ($iLimit > 0 ) {
      $sSQL .= " limit " . $iLimit . " offset " . $iOffset;
    }
    if ($bDebug) echo __METHOD__ . ": " . $sSQL . "<br/>\n";
    $this->onParseQueryBinding($sSQL, $aParam);
    $sResultSQL = $xpdo->parseBindings($sSQL, $aParam);
    return $sResultSQL;
  }

  public function doQuery($sTable="", & $aCond=array(), & $aGroupBy=null, & $aOrderby=null, & $sSelect=null, & $iLimit=null, & $iOffset=0) {
    //echo "sql: " . $sResultSQL;
    $stmt = $this->_doQuery($sTable, $aCond, $aGroupBy, $aOrderby, $sSelect, $iLimit, $iOffset);
    
    $aResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $aResult;
  }

  public function _doQuery($sTable="", & $aCond=array(), & $aGroupBy=null, & $aOrderby=null, & $sSelect=null, & $iLimit=null, & $iOffset=0) {
    $xpdo = FlexiModelUtil::getInstance()->getXPDO();
    $sSQL = $this->getQuery($sTable, $aCond, $aGroupBy, $aOrderby, $sSelect, $iLimit, $iOffset);
    $this->onQuery($sSQL);
    //echo "sql: " . $sResultSQL;
    $stmt = $xpdo->query($sSQL);
    if ($stmt) {
      return $stmt;
    } else {
      $aError = $xpdo->errorInfo();
      throw new Exception("Query failed: " . $aError[2] . ":".$sResultSQL);
    }
  }


  public function onParseQueryBinding(&$sSQL, &$aParam) {}
  /**
   * might not be used
   * On query table, with parameter, before generating SQL
   * @param String $sTable
   * @param array $aCond
   * @param array $aGroupBy
   * @param array $aOrderby
   * @param String $sSelect
   * @param int $iLimit
   * @param int $iOffset
   */
  public function onQueryParam(&$sTable, &$aCond, &$aGroupBy, &$aOrderby, &$sSelect, &$iLimit, &$iOffset) { }

  /**
   * On query of a sql
   * @param String $sSQL
   */
  public function onQuery(&$sSQL) { }


  //THESE 2 SHOULD BE IN CONTROLLER OR VIEW
  /**
   * Call before render form
   * @param FlexiTableObject $oObject
   * @param String $sType insert / update
   */
  //public function onBeforeRenderForm(FlexiTableObject & $oObject, $sType) {}
  //public function onBeforeRenderFormField(FlexiTableFieldObject & $oField) {}


  public function getManager() {
    $bDebug = false;
    if (is_null($this->oManager)) {
      $this->oManager = new FlexiObjectManager();
      if ($bDebug) echo "Setting repo: " . $this->sRepoPath . "<br/>\n";
      $this->oManager->setPath($this->sRepoPath);
    }
    return $this->oManager;
  }

  public function logSQL($sSQL) {
    $this->doLog($sSQL, "sql");
  }

}