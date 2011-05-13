<?php

class FlexiTableObject extends FlexiObject {
  //object name, table name
  public $sTableName = "";
  //object version
  
  public function __construct($sName, $sTable="") {
    parent::__construct($sName, "FlexiTable");
    $this->sTableName = empty($sTable) ? $sName: $sTable;
    $this->aChild["field"] = array();
  }

  public function isValidData($oRow, $sType) {
    try {
      $this->checkValidData($oRow, $sType);
      return true;
    } catch(Exception $e) {
      return false;
    }
  }

  public function checkValid() {
    parent::checkValid();
    
    if (empty($this->sTableName)) {
      throw new Exception("Table name is required");
    }

    if (count($this->getFieldsCount()) < 1) {
      throw new Exception("Fields are required");
    }
    if (!FlexiStringUtil::isCleanName($this->sTableName)) {
      throw new Exception("Invalid value for table name");
    }

    $aFields = & $this->aChild["field"];
    foreach($aFields as $sName => $oField) {
      //only check active, none deleted only
      if ($oField->iStatus==1) {
        if ($oField->dbtype == "text") {
          if (!empty($oField->default) && strtolower($oField->default) != "null") {
            throw new Exception($sName . ": DbType text cannot have default value");
          }
        }
        
        if (!$oField->cannull && strtolower($oField->default) == "null") {
          throw new Exception($sName . ": Default value null on a not null-able field");
        }
      }
    }
  }

  public function checkValidData($oRow, $sType) {
    foreach($this->aChild["field"] as $sName => $oField) {
      //only check active, none deleted only
      if ($oField->iStatus==1) {
        //check nulls
        $sFieldType   = $oField->type;
        $sDBType      = $oField->dbtype;
        $sValue       = $orow[$sField];
        $sLabel       = $oField->label;
        $sField       = $oField->getName();

        if ($sType=="update" && $oField->primary && (!isset($oRow[$sField]) || strlen($oRow[$sField]."") < 1)) {
          throw new Exception("Field " . $oField->label . " is primary therefore, required for update");// . print_r($oRow,true));
        }
        
        if (! $oField->cannull) {

          if ($sType == "insert" && $oField->primary) {
           //is ok, since is primary
          } else if (!isset($oRow[$sField])) {
            $sCanName = "input" . $sType;
            switch($oField->$sCanName) {
              case "readonly":
              case "none":
                //is okay, we dont need it
                break;
              default:
                //we need it!
                throw new Exception("Field " . $oField->label . " is required");
            }
          } else if (strlen($oRow[$sField]."") < 1) {
            throw new Exception("Field " . $oField->label . " is required");
          }
        }

        if (strlen($sValue."") > 0) {
          switch($sDBType) {
            case "tinyint":
            case "int":
              if (!is_int($sValue)) { throw new Exception("Field " . $sLabel . " is not a number"); }
            case "tinyint":
              if ($sValue < -127 || $sValue > 127) { throw new Exception("Field " . $sLabel . " is invalid"); }
            case "double":
            case "decimal":
              if (!is_numeric($sValue)) { throw new Exception("Field " . $sLabel . " is not a number"); }
          }
          
          switch($sFieldType) {
            case "email":
              if (! FlexiStringUtil::isValidEmail($sValue)) { throw new Exception("Field " . $sLabel . " is not a valid email"); }
          }
          
        } //end if
        
        
        
      }//status
    }//foreach fields
    
  }

  public function getFieldByAlias($sName) {
    foreach($this->aChild["field"] as $sField => &$oField) {
      if ($oField->linkname==$sName) {
        return $oField;
      }
    }
    throw new Exception("No such field by alias: " . $sName);
  }

  public function getNewRow() {
    $bDebug = false;
    $oResult = array();
    foreach($this->aChild["field"] as $sName => $oField) {
      $oResult[$sName] = $oField->getPHPDefaultValue();
    }
    if ($bDebug) var_dump($oResult);
    return $oResult;
  }
  
  public function getTableName() {
    return $this->sTableName;
  }

  public function getListFields() {
    $aResult = array();
    foreach($this->aChild["field"] as $sName => $oField) {
      if ($oField->canlist) $aResult[] = $sName;
    }
    return $aResult;
  }

  public function getPrimaryFields() {
    $aResult = array();
    foreach($this->aChild["field"] as $sName => $oField) {
      if ($oField->primary) $aResult[] = $sName;
    }
    return $aResult;
  }

  public function existsField($sName) {
    return $this->existsChild($sName, "field");
  }

  public function getFieldsCount() {
    return $this->getChildCount("field");
  }
  
  public function getField($sName) {
    if (!$this->existsField($sName)) throw new Exception("No such field: " .  $sName);
    return $this->getChild($sName, "field");
  }

