<?php

class FlexiObjectListManager extends FlexiLogManager {
  protected $oManager = null;
  protected $sRepoPath = "";
  protected $oObject = null;
  protected $oLastSavedRow = null;
  protected $sLastSaveType = "";
  protected $sTableAlias = "";
  protected $aParam = array();
  
  public function __construct($aParam=null) {
    parent::__construct($aParam);
    $this->setPath(FlexiConfig::$sRepositoryDir);
    $this->setLogPath(FlexiConfig::$sAssetsDir . "_logs");
  }
  
  public function getTableAlias() {
    return $this->sTableAlias;
  }
  
  public function setPath($sPath) {
    $this->sRepoPath = $sPath;
  }
  
  public function setObjectName($sName) {
    $this->oObject = $this->getManager()->load($sName);
    $this->onSetObject($this->oObject);
  }

  public function onSetObject(&$oObject) {}

  public function deleteRows($oCond) {
    $sTable = $this->oObject->sTableName;

    $aCond = is_array($oCond)? $oCond: get_object_vars($oCond);
    $aCond = $this->loadConditionByAlias($aCond);
    $aCond = $this->getObjectCond($aCond);
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
      $aResult[FlexiModelUtil::getSQLName($oField->getName())] = $sValue;
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
    $aPrimary = $oObject->getPrimaryFields();
    $oStore = array();
    $oCurrentRow = $sType == "insert" ? array(): $this->getRow($oRow);
    if (! $this->onBeforeStore($oObject, $oRow, $sType)) return false;
    
    foreach($oObject->aChild["field"] as $sField => $oField) {
      if ($bDebug) echo __METHOD__ . ": field: " . $sField . "\n<br/>";
      if ($oField->type == "file-varchar" || $oField->type=="file-text" ||
          $oField->type=="image-varchar" || $oField->type=="image-text" ||
          $oField->type=="multiimage-text") {
        $this->doUploadField($oField, $oStore, $oRow, $oCurrentRow);
      } else {
        if (isset($oRow[$sField])) {
          //if (! $this->onBeforeStoreField($oField, $oRow, $sType)) continue;
          $oStore[$sField] = $this->getFieldDataFromForm($oField, $oRow);
        }
      }
    }
    if( !$this->onStore($oStore, $oObject, $oRow, $sType)) {
      return false;
    }
    return $this->_storeObjectRow($oStore, $sType);
  }
  
  public function getStoredPrimaryValue() {
    $oRow = $this->oLastSavedRow;
    if (is_null($oRow)) throw new Exception("No stored row");
    $oObject = $this->getObject();
    $aPrimary = $oObject->getPrimaryFields();
    $aResult = array();
    foreach($aPrimary as $sName) {
      $aResult[$sName] = isset($oRow[$sName]) ? $oRow[$sName] : "";
      if ($this->sLastSaveType == "insert") {
        if ($this->oObject->aChild["field"][$sName]->autonumber) {
          $aResult[$sName] = FlexiModelUtil::getInstance()->getXPDOLastId();
        }
      }
    }
    return $aResult;
  }

