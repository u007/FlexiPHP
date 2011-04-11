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


  public function deleteRows($oCond) {
    $sTable = $this->oObject->sTableName;

    $aCond = is_array($oCond)? $oCond: get_object_vars($oCond);
    $aCond = $this->loadConditionByAlias($aCond);
    $aWhere = FlexiModelUtil::parseSQLCond($aCond);
    if (empty($aWhere["sql"])) throw new Exception("No condition specified");
    //var_dump($aCond);
    $sSQL = "DELETE FROM " . FlexiModelUtil::getSQLName($sTable) . " WHERE ".
      $aWhere["sql"];
    //echo "SQL: " . $sSQL;
    return FlexiModelUtil::getInstance()->getXPDOExecute($sSQL, $aWhere["param"]);
  }

  public function loadConditionByAlias($aCond) {
    $oTable = $this->getObject();
    $aResult = array();
    foreach($aCond as $sField => $sValue) {
      $oField = $oTable->getFieldByAlias($sField);
      $aResult[$oField->getName()] = $sValue;
    }
    return $aResult;
  }

  public function mergeObjectRow(&$oRow) {
    $bDebug = false;
    $oObject = $this->getObject();
    $this->onBeforeMerge($oRow);

    foreach($oObject->aChild["field"] as $sField => $oField) {
      if ($bDebug) echo __METHOD__ . ": field: " . $sField . "\n<br/>";
      if (isset($oRow[$sField])) {
        //if (! $this->onBeforeStoreField($oField, $oRow, $sType)) continue;
        $oStore[$sField] = $this->getFieldDataFromForm($oField, $oRow);
      }
    }
    $this->onMerge($oStore, $oObject, $oRow);
    return $oStore;
  }

  public function onBeforeMerge(&$oRow) {}
  public function onMerge(&$oStore, &$oObject, $oRow) {}

  public function storeObjectRow(&$oRow, &$sType) {
    $bDebug = false;
    if (! $this->validateFieldData($oRow, $sType)) {
      throw new Exception("Validation failed");
    }
    if ($bDebug) echo __METHOD__ . ": data: " . print_r($oRow,true) . "\n<br/>";

    $oObject = $this->getObject();
    $oStore = array();
    if (! $this->onBeforeStore($oObject, $oRow, $sType)) return false;
    
    foreach($oObject->aChild["field"] as $sField => $oField) {
      if ($bDebug) echo __METHOD__ . ": field: " . $sField . "\n<br/>";
      if (isset($oRow[$sField])) {
        //if (! $this->onBeforeStoreField($oField, $oRow, $sType)) continue;
        $oStore[$sField] = $this->getFieldDataFromForm($oField, $oRow);
      }
    }
    if( !$this->onStore($oStore, $oObject, $oRow, $sType)) {
      return false;
    }
    return $this->_storeObjectRow($oStore, $sType);
  }


  public function _storeObjectRow($oRow, $sType) {
    $oObject = $this->getObject();
    $sTable = $oObject->getTableName();
    
    $aPrimary = $oObject->getPrimaryFields();
    switch($sType) {
      case "insert":
        return FlexiModelUtil::getInstance()->insertXPDO($sTable, $oRow, $aPrimary);
        break;
      case "update":
        return FlexiModelUtil::getInstance()->updateXPDO($sTable, $oRow, $aPrimary);
        break;
      default:
        throw new Exception("Unknown store type: " . $sType);
    }
    return true;
  }
  

  /**
   * get value safe for database storage
   * @param FlexiTableFieldObject $oField
   * @param array $oRow
   * @return String
   */
  public function getFieldDataFromForm(FlexiTableFieldObject $oField, $oRow) {
    $bDebug = false;
    if ($bDebug) echo __METHOD__ . ": " . print_r($oRow, true) . "\n<br/>";
    $sName = $oField->getName();
    $mValue = $oRow[$sName];
    
    if ($oField->allowhtml) {
    } else {
      $mValue = strip_tags($mValue);
    }
    return $mValue;
  }
  /**
   * event for insert or update
   *  return true to continue
   * @param array $oStore - to save
   * @param FlexiTableObject $oObject
   * @param array $oRow - from form
   * @param String $sType
   * @return boolean
   */
  public function onStore(&$oStore, FlexiTableObject &$oObject, &$oRow, $sType) { return true; }

  public function onBeforeStore(FlexiTableObject &$oObject, &$oRow, $sType) { return true; }

  public function validateFieldData(&$oRow, &$sType) {
    $oObject = $this->getObject();
    return $this->_validateFieldData($oObject, $oRow, $sType);
  }

  public function _validateFieldData(&$oObject, &$oRow, &$sType) {
    if (!$this->onBeforeCheckValidData($oRow, $sType)) { return false; }
    $oObject->checkValidData($oRow, $sType);
    //will not pass here if checkvalid fail as it will throw exception
    $this->onCheckValidData($oObject, $oRow, $sType);
    return true;
  }

  public function onBeforeCheckValidData(& $oRow, &$sType) { return true; }
  public function onCheckValidData($oObject, & $oRow, $sType) {}

  public function doTableQuery(& $aCond=array(), & $aGroupBy=null, & $aOrderby=null, & $sSelect=null, & $iLimit=null, & $iOffset=0) {
    $sTable = $this->oObject->getTableName();
    return $this->doQuery($sTable, $aCond, $aGroupBy, $aOrderby, $sSelect, $iLimit, $iOffset);
  }

  public function doTableCountQuery(& $aCond=array(), & $aGroupBy=null) {
    $sTable = $this->oObject->getTableName();
    return $this->doQueryCount($sTable, $aCond, $aGroupBy);
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
    $oRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $this->onLoadRow($oRow, $aCond, $sSelect, $aGroupBy, $aOrderby);
    return $oRow;
  }
  
  public function onLoadRow(&$oRow, $aCond, $sSelect, $aGroupBy, $aOrderby) {}
  /**
   * Get all fields name
   * @return array
   */
  public function getFieldsName() {
    $oObject = $this->oObject;
    return array_keys($oObject->aChild["field"]);
  }

  public function getPrimaryValue($oRow) {
    $aPrimary = $this->getPrimaryFields();
    $aResult = array();
    foreach($aPrimary as $sPrimary) {
      $aResult[$sPrimary] = $oRow[$sPrimary];
    }
    return $aResult;
  }
  /**
   * get all primary fields name
   * @return array
   */
  public function getPrimaryFields() {
    $oObject = $this->oObject;
    return $oObject->getPrimaryFields();
  }

  /**
   * get all fields can list
   * @return array
   */
  public function getListFields() {
    $oObject = $this->oObject;
    return $oObject->getListFields();
  }
  /**
   * Get active object schema
   * @return FlexiTableObject
   */
  public function getObject() {
    return $this->oObject;
  }
  
  public function getNewObjectRow($aParam=array()) {
    $oRow = & $this->oObject->getNewRow();
    $this->onNewRow($oRow, $aParam);
    return $oRow;
  }

  public function onNewRow(& $oRow, $aParam) {}
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
  public function getQuery($sTable="", $aCond=array(), $aGroupBy=null, $aOrderby=null, $sSelect=null, $iLimit=null, $iOffset=0) {
    $bDebug = false;
    $xpdo = FlexiModelUtil::getInstance()->getXPDO();

    //any auto condition changes here
    
    //pass to event handler, throw exception to stop process
    //$this->onQueryParam($sTable, $aCond, $aGroupBy, $aOrderby, $sSelect, $iLimit, $iOffset);
    $sSQL = "";
    if (empty($sSelect)) {
      $sSQL = "SELECT * FROM " . $sTable;
    } else {
      $sSQL = $sSelect;
      if (!empty($sTable)) {
        $sSQL .= " FROM " . FlexiModelUtil::getSQLName($sTable);
      }
    }
    $aParam = array();
    $aWhere = FlexiModelUtil::parseSQLCond($aCond);

    if (!empty($aWhere["sql"])) {
      $sSQL .= " WHERE " . $aWhere["sql"];
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

    if (!is_null($aOrderby)) {
      $sOrderbySQL = "";
      foreach($aOrderby as $sOrderBy) {
        $sOrderbySQL .= empty($sOrderbySQL) ? "": ",";
        $aOrder = explode(" " , $sOrderBy);
        $sOrderType = count($aOrder) > 1?
          (strtolower($aOrder[1])=="asc" ? "ASC": "DESC") : "ASC";
        $sOrderbySQL .= FlexiModelUtil::parseSQLName($aOrder[0]) . " " . $sOrderType;
      }
      $sSQL .= !empty($sOrderbySQL) ? " order by " . $sOrderbySQL : "";
    }
    
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
    $this->onDoQuery($sTable, $aCond, $aGroupBy, $aOrderby, $sSelect, $iLimit, $iOffset);
    $stmt = $this->_doQuery($sTable, $aCond, $aGroupBy, $aOrderby, $sSelect, $iLimit, $iOffset);
    
    $aResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $aResult;
  }
  /**
   * Get query count
   * @param String $sTable
   * @param array $aCond
   * @param array $aGroupBy
   * @return array 1 row of cnt
   */
  public function doQueryCount($sTable="", $aCond=array(), & $aGroupBy=null) {
    $sResultSQL = $this->getQuery($sTable, $aCond, $aGroupBy, null, "SELECT COUNT(*) AS cnt ");
    
    $xpdo = FlexiModelUtil::getInstance()->getXPDO();
    $this->onQuery($sResultSQL);
    $stmt = $xpdo->query($sResultSQL);
    if ($stmt) {
      $oRow = $stmt->fetch(PDO::FETCH_ASSOC);
      return $oRow;
    } else {
      $aError = $xpdo->errorInfo();
      throw new Exception("Query failed: " . $aError[2] . ":".$sResultSQL);
    }
  }

  public function onDoQuery(&$sTable, &$aCond, &$aGroupBy, &$aOrderby, &$sSelect, &$iLimit, &$iOffset) {}

  public function _doQuery($sTable="", & $aCond=array(), & $aGroupBy=null, & $aOrderby=null, & $sSelect=null, & $iLimit=null, & $iOffset=0) {
    $xpdo = FlexiModelUtil::getInstance()->getXPDO();
    $sSQL = $this->getQuery($sTable, $aCond, $aGroupBy, $aOrderby, $sSelect, $iLimit, $iOffset);
    $this->onQuery($sSQL);
    //echo "sql: " . $sSQL;
    $stmt = $xpdo->query($sSQL);
    if ($stmt) {
      return $stmt;
    } else {
      $aError = $xpdo->errorInfo();
      throw new Exception("Query failed: " . $aError[2] . ":".$sSQL);
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