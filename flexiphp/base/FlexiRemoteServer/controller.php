<?php

class FlexiRemoteServerController extends FlexiBaseRemoteController {

  public function methodDefault() {
    return true;
  }

  
  public function serviceSyncTable($oData) {
    FlexiLogger::debug(__METHOD__, "Running");
    //var_dump($aData["type"]);
    //FlexiLogger::error(__METHOD__, print_r($oData, true));
    $aTable = $oData->tables;
    $sType = $oData->type;
    $aMessage = array();
    FlexiLogger::info(__METHOD__, "Sync type: " . $sType);
    $oUtil = FlexiModelUtil::getInstance();
    foreach($aTable as $sTable => $aTableSetting) {
      $aRow = $aTableSetting->rows;
      $sIDField = $aTableSetting->idfield;
      //FlexiLogger::error(__METHOD__, $sTable);

      $oModel = $oUtil->getRedBeanModel($sTable);
      if ($sType == "fullsync") {
        FlexiLogger::info(__METHOD__, "Clearing table: " . $sTable);
        if ($oUtil->getRedBeanDB()->tableExists($sTable)) {
          $oUtil->getRedBeanExecute("delete from " . $sTable);
        }
      }

      if (count($aRow) > 0) {
        $sFields = implode(",", array_keys((array)$aRow[0]));
      }
      
      $oUtil->setRedBeanTableIdField($sTable, $sIDField);
      FlexiLogger::debug(__METHOD__, $sTable . ", fields: " . $sFields);
      foreach($aRow as $oRow) {
        $oRowData = (array)$oRow;

        $oModel = $oUtil->getRedBeanModel($sTable);
//        $sPrimaryField = "id";
//        if (isset($oRowData[$sPrimaryField])) {
//          $oModel = $oUtil->getRedBeanModel($sTable, $oRowData[$sPrimaryField]);
//        } else {
//          $oModel = $oUtil->getRedBeanModel($sTable);
//        }
        //FlexiLogger::info(__METHOD__, "Type: " . $oModel->getMeta("type"));
        $oModel->import($oRowData, $sFields);
        FlexiLogger::info(__METHOD__, "model: " . $sTable . ", values: " . print_r($oModel->export(), true));
        $oUtil->insertOrUpdateRedBean($oModel);
        unset($oModel);
      } //each records

      $aMessage[] = "Imported: " . $sTable . ", cnt: " . count($aRow);
      FlexiLogger::info(__METHOD__, "Imported: " . $sTable . ", cnt: " . count($aRow));
      //if (1==1) { break; }
    } //each table

    $this->unsetToken();
    FlexiLogger::info(__METHOD__, "Done");

    return array("msg" => implode("\r\n<br/>", $aMessage));
  } //function

  public function permission($sMethod) {
    return FlexiConfig::getLoginHandler()->isSuperUser();
//    if (strtolower($sMethod) == "synctable") {
//      return FlexiConfig::getLoginHandler()->hasPermission($sTitle);
//    }
  }

}

?>
