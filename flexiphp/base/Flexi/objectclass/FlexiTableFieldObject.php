<?php

class FlexiTableFieldObject extends FlexiObject {
  protected $type = "";
  protected $label = "";
  protected $dbtype = "";
  protected $precision = "";
  protected $default = "";
  protected $cannull = 1;
  protected $autonumber = 0;
  protected $unique = 0;
  protected $oldname = "";
  protected $oldtype = "";
  protected $primary = 0;

  protected $caninsert = 1;
  protected $canupdate = 1;
  protected $inputinsert = "edit"; //edit/readonly/hidden
  protected $inputupdate = "edit";

  protected $canlist   = 1;
  protected $allowhtml = 0;
  //refer: FlexiBaseViewManager::getFieldSafeTags
  protected $allowtag = ""; //basic / advanced / safe / all / noscript / noframe / noobject / ...

  protected $formsize = "25";
  protected $linkname = "";
  protected $options  = "";

  protected $unsigned = false;

  protected $rawvalue = null;
  protected $aCachedOption = array();
  
  public function __construct($sName) {
    parent::__construct($sName, "FlexiTableField");
    $this->label = $sName;
  }

  public function  __isset($name) {
    return isset($this->$name);
  }

  public function getPHPDefaultValue() {
    $bDebug = false;
    if (!is_null($this->default) && strlen($this->default) != 0) {
      $sScript = "return " . $this->default . ";";
    } else {
      $sScript = "return '';";
    }

    if ($bDebug) echo "script: " . $sScript . "<br/>\n";
    return eval($sScript);
  }

  public function  __set($name, $value) {
    //echo __METHOD__ . ": " . $name . "=" . $value . "\n";
    switch($name) {
      case "dbtype":
        throw new Exception("Cannot directly set dbtype");
        break;
      case "cannull":
      case "autonumber":
      case "unique":
      case "primary":
      case "caninsert":
      case "canupdate":
      case "allowhtml":
      case "canlist":
        if ($value != 1 && !empty($value)) throw new Exception("Invalid " . $name . ": ". $value);
        break;
      case "type":
        $sType = $value;
        $sPrecision = $this->precision;
        $sDBType = empty($this->dbtype) ? "varchar": $this->dbtype;
        switch(strtolower($sType)) {
          case "email":
            $sDBType = "varchar";
            if (empty($sPrecision)) $sPrecision = "255";
            break;
          case "select-char":
            $sDBType = "char";
            if (empty($sPrecision)) $sPrecision = "1";
            break;
          case "select-tinyint":
            $sDBType = "tinyint";
            break;
          case "select-smallint":
            $sDBType = "smallint";
            break;
          case "select-int":
            $sDBType = "int";
            break;
          case "select-bigint":
            $sDBType = "bigint";
            break;
          case "select-text":
            $sDBType = "varchar";
            if (empty($sPrecision)) $sPrecision = "100";
            break;
          case "select-enum":
            $sDBType = "enum";
            break;
          case "html":
            $sDBType = empty($sPrecision) && $sPrecision <=1000 ? "varchar": "text";
            if (empty($sPrecision)) $sPrecision = "255";
            break;
          case "html-tiny":
            $sDBType = "tinytext";
            break;
          case "html-medium":
            $sDBType = "mediumtext";
            break;
          case "html-long":
            $sDBType = "longtext";
            break;
          
          case "string":
            $sDBType = "varchar";
            if (empty($sPrecision)) $sPrecision = "255";
            break;
          case "json":
            $sDBType = "varchar";
            if (empty($sPrecision)) $sPrecision = "500";
          case "char":
            $sDBType = "char";
            if (empty($sPrecision)) $sPrecision = "1";
            break;
          case "json-tiny":
            $sDBType = "tinytext";
            break;
          case "json-medium":
            $sDBType = "mediumtext";
            break;
          case "json-long":
            $sDBType = "longtext";
            break;
          case "text":
              $sDBType = "text";
              //if (empty($sPrecision)) $sPrecision = "255";
              break;
          case "text-tiny":
            $sDBType = "tinytext";
            break;
          case "text-medium":
            $sDBType = "mediumtext";
            break;
          case "text-long":
            $sDBType = "longtext";
            break;
          case "int":
            $sDBType = "int";
            if (empty($sPrecision)) $sPrecision = "11";
            break;
          case "tinyint":
            $sDBType = "tinyint";
            break;
          case "smallint":
            $sDBType = "smallint";
            break;
          case "mediumint":
            $sDBType = "mediumint";
            break;
          case "bigint":
            $sDBType = "bigint";
            break;
          case "double":
            $sDBType = "double";
            break;
          case "decimal":
            $sDBType = "decimal";
            break;
          case "float":
            $sDBType = "float";
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
          case "timestamp":
            $sDBType = "timestamp";
            break;
          case "timestamp-int":
            $sDBType = "int";
            if (empty($sPrecision)) $sPrecision = "11";
          case "file-varchar":
            $sDBType = "varchar";
            if (empty($sPrecision)) $sPrecision = "255";
            break;
          case "file-text":
            $sDBType = "text";
            break;
          case "hidden":
            break;
          default:
            throw new Exception("Unknown field type: " . $sType);
        }
        
        if ($sPrecision != $this->precision) $this->precision = $sPrecision;
        if ($sDBType != $this->dbtype) $this->dbtype = $sDBType;
        $value = $sType;
        break;
        
      case "allowhtml":
        //overwrite for html type input
        $value = $this->type == "html" ? 1: $value;
        break;
    } //switch field name
    

    $this->$name = $value;

    if ($name == "options") {
      $this->loadOptions();
    }
  }

