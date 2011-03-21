<?php

class FlexiObject {
  //object name, and type
  public $sName 		= "";
  public $sType 		= "";
  //object version
  public $iVersion = 1;
  public $iStatus = 1;
  public $aChild = array();
  
  public function __construct($sName, $sType) {
    $this->sName = $sName;
    $this->sType = $sType;
  }

  public function getName() {
    return $this->sName;
  }
  
  public function getType() {
    return $this->sType;
  }

  public function isValid() {
    try {
      $this->checkValid();
      return true;
    } catch (Exception $e) {
      return false;
    }
  }

  public function checkValid() {
    if (empty($this->sName)) {
      throw new Exception("Name is required");
    }
    if (empty($this->iVersion)) {
      throw new Exception("Version is required");
    }
    
    if (!FlexiStringUtil::isCleanName($this->sName)) {
      throw new Exception("Invalid value for name");
    }
  }

  public function addChild(FlexiObject & $oObject, $sType) {
    if (!isset($this->aChild[$sType])) {
      $this->aChild[$sType] = array();
    }
    $sName = $oObject->getName();
    if ($this->existsChild($sName, $sType)) throw new Exception("Child already exists: " . $sName);
    $this->aChild[$sType][$oObject->getName()] = & $oObject;
  }
  
  public function existsChild($sName, $sType) {
    if (!isset($this->aChild[$sType])) {
      return false;
    }
    return isset($this->aChild[$sType][$sName]);
  }

  public function getChild($sName, $sType) {
    if (!$this->existsChild($sName, $sType)) throw new Exception("No such child: " . $sName . "(" . $sType.")");
    return $this->aChild[$sType][$sName];
  }

  public function getChildCount($sType) {
    if (!isset($this->aChild[$sType])) {
      return 0;
    }
    return count($this->aChild[$sType]);
  }

  public function getAllChild($sType) {
    if (!isset($this->aChild[$sType])) {
      return array();
    }
    return $this->aChild[$sType];
  }

  public function clearChild($sType) {
    if (isset($this->aChild[$sType])) {
      unset($this->aChild[$sType]);
    }
    $this->aChild[$sType] = array();
  }

  public function delChild($sName, $sType) {
    if (!$this->existsChild($sName, $sType)) throw new Exception("No such child: " . $sName . "(" . $sType.")");
    unset($this->aChild[$sType][$sName]);
  }
  
}