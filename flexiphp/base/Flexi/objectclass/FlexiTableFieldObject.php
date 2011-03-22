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

  protected $rawvalue = null;
  
  public function __construct($sName) {
    parent::__construct($sName, "FlexiTableField");

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
        if ($value != 1 && !empty($value)) throw new Exception("Invalid " . $name . ": ". $value);
        break;
      case "type":
        $sType = $value;
        $sPrecision = $this->precision;
        $sDBType = empty($this->dbtype) ? "varchar": $this->dbtype;
        switch(strtolower($sType)) {
          case "select-tinyint":
            $sDBType = "tinyint";
            break;
          case "select-int":
            $sDBType = "int";
            break;
          case "select-text":
            $sDBType = "varchar";
            if (empty($sPrecision)) $sPrecision = "100";
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
          case "text":
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

  public function __sleep() {
    return array(
      "sName", "type", "label", "dbtype", "precision", "default", "cannull", "autonumber",
      "unique", "oldname", "oldtype", "primary", "caninsert", "canupdate", "inputinsert", 
      "inputupdate", "canlist", "allowhtml", "allowtag", "formsize", "linkname", "options");
  }
}