  /**
   * exposes all variables
   */
  public function toArray() {
    return get_object_vars($this);
  }

  public function  __get($name) {
    if ($name == "linkname" && empty($this->linkname)) {
      return $this->getName();
    }
    return $this->$name;
  }

  public function addOption($sKey, $sValue=null) {
    if (!is_null($sValue) || !$this->existsOption($sKey)) {
      $sLabel = is_null($sValue) ? $sKey: $sValue;
      $this->aCachedOption[$sKey] = $sLabel;
      $this->saveOptions();
    }
    return $this->options;
  }

  public function existsOption($sKey) {
    $aOption = $this->getOptions();
    if (isset($aOption[$sKey])) {
      return true;
    }
    return false;
  }

  public function getOptionLabel($sKey) {
    if (isset($this->aCachedOption[$sKey])) {
      return $this->aCachedOption[$sKey];
    }
    return null;
  }

  public function getEnum() {
    $aOption = $this->getOptions();
    //var_dump($aOption);
    $aValue = array_keys($aOption);
    for($c = 0; $c < count($aValue); $c++) {
      $aValue[$c] = "'" . $aValue[$c] . "'";
    }
    return implode(",", $aValue);
  }
  
  public function getOptions() {
    if (count($this->aCachedOption) < 1) $this->loadOptions ();
    return $this->aCachedOption;
  }

  public function saveOptions() {
    $aOption = $this->getOptions();
    $this->options = "";
    foreach($aOption as $sKey => $sValue) {
      $this->options .= empty($this->options) ? "": "\n";
      $this->options .= $sKey ."=".$sValue;
    }
    return $this->options;
  }

  public function clearOtherOption($aKeys) {
    $aOption = $this->getOptions();
    foreach($aOption as $sKey => $sValue) {
      if (!in_array($sKey, $aKeys)) {
        unset($this->aCachedOption[$sKey]);
      }
    }
    $this->saveOptions();
  }

  public function loadOptions() {
    if (trim($this->options."")=="") {
      $this->aCachedOption = array();
      return $this->aCachedOption;
    }
    $aOptions = explode("\n", $this->options);
    $aResultOptions = array();
    foreach($aOptions as $sOption) {
      $aOption = explode("=", $sOption);
      $sKey = $aOption[0];
      $sLabel = count($aOption) > 1? $aOption[1]: $sKey;
      $aResultOptions[$sKey] = $sLabel;
    }
    $this->aCachedOption = $aResultOptions;
    return $this->aCachedOption;
  }

  public function __sleep() {
    return array(
      "sName", "type", "label", "dbtype", "precision", "default", "cannull", "autonumber",
      "unique", "oldname", "oldtype", "primary", "caninsert", "canupdate", "inputinsert", 
      "inputupdate", "canlist", "allowhtml", "allowtag", "formsize", "linkname", "options",
      "unsigned");
  }
}

