<?php

class FlexiObjectManager extends FlexiBaseObjectManager {

  public function __construct($aParam=array()) {
    parent::__construct($aParam);
    $this->setLogPath(FlexiConfig::$sAssetsDir . "_logs");
  }
  
  public function checkValid(FlexiTableObject $oObject) {
    return $oObject->checkValid();
  }

  public function import($sName) {
    $oObject = $this->getImport($sName, $sName);
    return $this->store($oObject);
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
      $sSQL = "DROP TABLE " . FlexiModelUtil::getSQLName($oObject->sTableName) . "";
      $this->logSQL($sSQL);
      return FlexiModelUtil::getInstance()->getXPDOExecute($sSQL);
    }
  }

  /**
   * import a table as flexiobject
   * @param String $sName table
   * @return FlexiObject
   */
  public function getImport($sName, $sTable) {
    $bExists = $this->exists($sTable);
    $aField = FlexiModelUtil::getTableSchema($sTable);
    $oObject = $bExists? $this->load($sName): new FlexiTableObject($sName, $sTable);

    $this->doLog("Importing: " . $sName);
    foreach($aField as $oField) {
      $sFieldName = $oField["Field"];
      $oFieldObject = $oObject->existsField($sFieldName)? $oObject->aChild["field"][$sFieldName] :
              new FlexiTableFieldObject($sFieldName);
      $aType = FlexiModelUtil::parseFieldType($oField["Type"]);

      $sType      = $oFieldObject->type;
      $sPrecision = $aType["precision"];
      $sDBType    = $aType["type"];
      $aOptions   = $aType["option"];
      $bUnsigned  = $aType["unsigned"];
      //$this->doLog("Field2: " . $sFieldName . ", type:" . $sDBType. ",option:" . print_r($aOptions,true));
      if (!empty($sType)) {
        $sDefaultType = $this->getFieldInputTypeByDBType($sDBType);
        //check on existing type
        // only special type cannot change to default type
        switch($sType) {
          case "select-tinyint":
          case "select-smallint":
          case "select-int":
          case "select-text":
          case "select-enum":
          case "select-bigint":
            switch($sDBType) {
              case "int":
              case "smallint":
              case "tinyint":
              case "text":
              case "enum":
              case "bigint":
                $sType = "select-" . $sDBType;
                break;
              case "varchar":
                $sType = "select-text";
                break;
              default:
                $sType = $sDefaultType;
                $this->doLog("Field: " . $sFieldName . ",unsupported select for type: " . $sDBType . ", using: " . $sType);
                //throw new Exception ("Unsupported select for type: " . $sDBType);
            }
            break;
          
          case "html":
          case "html-tiny":
          case "html-medium":
          case "html-long":
            switch($sDBType) {
              case "tinytext":
                $sType = "html-tiny";
                break;
              case "mediumtext":
                $sType = "html-medium";
                break;
              case "longtext":
                $sType = "html-long";
                break;
              case "text":
              case "varchar":
                $sType = "html";
                break;
              default:
                $sType = $sDefaultType;
                $this->doLog("Field: " . $sFieldName . ",unsupported html for type: " . $sDBType . ", using: " . $sType);
                //throw new Exception ("Unsupported html for type: " . $sDBType);
            }
            break;

          case "json":
          case "json-tiny":
          case "json-medium":
          case "json-long":
            switch($sDBType) {
              case "tinytext":
                $sType = "json-tiny";
                break;
              case "mediumtext":
                $sType = "json-medium";
                break;
              case "longtext":
                $sType = "json-long";
                break;
              case "text":
              case "varchar":
                $sType = "json";
                break;
              default:
                $sType = $sDefaultType;
                $this->doLog("Field: " . $sFieldName . ",unsupported JSON for type: " . $sDBType . ", using: " . $sType);
                //throw new Exception ("Unsupported json for type: " . $sDBType);
            }
            break;

           case "money":
            switch($sDBType) {
              case "decimal":
              case "float":
              case "double":
                $sType = "money";
                break;
              default:
                $sType = $sDefaultType;
                $this->doLog("Field: " . $sFieldName . ",unsupported money for type: " . $sDBType . ", using: " . $sType);
                //throw new Exception ("Unsupported money for type: " . $sDBType);
            }
            break;

          //default to direct field form input = dbtype
          default:
            $sType = $sDefaultType;
        }//existing type
        
      } else {
        $sType = $this->getFieldInputTypeByDBType($sDBType);
      }
      //$this->doLog("type: " . $sDBType);
      //set precision 1st
      $oFieldObject->precision  = $sPrecision;
      $oFieldObject->type       = $sType;
      $oFieldObject->unsigned   = $bUnsigned;
      if ($sDBType == "enum" && count($aOptions) > 0) {
        $aEnum = array();
        foreach($aOptions as $sValue) {
          //remove start and tailing '
          $sOption = substr($sValue,1,-1);
          $aEnum[] = $sOption;
          $oFieldObject->addOption($sOption);
          //$this->doLog("added: " . $sValue . "[" . $oFieldObject->options. "]");
        }
        $oFieldObject->clearOtherOption($aEnum);
        //$this->doLog("options: " . $oFieldObject->options);
      }

      $oFieldObject->cannull    = $oField["Null"]=="YES";
      $oFieldObject->primary    = $oField["Key"] == "PRI";
      $oFieldObject->default    = is_null($oField["Default"])? null: "'".$oField["Default"]."'";
      $oFieldObject->autonumber = strpos($oField["Extra"],"auto_increment")!==false;
      
      $oObject->aChild["field"][$sFieldName] = $oFieldObject;
    }
    
    return $oObject;
  }

  public function getFieldInputTypeByDBType($sDBType) {
    switch($sDBType) {
      case "varchar":
        $sType = "string";
        break;
      case "integer":
        $sType = "int";
        break;
      case "tinytext":
        $sType = "text-tiny";
        break;
      case "mediumtext":
        $sType = "text-tiny";
        break;
      case "longtext":
        $sType = "text-tiny";
        break;
      case "tinytext":
        $sType = "text-tiny";
        break;
      case "tinyblob":
        $sType = "blob-tiny";
        break;
      case "mediumblob":
        $sType = "blob-medium";
        break;
      case "longblob":
        $sType = "blob-long";
        break;
      case "enum":
        $sType = "select-enum";
        break;
      case "text":
      case "float":
      case "int":
      case "tinyint":
      case "smallint":
      case "mediumint":
      case "bigint":
      case "decimal":
      case "double":
      case "date":
      case "datetime":
      case "timestamp":
      case "blob":
        $sType = $sDBType;
        break;
      default:
        throw new Exception("Unknown dbtype: " . $sDBType);
    }//dbtype
    return $sType;
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
    foreach($oObject->aChild["field"] as $oFieldObject) {
      $oField = $oFieldObject->toArray();
      //finding field by old name and new name
      $bHasField = false;
      foreach($aList as $oTableField) {
        $sFindName = empty($oField["oldname"]) ? $oField["sName"]: $oField["oldname"];
        if ($bDebug) echo "[" . $oTableField["Field"] . "]vs[" . $sFindName . "]";
        if ($oTableField["Field"]==$sFindName) {
          $bHasField = true; break;
        }
      }
      //if old name not found, use new name
      if (!$bHasField && $oField["sName"] != $oField["oldname"]) {
        foreach($aList as $oTableField) {
          $sFindName = $oField["sName"];
          if ($bDebug) echo "[" . $oTableField["Field"] . "]vs[" . $sFindName . "]";
          if ($oTableField["Field"]==$sFindName) {
            $bHasField = true; break;
          }
        }
      }

      if ($oField["iStatus"]==1) {
        //update or add
        $sSQLDefault = FlexiModelUtil::getDefaultSQL($oField["default"], $oField["cannull"]);
        if ($oField["primary"]) { $aPrimary[] = $oField["sName"]; }
        if (strpos($oTableField["Key"], "PRI")!==false) {
          $aLastPrimary[] = $oTableField["Field"];
        }

        //$this->doLog("Field: " . $oField["sName"] . ", " . $oFieldObject->options . ", enum: " . $oFieldObject->getEnum());

        
        $sFieldTypeSQL = FlexiModelUtil::getSQLName($oField["sName"]) . " " . strtoupper($oField["dbtype"]) . ""
          . (empty($oField["precision"])? "": "(" . $oField["precision"] . ") ") .
          (!empty($oField["options"]) && $oField["dbtype"]=="enum" ? "(" . $oFieldObject->getEnum() .")": "") .
          " " . $sSQLDefault . " " .
          ($oField["autonumber"]? "AUTO_INCREMENT": "");

        if ($bHasField) {
          $sFieldSQL = "CHANGE COLUMN " . FlexiModelUtil::getSQLName($sFindName) . " " .
            $sFieldTypeSQL ;
        } else {
          $sFieldSQL = "ADD COLUMN " . $sFieldTypeSQL;
        }
        $sFieldSQL .= empty($sLastField) ? " FIRST ": " AFTER " . FlexiModelUtil::getSQLName($sLastField);
        $aFieldSQL[] = $sFieldSQL;
        $sLastField = $oField["sName"];
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

    //$sSQL .= ";";
    FlexiLogger::info(__METHOD__, "SQL: " . $sSQL);
    $this->logSQL($sSQL);
    return FlexiModelUtil::getInstance()->getXPDOExecute($sSQL);
  }

  public function delete($sName) {
    $sFile = $sName . ".object.php";
    return parent::delete($sFile);
  }

  public function store(FlexiObject $oObject) {
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
    $this->doLog($sSQL.";", "sql");
  }

}