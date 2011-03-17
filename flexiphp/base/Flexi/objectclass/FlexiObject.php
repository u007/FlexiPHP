<?php

class FlexiObject {
  //object name, also table name
  public $sName 		= "";
  public $sTableName = "";
  //store field info
  public $aFields 	= array();
  //store field raw value
  public $aFieldValue = array();

  //todo foreign key and sub tables
  public $aLinks = array();
  //object version
  public $iVersion = 1;
  public $iStatus = 1;
  
  public function __construct($sName, $sTable="") {
    $this->sName = $sName;
    $this->sTableName = empty($sTable) ? $sName: $sTable;
  }
  
  public function getName() {
    return $this->sName;
  }
  
  public function getTableName() {
    return $this->sTableName;
  }
  
  public function getFieldValue($sName) {
    $mRaw = $this->getFieldRawValue($sName);
    $sType = $this->aFields[$sName]["type"];
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
    return isset($this->aFieldValue[$sName]) ? $this->aFieldValue[$sName]: null;
  }
  
  public function existsField($sName) {
    return isset($this->aFields[$sName]);
  }
  
  public function getField($sName) {
    if(!isset($this->aFields[$sName])) throw new Exception("No such field: " . $sName);
    return $this->aFields[$sName];
  }
  
  public function getFieldLabel($sName) {
    $oField = $this->getField($sName);
    return $oField["label"];
  }

  public function clearFields() {
    $this->aFields = array();
  }

  public function delField($sName) {
    if ($this->existsField($sName)) {
      $this->aFields[$sName]["status"] = 0;
    } else {
      $this->aFields[$sName] = array(
          "name" => $sName, "status" => 0
      );
    }
  }
  
  public function addField($sName, $sLabel, $sType, $sPrecision=null, $mDefault=null, $bCanNull=true, $bAutoNumber=false, $bPrimary=false, $bUnique=false, $sOldName="", $sOldType="", $iStatus=1) {
    if(isset($this->aFields[$sName])) {
      throw new Exception("Field already exists");
    }
    $sDBType = "varchar"; 
    switch(strtolower($sType)) {
      case "html":
        $sDBType = empty($sPrecision) && $sPrecision <=1000 ? "varchar": "text";
        if (empty($sPrecision)) $sPrecision = "255";
        break;
      case "string":
        $sDBType = "varchar";
        if (empty($sPrecision)) $sPrecision = "255";
        break;
      case "json":
        $sDBType = "varchar";
        if (empty($sPrecision)) $sPrecision = "500";
	  case "Text":
        $sDBType = "text";
        //if (empty($sPrecision)) $sPrecision = "255";
        break;
	  case "int":
	    $sDBType = "int";
	    if (empty($sPrecision)) $sPrecision = "11";
	    break;
    case "tinyint":
	    $sDBType = "tinyint";
	    break;
	  case "double":
	    $sDBType = "double";
      break;
    case "decimal":
      $sDBType = "decimal";
      break;
    case "money":
      $sDBType = "decimal";
	    if (empty($sPrecision)) $sPrecision = "10,2";
	    break;
	  case "date":
	    $sDBType = "date";
	    break;
	  case "datetime":
	    $sDBType = "datetime";
	    break;
	  default:
	    throw new Exception("Unknown field type: " . $sType);
    }
    $this->aFields[$sName.""] = array(
      "name" 		=> $sName,
      "label" 		=> $sLabel,
      "type" 		=> $sType,
      "dbtype" 		=> $sDBType,
      "precision" 	=> $sPrecision,
      "default"		=> $mDefault,
      "cannull"		=> $bCanNull,
      "autonumber" => $bAutoNumber,
      "primary"   => $bPrimary ? 1: 0,
      "unique"   => $bUnique ? 1: 0,
      "oldname"   => $sOldName,
      "oldtype"   => $sOldType,
      "status"    => $iStatus
    );
    
  }

  public function getSchemaSQL() {
    $sSQL = "CREATE TABLE " . FlexiModelUtil::getSQLName($this->sTableName);
    $sSQL .=" (";
    $sFieldSQL = ""; $sPrimaryField = "";
    foreach($this->aFields as $oField) {
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
    foreach($this->aFields as $sKey => $aValue) {
      if ($aValue["status"] == 0) {
        unset($this->aFields[$sKey]);
      }
    }
    return array_keys( get_object_vars( $this ) );
  }
  
}