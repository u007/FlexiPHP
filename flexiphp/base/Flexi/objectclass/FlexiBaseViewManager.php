<?php

class FlexiBaseViewManager {
  protected $oView = null;
  protected $oObjectListManager = null;
  public function  __construct($aParam) {
    //parent::__construct($aParam);
  }

  public function setView(FlexiView &$oView) {
    $this->oView = $oView;
  }

  public function setObjectListManager(FlexiObjectListManager $oManager) {
    $this->oObjectListManager = $oManager;
  }

  public function prepareListHeader() {
    if (is_null($this->oView)) throw new Exception("View not set");
    if (is_null($this->oObjectListManager)) throw new Exception("ObjectListManager not set");
    
    $oObject = $this->oObjectListManager->getObject();
    $this->oView->addVar("aFieldHeader", $this->renderFieldsListHeader($oObject));
  }

  public function prepareForm($oRow, $sType) {
    $oObject = $this->oObjectListManager->getObject();
    $this->oView->addVar("aFieldsInput", $this->renderFieldsInput($oObject, $oRow, $sType));
  }

  public function renderFieldsListHeader(FlexiTableObject $oTable) {
    $aResult = array();
    $sTable = $oTable->getTableName();
    foreach($oTable->aChild["field"] as $sName => & $oField) {
      if (!$this->onBeforeRenderFieldHeader($oField)) continue;
      
      if ($oField->canlist) {
        $sOutput = $this->renderFieldHeader($oField);
        $this->onAfterRenderFieldHeader($sOutput, $oField);
        $aResult[$oField->sName] = $sOutput;
      }
    }
    return $aResult;
  }

  public function renderFieldHeader(FlexiTableFieldObject $oField) {
    return $oField->label;
  }
  
  
  /**
   * return true to render field to list
   * @param FlexiTableFieldObject $oField
   * @return boolean
   */
  public function onBeforeRenderFieldHeader(FlexiTableFieldObject &$oField) { return true; }
  public function onAfterRenderFieldHeader(& $sOutput, FlexiTableFieldObject & $oField) {}

  /**
   * return true to render field to list
   * @param FlexiTableFieldObject $oField
   * @return boolean
   */
  public function onBeforeRenderListField(FlexiTableFieldObject &$oField) { return true; }
  public function onAfterRenderListField(& $sOutput, FlexiTableFieldObject & $oField) {}

  
  /**
   * Render input fields
   * @param FlexiTableObject $oTable : object schema
   * @param array $oRow : data in array
   * @param String $sType : insert / update
   * @return array()
   */
  public function renderFieldsInput(FlexiTableObject $oTable, $oRow, $sType) {
    $aResult = array();
    $sTable = $oTable->getTableName();
    foreach($oTable->aChild["field"] as $sName => & $oField) {
      if (!$this->onBeforeRenderFieldInput($oField, $sType)) continue;
      
      $sCheck = "can" . $sType;
      if ($oField->$sCheck) {
        $sOutput = $this->renderFieldInputForm($oField, $oRow, $sType);
        $sLabel  = $this->renderFieldInputLabel($oField, $sType);
        
        $this->onAfterRenderFieldInput($sOutput, $sLabel, $oField, $oRow, $sType);
        $aResult[$oField->sName] = array("label" => $sLabel, "input" => $sOutput);
      }
    }
    return $aResult;
  }

  public function renderFieldInputLabel(FlexiTableFieldObject $oField, $sType) {
    $sLabel = $this->getFieldInputLabel($oField, $sType);

    $sInputName = "input" . $sType;
    $sFormInput = $oField->$sInputName;
    $bRender = true;
    switch ($sFormInput) {
      case "edit":
        $bRender = true;
        break;
      case "readonly":
        $bRender = true;
        break;
      case "hidden":
        $bRender = false;
        break;
    }

    if (is_null($sLabel)) return "";
    if (strlen($sLabel)==0) return "";
    if (! $bRender) return "";
    return "<label for=\"field" . $oField->getName() . "\">" . $sLabel . "</label>";
  }

  public function getFieldInputLabel(FlexiTableFieldObject $oField, $sType) {
    $sLabel = $oField->label;
    $this->onInputFieldLabel($sLabel, $oField, $sType);
    return $sLabel;
  }

  public function renderFieldInputForm(FlexiTableFieldObject $oField, $oRow, $sType) {
    $sInputName = "input" . $sType;
    $sFormInput = $oField->$sInputName;
    switch ($sFormInput) {
      case "edit":
        $oForm = $this->getFieldInput($oField, $oRow);
        $this->onRenderFieldInput($oForm, $oField, $oRow, $sType);
        $sOutput = $this->oView->renderMarkup($oForm, $oField->sName);
        break;
      case "readonly":
        $sOutput = $oRow->getFieldDisplay($oField, $oRow);
        break;
      case "hidden":
        $oFieldConfig = clone($oField);
        $oFieldConfig->type = "hidden";
        $oForm = $this->getFieldInput($oFieldConfig, $oRow);
        $this->onRenderFieldInput($oForm, $oField, $oRow, $sType);
        $sOutput = $this->oView->renderMarkup($oForm, $oField->sName);
        break;
    }
    
    return $sOutput;
  }

  public function getFieldDisplay(FlexiTableFieldObject $oField, $oRow) {
    $sName = $oField->getName();
    $mValue = $oRow[$sName];
    
    switch($oField->type) {
      case "html":
        return $mValue;
      default:
    }
    return strip_tags($mValue);
  }
  
  public function getFieldInput(FlexiTableFieldObject $oField, $oRow) {
    $sName = $oField->getName();
    $aResult = array(
      "#name"           => "field" . $sName,
      "#title"          => $oField->label,
      "#required"       => $oField->cannull==1? false: true,
      "#default_value"  => $oField->getPHPDefaultValue(),
      "#dbfield"        => $sName,
      "#insert"         => $oField->caninsert,
      "#update"         => $oField->canupdate,
    );
    switch($oField->type) {
      case "string":
      case "int":
      case "tinyint":
      case "money":
      case "decimal":
      case "double":
        $aResult["#type"] = "textfield.raw";
        break;
      case "html":
        $aResult["#type"] = "html.raw";
        break;
      case "text":
        $aResult["#type"] = "textarea.raw";
        break;
      case "json":
        $aResult["#type"] = "textarea.raw";
        break;
      case "date":
        $aResult["#type"] = "date.raw";
        break;
      case "datetime":
        $aResult["#type"] = "datetime.raw";
        break;
      case "hidden":
        $aResult["#type"] = "hidden.raw";
        break;
      default:
        throw new Exception("Unsupported type: " . $oField->type);
    }

    if (isset($oRow[$sName])) {
      $aResult["#value"] = $oRow[$sName];
    }

    return $aResult;
  }

  /**
   * to change field label event
   * @param String $sLabel
   */
  public function onInputFieldLabel(& $sLabel, FlexiTableFieldObject &$oField, $sType) {}
  /**
   * return true to continue, or false to not render
   * @param FlexiTableFieldObject $oField
   * @param String $sType
   * @return boolean
   */
  public function onBeforeRenderFieldInput(FlexiTableFieldObject &$oField, $sType) { return true; }
  public function onRenderFieldInput(& $aField, FlexiTableFieldObject &$oField, $oRow, $sType) {}
  public function onAfterRenderFieldInput(& $sOutput, &$sLabel, FlexiTableFieldObject & $oField, $oRow, $sType) {}
}
