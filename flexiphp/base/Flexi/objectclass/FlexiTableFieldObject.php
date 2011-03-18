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

  protected $rawvalue = null;
  
  public function __construct($sName) {
    parent::__construct($sName, "FlexiTableField");

  }

  public function  __isset($name) {
    return isset($this->$name);
  }

  public function  __set($name, $value) {

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

        if ($sPrecision != $this->precision) $this->precision = $sPrecision;
        if ($sDBType != $this->dbtype) $this->dbtype = $sDBType;
        $value = $sType;
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
    return $this->$name;
  }

  public function __sleep() {
    return array(
      "sName", "type", "label", "dbtype", "precision", "default", "cannull", "autonumber",
      "unique", "oldname", "oldtype", "primary", "caninsert", "canupdate", "inputinsert", "inputupdate");
  }
}