  public function getFieldLabel($sName) {
    $oField = $this->getField($sName);
    return $oField->label;
  }

  public function clearFields() {
    $this->clearChild("field");
  }
  
  public function delField($sName) {
    if ($this->existsField($sName)) {
      $oField = & $this->getChild($sName, "field");
      $oField->iStatus = 0;
    } else {
      $oField = new FlexiTableFieldObject($sName);
      $oField->iStatus = 0;
      $this->addField($oField);
    }
  }

  public function setFieldConfig($sField, $sName, $sValue) {
    if (!$this->existsField($sField)) {
      throw new Exception("No such field: " . $sField);
    }

    $this->aChild["field"][$sField]->$sName = $sValue;
  }

  public function setField(FlexiTableFieldObject $oField) {
    if ($this->existsField($oField->getName())) {
      $this->aChild["field"][$oField->getName()] = &$oField;
      return;
    }
    $this->addField($oField, "field");
  }
  public function addField(FlexiTableFieldObject $oField) {
    if ($this->existsField($oField->getName())) throw new Exception("Field already exists: " . $oField->getName());
    $this->addChild($oField, "field");
  }
  //this is not used, to be removed
  /*
  public function getFieldValue($sName) {
    $mRaw = $this->getFieldRawValue($sName);
    $oField = $this->getField($sName);
    $sType = $oField->type;
    if (is_null($mRaw)) return null;
    
    switch(strtolower($sType)) {
      case "html":
        return htmlentities($mRaw);
      case "json":
        return json_decode($mRaw);
      case "text":
      case "int":
      case "tinyint":
      case "string":
      case "double":
        return $mRaw;
      case "date":
        return substr(date(FlexiConfig::$sDisplayDateFormat, strtotime($mRaw)),0,10);
      case "datetime":
        return date(FlexiConfig::$sDisplayDateFormat, strtotime($mRaw));
      default:
	    throw new Exception(__METHOD__ . ":Unknown field type: " . $sType);
    }
  }
  */
  public function getFieldRawValue($sName) {
    $oField = $this->getField($sName); //test existance of field
    return $oField->rawvalue;
  }

  public function getAllFields() {
    return $this->getAllChild("field");
  }

  public function fieldsToArray() {
    $aFields = $this->getAllFields();
    $aResult = array();
    foreach($aFields as $sKey => $oField) {
      $aResult[$sKey] = $oField->toArray();
    }
    return $aResult;
  }

  public function toArray() {
    $result = get_object_vars($this);
    $result["aChild"]["field"] = $this->fieldsToArray();
    return $result;
  }

  public function getSchemaSQL() {
    $sSQL = "CREATE TABLE " . FlexiModelUtil::getSQLName($this->sTableName);
    $sSQL .=" (";
    $sFieldSQL = ""; $sPrimaryField = "";
    $aFields = $this->fieldsToArray();
    
    foreach($aFields as $oField) {
      $oFieldObject = $this->aChild["field"][$oField["sName"]];
      $sFieldSQL .= empty($sFieldSQL) ? "": ",\n";
      if ($oField["primary"]) { $sPrimaryField .= (empty($sPrimaryField) ? "": ",") . $oField["sName"]; }
      $sSQLDefault = FlexiModelUtil::getDefaultSQL($oField["default"], $oField["cannull"]);
      $sFieldSQL .= FlexiModelUtil::getSQLName($oField["sName"]) . " " .
        strtoupper($oField["dbtype"]) . " " . (empty($oField["precision"])? "": "(" . $oField["precision"] .") ") .
        (!empty($oField["options"]) && $oField["dbtype"]=="enum" ? "(" . $oFieldObject->getEnum() .")":"") .
        " " . $sSQLDefault . " " . 
        ($oField["autonumber"]? " AUTO_INCREMENT": "");
    }
    $sSQL .= "\n" . $sFieldSQL . "\n";
    if (!empty($sPrimaryField) ) {
      $aPrimary = explode(",", $sPrimaryField);
      $sPrimarySQL = FlexiModelUtil::getSQLName($aPrimary);
      $sSQL .= "\n,PRIMARY KEY (" . $sPrimarySQL . ")\n";
    }
    $sSQL .= ") ENGINE=InnoDB CHARSET UTF8";
    return $sSQL;
  }

  public function __sleep()
  {
    //clean up deleted fields
    $aFields = & $this->getAllFields();

    foreach($this->aChild["field"] as $sName => $aValue) {
      if ($aValue->iStatus == 0) {
        unset($this->aChild["field"][$sName]);
      }
    }
    return array_keys( get_object_vars( $this ) );
  }
  
}