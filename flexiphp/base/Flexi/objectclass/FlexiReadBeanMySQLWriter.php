<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FlexiReadBeanSQLWriter
 *
 * @author james
 */
class FlexiReadBeanMySQLWriter extends RedBean_QueryWriter_MySQL {


  public function getIDField( $type ) {

    if (gettype($type) != "string") {
      throw new FlexiException("Type must be string: " . gettype($type), ERROR_UNKNOWNTYPE);
    }
    FlexiLogger::debug(__METHOD__, "Getting id: " . $type);
    if (isset(FlexiModelUtil::$aTableId[$type])) {
      //FlexiLogger::info(__METHOD__, "Is set: " . FlexiModelUtil::$aTableId[$type]);
      return FlexiModelUtil::$aTableId[$type];
    }
    return parent::getIDField($type);
  }

}
?>
