<?php

abstract class FlexiBaseService {
  protected $oActiveController = null;

  public function __construct(& $controller, $aParam=array()) {
    $this->oActiveController = & $controller;
    $this->init($aParam);
  }

  abstract public function init($aParam);
}