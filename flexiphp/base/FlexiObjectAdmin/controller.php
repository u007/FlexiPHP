<?php

class FlexiObjectAdminController extends FlexiAdminBaseController {
  public $sPath = "";
  
  public function onInit() {
    $oService = $this->getService("FlexiObject");
    $oService->setPath(dirname(__FILE__) . "/models");
    //$this->checkSetup();
  }
  public function methodDefault() {
    return $this->runControl("home");
  }

  public function methodHome() {
    $this->methodList();
    $data = get_object_vars($this->openTempObject());
    $this->oView->addVar("oObject", $data);
    return true;
  }

  public function openTempObject() {
    $oService = $this->getService("FlexiObject");
    if ($oService->exists("_temp")) {
      return $oService->load("_temp");
    } else {
      return new FlexiTableObject("", "");
    }
  }

  public function methodSave() {
    $this->disableView();
    $oService = $this->getService("FlexiObject");
    $aData = $this->getAllRequest();
    try {
      if (empty($aData["txtActualName"])) {
        $oObject = $this->openTempObject();
        $oObject->iVersion = 0;
      } else {
        $oObject = $oService->load($aData["txtActualName"]);
      }
      
      $oObject->sName         = $aData["txtName"];
      $oObject->sTableName    = $aData["txtTableName"];
      
      $oObject->iVersion = empty($oObject->iVersion) ? 1: $oObject->iVersion+1;
      //echo "v: " . $oObject->iVersion;
      $oObject->clearFields();
      $aCount = explode(",", $aData["txtPosition"]);
      foreach($aCount as $c) {
        if (!empty($aData["txtFieldName$c"])) {
          
          $oField = new FlexiTableFieldObject($aData["txtFieldName$c"]);
          
          $oField->oldname      = $aData["txtFieldOldName$c"];
          $oField->oldtype      = $aData["txtFieldOldType$c"];

          $oField->iStatus      = $aData["txtFieldStatus$c"];
          $oField->label        = $aData["txtFieldLabel$c"];

          $oField->autonumber   = $aData["txtFieldAutoNumber$c"];
          
          $oField->precision    = $aData["txtFieldPrecision$c"];
          $oField->cannull      = $aData["txtFieldCanNull$c"];
          $oField->default      = $aData["txtFieldDefault$c"];
          
          $oField->primary      = $aData["txtFieldPrimary$c"];
          $oField->unique       = $aData["txtFieldUnique$c"];


          $oField->caninsert       = $aData["txtFieldCanInsert$c"];
          $oField->canupdate       = $aData["txtFieldCanUpdate$c"];
          $oField->inputinsert       = $aData["txtFieldInputInsert$c"];
          $oField->inputupdate       = $aData["txtFieldInputUpdate$c"];
          
          $oField->canlist       = $aData["txtFieldCanList$c"];

          $oField->type         = $aData["txtFieldType$c"];
          //makesure this is after type
          $oField->allowhtml       = $aData["txtFieldAllowHTML$c"];
          $oField->allowtag       = $aData["txtFieldAllowTag$c"];

          $oField->formsize       = $aData["txtFieldFormSize$c"];
          //type should be last, to overwrite precision, default and dbtype

          $oObject->addField($oField);
        }
      }

      $oService->syncObject($oObject);
      $oService->store($oObject);
      echo $this->returnJSON(true, $oObject->toArray(), "Saved");
    } catch (Exception $e) {
      echo $this->returnJSON(false, null, $e->getMessage());
    }
    
    return true;
  }

  public function methodAjaxSync() {
    $this->disableView();
    $sName = $this->getRequest("name");
    $oService = $this->getService("FlexiObject");

    try {
      $oService->sync($sName);
      echo $this->returnJSON(true, null, "Sync completed");
      return true;
    } catch (Exception $e) {
      echo $this->returnJSON(false, null, $e->getMessage());
    }

    return false;
  }

  public function methodAjaxDelete() {
    $this->disableView();
    $sName = $this->getRequest("name");
    $oService = $this->getService("FlexiObject");

    try {
      $oObject = $oService->load($sName);
      //$oService->sync($sName);
      $oService->deleteTable($oObject);
      $oService->delete($sName);
      echo $this->returnJSON(true, null, "Deleted");
      return true;
    } catch (Exception $e) {
      echo $this->returnJSON(false, null, $e->getMessage());
    }

    return false;
  }
  public function methodAjaxLoad() {
    $this->disableView();
    $sName = $this->getRequest("name");
    $oService = $this->getService("FlexiObject");

    try {
      $oObject = $oService->load($sName)->toArray();
      echo $this->returnJSON(true, $oObject);
      return true;
    } catch (Exception $e) {
      echo $this->returnJSON(false, null, $e->getMessage());
    }
    
    return false;
  }

  public function getObjectArray($oObject) {
    //$result = get_object_vars($oObject);
    return get_object_vars($oObject);
  }

  public function methodAjaxList() {
    $this->disableView();
    $aList = $this->getListArray();
    echo $this->returnJSON(true, $aList);
    return true;
  }

  public function methodAjaxSQLLog() {
    $this->disableView();
    $oService = $this->getService("FlexiObject");
    $sLog = realpath($oService->sLogPath . "/sql.log");

    if (empty($sLog)) {
      echo $this->returnJSON(true, "");
    } else {
      //$sContent = file_get_contents($sLog);
      $sContent = implode("", FlexiFileUtil::getTail($sLog,50));
      //var_dump($sContent);
      $sContent = str_replace("\r\n", "\n", $sContent);

      $aContent = explode(";\n", $sContent);
      $aContent = array_reverse($aContent);
      if (trim($aContent[0]) == "") {
        array_shift($aContent);
      }
      $sContent = implode(";\n\n", $aContent);
      $sContent .= !empty($sContent)? ";\n": "";
      $sContent = str_replace("\n", "<br/>\n", $sContent);

      //$sContent = str_replace("\n", "\n<br/>", $sContent);
      echo $this->returnJSON(true, $sContent);
    }
    return true;
  }

  public function getListArray() {
    $oService = $this->getService("FlexiObject");
    $aList = $oService->fetchAll("", false);
    $aField = array(); $aResult = array();
    foreach($aList as $oObject) {
      $aResult[] = $oObject->toArray();
    }
    return $aResult;
  }

  public function methodList() {
    $this->oView->addVar("aList", $this->getListArray());
  }


  public function setup() {
    
  }


}