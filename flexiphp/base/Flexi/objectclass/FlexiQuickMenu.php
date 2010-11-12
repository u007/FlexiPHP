<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class FlexiQuickMenu {
  protected static $aMenu = array();

  public static function add($sLink, $sTitle=null) {
    self::$aMenu[] = array("link" => $sLink, "title" => $sTitle==null? $sLink : $sTitle);
  }

  //TODO REMOVE?
  
  public static function clear() {
    self::$aMenu = array();
  }

  public static function getMenu() {
    return self::$aMenu;
  }
}