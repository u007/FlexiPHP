<?php

class FlexiObjectManager extends FlexiBaseObjectManager {

  public function __construct($aParam) {
    parent::__construct($aParam);
    $this->setLogPath(FlexiConfig::$sAssetsDir . "_logs");
  }
  
  public function checkValid(FlexiObject $oObject) {
    if (empty($oObject->sName)) {
      throw new Exception("Name is required");
    }
    if (empty($oObject->sTableName)) {
      throw new Exception("Table name is required");
    }
    if (empty($oObject->iVersion)) {
      throw new Exception("Version is required");
    }
    if (count($oObject->aFields) < 1) {
      throw new Exception("Fields are required");
    }

    if (!FlexiStringUtil::isCleanName($oObject->sName)) {
      throw new Exception("Invalid value for name");
    }

    if (!FlexiStringUtil::isCleanName($oObject->sTableName)) {
      throw new Exception("Invalid value for table name");
    }

    foreach($oObject->aFields as $sName => $aValue) {
      //only check active, none deleted only
      if ($aValue["status"]==1) {
        if ($aValue["dbtype"] == "text") {
          if (!empty($aValue["default"]) && strtolower($aValue["default"]) != "null") {
            throw new Exception("DbType text cannot have default value");
          }
        }
      }
    }

    return false;
  }

  public function sync($sName) {
    $oObject = $this->load($sName);
    return $this->syncObject($oObject);
  }

  public function syncObject(FlexiObject $oObject) {
    if ($oObject->iStatus == 1) {
      if (!FlexiModelUtil::existsTable($oObject->sTableName)) {
        return $this->createTable($oObject);
      } else {
        return $this->updateTable($oObject);
      }
    } else {
      //is delete
      $this->deleteTable($oObject);
    }
  }

  public function deleteTable(FlexiObject $oObject) {
    if (FlexiModelUtil::existsTable($oObject->sTableName)) {
      $sSQL = "DROP TABLE " . FlexiModelUtil::getSQLName($oObject->sTableName) . ";";
      $this->logSQL($sSQL);
      return FlexiModelUtil::getInstance()->getXPDOExecute($sSQL);
    }
  }

  public function createTable(FlexiObject $oObject) {
    $sSQL = $oObject->getSchemaSQL();
    $this->logSQL($sSQL);
    return FlexiModelUtil::getInstance()->getXPDOExecute($sSQL);
  }

  public function updateTable(FlexiObject $oObject) {
    $bDebug = false;
    $aList = FlexiModelUtil::getTableSchema($oObject->sTableName);
    $aFieldSQL = array();
    $aPrimary = array(); $sLastField = "";
    $aLastPrimary = array();
    foreach($oObject->aFields as $oField) {

      //finding field by old name and new name
      $bHasField = false;
      foreach($aList as $oTableField) {
        $sFindName = empty($oField["oldname"]) ? $oField["name"]: $oField["oldname"];
        if ($bDebug) echo "[" . $oTableField["Field"] . "]vs[" . $sFindName . "]";
        if ($oTableField["Field"]==$sFindName) {
          $bHasField = true; break;
        }
      }
      //if old name not found, use new name
      if (!$bHasField && $oField["name"] != $oField["oldname"]) {
        foreach($aList as $oTableField) {
          $sFindName = $oField["name"];
          if ($bDebug) echo "[" . $oTableField["Field"] . "]vs[" . $sFindName . "]";
          if ($oTableField["Field"]==$sFindName) {
            $bHasField = true; break;
          }
        }
      }

      if ($oField["status"]==1) {
        //update or add
        $sSQLDefault = FlexiModelUtil::getDefaultSQL($oField["default"], $oField["cannull"]);
        if ($oField["primary"]) { $aPrimary[] = $oField["name"]; }
        if (strpos($oTableField["Key"], "PRI")!==false) {
          $aLastPrimary[] = $oTableField["Field"];
        }

        if ($bHasField) {
          $sFieldSQL = "CHANGE COLUMN " . FlexiModelUtil::getSQLName($sFindName) .
            " " . FlexiModelUtil::getSQLName($oField["name"]) . " " . strtoupper($oField["dbtype"]) . ""
            . (empty($oField["precision"])? " ": "(" . $oField["precision"] . ") ") .
            " " . $sSQLDefault . " " .
            ($oField["autonumber"]? "AUTO_INCREMENT": "");
        } else {
          $sFieldSQL = "ADD COLUMN "
            . FlexiModelUtil::getSQLName($oField["name"]) . " " . strtoupper($oField["dbtype"]) . ""
            . (empty($oField["precision"])? " ": "(" . $oField["precision"] . ") ") .
            " " . $sSQLDefault . " " .
            ($oField["autonumber"]? " AUTO_INCREMENT": "");
        }
        $sFieldSQL .= empty($sLastField) ? " FIRST ": " AFTER " . FlexiModelUtil::getSQLName($sLastField);
        $aFieldSQL[] = $sFieldSQL;
        $sLastField = $oField["name"];
      } else {
        //do delete
        if ($bHasField) {
          $aFieldSQL[] = "DROP COLUMN " . FlexiModelUtil::getSQLName($sFindName);
        }
      }
      
    }//for

    $sSQL = "ALTER TABLE " . FlexiModelUtil::getSQLName($oObject->sTableName) . "\n";
    
    $sSQL .= implode(",\n", $aFieldSQL);
    
    if (!empty($aPrimary) ) {
      $bIsSame = false;
      if (!empty($aLastPrimary)) {
        foreach($aPrimary as $sPKey) {
          $bFoundKey = false;
          foreach($aLastPrimary as $sLastKey) {
            if ($sLastKey==$sPKey) {
              $bFoundKey = true; $bIsSame = true;
              break;
            }
          }
          //once found a single not same, time to redo primary key
          if (!$bFoundKey) { $bIsSame = false; break; }
        }
      }
      if (!$bIsSame) {
        $sPrimarySQL = FlexiModelUtil::getSQLName($aPrimary);
        if (!empty($aLastPrimary)) $sSQL .= "\n,DROP PRIMARY KEY";
        $sSQL .= "\n,ADD PRIMARY KEY (" . $sPrimarySQL . ")";
      }
    } else {
      //new no primary key
      if (!empty($aLastPrimary)) {
        //last has primary, time to drop it
        $sSQL .= "\n,DROP PRIMARY KEY";
      }
    }

    $sSQL .= ";";
    FlexiLogger::info(__METHOD__, "SQL: " . $sSQL);
    $this->logSQL($sSQL);
    return FlexiModelUtil::getInstance()->getXPDOExecute($sSQL);
  }

  public function delete($sName) {
    $sFile = $sName . ".object.php";
    return parent::delete($sFile);
  }

  public function store(FlexiObject $oObject) {

  	//todo, how to protect file from opened from outside?
    $this->checkValid($oObject);
    
    $sFile = $oObject->getName() . ".object.php";
    return parent::store($sFile, $oObject, "");
  }

  public function load($sName) {
    $sFile = $sName . ".object.php";
    return parent::load($sFile);
  }

  public function exists($sName) {
    $sFile = $sName . ".object.php";
    return parent::exists($sFile);
  }

  public function logSQL($sSQL) {
    $this->doLog($sSQL, "sql");
  }

}