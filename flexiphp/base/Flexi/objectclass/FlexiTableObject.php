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

  public function isValid() {
    try {
      $this->checkValid();
      return true;
    } catch(Exception $e) {
      return false;
    }
  }

  public function checkValid() {
    
  }
  
  public function getTableName() {
    return $this->sTableName;
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
  
  public function addField(FlexiTableFieldObject $oField) {
    if ($this->existsField($oField->getName())) throw new Exception("Field already exists: " . $oField->getName());
    $this->addChild($oField, "field");
  }
  
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
	  case "Text":
	  case "int":
    case "tinyint":
    case "string":
	  case "double":
	  case "date":
	    return substr(date(FlexiConfig::$sDisplayDateFormat, strtotime($mRaw)),0,10);
	  case "datetime":
	    return date(FlexiConfig::$sDisplayDateFormat, strtotime($mRaw));
	  default:
	    throw new Exception("Unknown field type: " . $sType);
    }
  }
  
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
      $sFieldSQL .= empty($sFieldSQL) ? "": ",\n";
      if ($oField["primary"]) { $sPrimaryField .= (empty($sPrimaryField) ? "": ",") . $oField["name"]; }
      $sSQLDefault = FlexiModelUtil::getDefaultSQL($oField["default"], $oField["cannull"]);
      $sFieldSQL .= FlexiModelUtil::getSQLName($oField["name"]) . " " . 
        strtoupper($oField["dbtype"]) . " " . (empty($oField["precision"])? "": "(" . $oField["precision"] .") ") .
        " " . $sSQLDefault . " " . 
        ($oField["autonumber"]? " AUTO_INCREMENT": "");
    }
    $sSQL .= "\n" . $sFieldSQL . "\n";
    if (!empty($sPrimaryField) ) {
      $aPrimary = explode(",", $sPrimaryField);
      $sPrimarySQL = FlexiModelUtil::getSQLName($aPrimary);
      $sSQL .= "\n,PRIMARY KEY (" . $sPrimarySQL . ")\n";
    }
    $sSQL .= ") ENGINE=InnoDB CHARSET UTF8;";
    return $sSQL;
  }

  public function __sleep()
  {
    //clean up deleted fields
    $aFields = & $this->getAllFields();

    foreach($aFields as $sName => $aValue) {
      if ($aValue->iStatus == 0) {
        $this->delField($sName);
      }
    }
    return array_keys( get_object_vars( $this ) );
  }
  
}