  public function _storeObjectRow($oRow, $sType) {
    $bDebug = false;
    $oObject = $this->getObject();
    $sTable = $oObject->getTableName();
    
    $aPrimary = $oObject->getPrimaryFields();
    $this->oLastSavedRow = $oRow;
    $this->sLastSaveType = $sType;
    switch($sType) {
      case "insert":
        if ($bDebug) echo __METHOD__ . ": Insert " . $sTable . ": " . print_r($oRow,true) . "<Br/>\n";
        return FlexiModelUtil::getInstance()->insertXPDO($sTable, $oRow, $aPrimary);
        break;
      case "update":
        if ($bDebug) echo __METHOD__ . ": Update " . $sTable . ": " . print_r($oRow,true) . "<Br/>\n";
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
  public function getFieldDataFromForm(FlexiTableFieldObject $oField, & $oRow) {
    $bDebug = false;
    if ($bDebug) echo __METHOD__ . ": " . print_r($oRow, true) . "\n<br/>";
    $sName = $oField->getName();
    $mValue = $oRow[$sName];
    
    if ($oField->allowhtml) {
    } else {
      //upload form return an array
      if (! is_array($mValue)) {
        $mValue = strip_tags($mValue);
      } else {
        for($c=0; $c < count($mValue); $c++) {
          $mValue[$c] = strip_tags($mValue[$c]);
        }
      }
    }
    
    if (!empty($mValue) && is_array($mValue)) {
      $mValue = implode($oField->uploadseparator, $mValue);
    }
    
    return $mValue;
  }

  /**
   * upload a field
   * @param FlexiTableFieldObject $oField
   * @param array $oStore
   * @param array $oRow (new form row)
   * @param array $oCurrentRow  (old row)
   */
  public function doUploadField(FlexiTableFieldObject $oField, & $oStore, & $oForm, $oCurrentRow) {
    $sName = $oField->getName();
    
    $sSavePath = is_null($oField->savepath) ?
      FlexiFileUtil::getFullUploadPath("media/libraries"): $oField->savepath;
    //relative path is to cut out prefix of path before saving to field
    $sFullRelativeBasePath = empty($oField->savepath) ? "": realpath($oField->savepath);
    
    //if multiple file
    //var_dump($oField->type);
    if ($oField->type == "multiimage-text") {
      $aCurrentFile = array();
      if (!empty($oCurrentRow[$sName])) {
        $aCurrentFile = explode($oField->uploadseparator, $oCurrentRow[$sName]);
      }
      $aResultFile = array();
      //var_dump($oForm);
      for($c = 1; $c <= $oField->uploadcount; $c++) {
        
        if (isset($oForm[$sName . "_". $c])) {
          $sNewFile = "media." . FlexiStringUtil::createRandomAlphaNumeric() . "_" . time();
          $aStatus = FlexiFileUtil::storeUploadFile($oForm[$sName . "_". $c], $sSavePath, $sNewFile. ".");
          $this->onGetUploadFileName($sSaveDir, $sNewFile);
          
          if ($aStatus["status"]) {
            //replace photo if already exists
            if (! empty($aCurrentFile[$c-1])) {
              unlink(FlexiFileUtil::getFullPathFrom($aCurrentFile[$c-1], $sFullRelativeBasePath));
            }
            if ($oField->isUploadImage() && !empty($oField->maxwidth) || !empty($oField->maxheight)) {
              FlexiImageUtil::imageResize($oField->maxwidth, $oField->maxheight, $aStatus["path"]);
            }
            //if savepath not declared, full path from root is saved
            //  if declared, only save filename
            //  "" => use base root path
            
            //resize image based on max width, height
            //FlexiImageUtil::imageResize(345, 287, $aStatus["path"]);
            $aResultFile[$c-1] = FlexiFileUtil::getRelativePathFrom($aStatus["path"], $sFullRelativeBasePath);
          } else {
            //No file
            $aResultFile[$c-1] = $aCurrentFile[$c-1] ;
          }
        }
      }//for each file
      
      $oStore[$sName] = implode($oField->uploadseparator, $aResultFile);
      
    } else {
      //single file upload
      if (! isset($oForm[$sName])) return;
      //isupload form, presume
      if (is_array($oForm[$sName])) {
        $sNewFile = "media." . FlexiStringUtil::createRandomAlphaNumeric() . "_" . time();
        //var_dump($oRow[$sName]);
        $aStatus = FlexiFileUtil::storeUploadFile($oForm[$sName], $sSavePath, $sNewFile. ".");
        $this->onGetUploadFileName($sSaveDir, $sNewFile);

        if ($aStatus["status"]) {
          //replace photo if already exists
          if (! empty($oCurrentRow[$sName])) {
            $sOldPath = FlexiFileUtil::getRelativePathFrom($oCurrentRow[$sName], $sFullRelativeBasePath);
            unlink($sOldPath);
          }
          if ($oField->isUploadImage() && !empty($oField->maxwidth) || !empty($oField->maxheight)) {
            FlexiImageUtil::imageResize($oField->maxwidth, $oField->maxheight, $aStatus["path"]);
          }
          //if savepath not declared, full path from root is saved
          //  if declared, only save filename
          //  "" => use base root path
          //resize image based on max width, height
          //FlexiImageUtil::imageResize(345, 287, $aStatus["path"]);
          $oStore[$sName]    = FlexiFileUtil::getRelativePathFrom($aStatus["path"], $sFullRelativeBasePath);
        } else {
          //No file
        }
      } else if (is_string($oForm[$sName])) {
        //could be manually saved or from old path
        $sNewFile = $oForm[$sName];
        //delete old file if different from new file
        if (! empty($oCurrentRow[$sName]) && !empty($sNewFile)) {
          $sOldPath = FlexiFileUtil::getFullPathFrom($oCurrentRow[$sName], $sFullRelativeBasePath);
          $sNewPath = FlexiFileUtil::getFullPathFrom($sNewFile, $sFullRelativeBasePath);
          
          $sOldPathReal = realpath($sOldPath);
          if (!empty($sOldPathReal) && $sOldPathReal != realpath($sNewPath)) {
            unlink($sOldPathReal);
          }
        }
        $oStore[$sName]    = FlexiFileUtil::getRelativePathFrom($sNewFile, $sFullRelativeBasePath);
      } else {
        throw new Exception("Invalid upload value: " . $oForm[$sName]);
      }//else error
      
    }//if single file
  }
  //overide to replace save dir and file name
  public function onGetUploadFileName(&$sSaveDir, &$sNewFile) {}

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
    //var_dump($oRow);
    $oObject->checkValidData($oRow, $sType);
    //will not pass here if checkvalid fail as it will throw exception
    $this->onCheckValidData($oObject, $oRow, $sType);
    return true;
  }

  public function onBeforeCheckValidData(& $oRow, &$sType) { return true; }
  public function onCheckValidData($oObject, & $oRow, $sType) {}
  
  /**
   * Get option list as key=>value
   * @param String $sLabelCol Colume name for label
   * @param String $sValue colume name for value
   * @param array $aCond
   * @param array $aGroupBy
   * @param array $aOrderby
   * @param String $sSelect
   * @param int $iLimit
   * @param int $iOffset 
   */
  public function getOptionList($sLabelCol, $sValueCol=null, $aCond=array(), $aGroupBy=null, $aOrderby=null, $sSelect=null, $iLimit=null, $iOffset=0) {
    $aList = $this->doTableQuery($aCond, $aGroupBy, $aOrderby, $sSelect, $iLimit, $iOffset);
    $aResult = array();
    $oObject = $this->getObject();
    
    $aPrimary = $oObject->getPrimaryFields();
    foreach($aList as $oRow) {
      $aKey = array();
      if (empty($sValueCol)) {
        //use primary
        foreach($aPrimary as $sCol) {
          $aKey[] = $oRow[$sCol];
        }
      } else {
        $aKey[] = $oRow[$sValueCol];
      }
      
      $aResult[implode(",", $aKey).""] = $oRow[$sLabelCol];
    }
    return $aResult;
  }
  
  public function doTableQuery(& $aCond=array(), & $aGroupBy=null, & $aOrderby=null, & $sSelect=null, & $iLimit=null, & $iOffset=0) {
    //$sTable = $this->oObject->getTableName();
    return $this->doQuery("", $aCond, $aGroupBy, $aOrderby, $sSelect, $iLimit, $iOffset);
  }

  public function doTableCountQuery(& $aCond=array(), & $aGroupBy=null) {
    //$sTable = $this->oObject->getTableName();
    return $this->doQueryCount("", $aCond, $aGroupBy);
  }
  
  public function getParam($sName) {
    if (isset($this->aParam[$sName])) {
      return $this->aParam[$sName];
    }
    return null;
  }
  
  public function setParams($aParam) {
    $this->aParam = $aParam;
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
    $aFields = $oObject->getPrimaryFields();
    
    return $aFields;
  }
  /**
   * get all fields can list
   * @return array
   */
  public function getListFields() {
    $oObject = $this->oObject;
    return $oObject->getListFields();
  }
  
  public function getQueryListFields() {
    $aFields = $this->getListFields();
    if (!empty($this->sTableAlias)) {
      for($c=0; $c < count($aFields); $c++) {
        $aFields[$c] = (!empty($this->sTableAlias) ? $this->sTableAlias . ".": "") . 
              $aFields[$c];
      }
    }
    return $aFields;
  }
  
  public function getQueryPrimaryFields() {
    $aFields = $this->getPrimaryFields();
    if (!empty($this->sTableAlias)) {
      for($c=0; $c < count($aFields); $c++) {
        $aFields[$c] = (!empty($this->sTableAlias) ? $this->sTableAlias . ".": "") . 
              $aFields[$c];
      }
    }
    return $aFields;
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
    $this->aParam = $aParam;
    $this->onNewRow($oRow, $aParam);
    return $oRow;
  }

  public function onNewRow(& $oRow, $aParam) {}
  
  public function getRows($aCond=array(), $aGroupBy=null, $aOrderby=null, $sSelect=null, $iLimit=null, $iOffset=0) {
    $sTable = $this->oObject->getTableName();
    return $this->doQuery($sTable, $aCond, $aGroupBy, $aOrderby, $sSelect, $iLimit, $iOffset);
  }
  /**
   * Get a row by row value with primary fields
   * @param array $oRow
   * @return array
   */
  public function getRow($oRow) {
    $aPrimary = $this->getPrimaryFields();
    $sTable = $this->oObject->getTableName();
    $aCond = array();
    foreach($aPrimary as $sPrimary) {
      if (!isset($oRow[$sPrimary])) throw new Exception("Primary field missing: " . $sPrimary);
      //echo "cond: " . FlexiModelUtil::getSQLName($sPrimary) . "<Br/>\n";
      $sName = (!empty($this->sTableAlias) ? $this->sTableAlias . ".": "") . 
              FlexiModelUtil::getSQLName($sPrimary);
      $aCond[$sName] = $oRow[$sPrimary];
    }
    $sSQL = $this->getQuery($sTable, $aCond);
    
    return FlexiModelUtil::getInstance()->getXPDOFetchOne($sSQL);
  }
  
  public function getFromQuerySyntax($sTable="") {
    $sTable = empty($sTable) ? FlexiModelUtil::getSQLName($this->oObject->getTableName()): $sTable;
    return $sTable;
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
  public function getQuery($sTable="", $aCond=array(), $aGroupBy=null, $aOrderby=null, $sSelect=null, $iLimit=null, $iOffset=0) {
    $bDebug = false;
    $xpdo = FlexiModelUtil::getInstance()->getXPDO();
    
    $sFromQuery = $this->getFromQuerySyntax($sTable);
    //any auto condition changes here
    //pass to event handler, throw exception to stop process
    //$this->onQueryParam($sTable, $aCond, $aGroupBy, $aOrderby, $sSelect, $iLimit, $iOffset);
    $sSQL = "";
    if (empty($sSelect)) {
      $sSQL = "SELECT * FROM " . $sFromQuery;
    } else {
      $sSQL = $sSelect . " FROM " . $sFromQuery;
    }
    $aParam = array();
    
    $aCond = $this->getObjectCond($aCond);
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
    if ($bDebug) echo __METHOD__ . ": " . $sResultSQL . "<br/>\n";

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
   * Change all direct field condition to table_alias.fieldname
   * @param array key=>value
   */
  public function getObjectCond($aCond) {
    return $aCond; //not used
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
    //echo __METHOD__ . ": select: " . $sSelect . "<br/>\n";
    $xpdo = FlexiModelUtil::getInstance()->getXPDO();
    $sSQL = $this->getQuery($sTable, $aCond, $aGroupBy, $aOrderby, $sSelect, $iLimit, $iOffset);
    $this->onQuery($sSQL);
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