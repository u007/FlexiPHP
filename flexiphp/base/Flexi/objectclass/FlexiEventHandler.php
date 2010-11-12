<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FlexiEventHandler
 *
 * @author james
 */
abstract class FlexiEventHandler {
  
  public static function trigger($sEvent) {
    return self::eventHook($sEvent);
  }

  abstract static function eventHook($sEvent);
}

?>
