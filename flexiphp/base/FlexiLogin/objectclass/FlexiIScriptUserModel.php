<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FlexiIScriptUserEvent
 *
 * @author james
 */
class FlexiIScriptUserModel extends Flexi{
    //put your code here
  public function onUpdate($oModel) {
    FlexiLogger::error(__METHOD__, "Model: " . $oModel->id);
    $aForm = self::getFormFields();
    FlexiModelUtil::validateForm($aForm, $oModel);
  } //onUp

  //TODO
  public static function getFormFields($sExclude = "") {
    static $aForm = null;
    if ($aForm == null) {
    }
    return $aForm;
  }
}
?>
