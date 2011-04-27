<?php

class FlexiBaseViewManager {
  protected $oView = null;
  protected $oObjectListManager = null;
  protected $sFieldPrefix = "field";
  protected $sListLinkCol = "id";
  protected $aView = array();
  
  public function  __construct($aParam) {
    //parent::__construct($aParam);
  }

  /**
   * Render a view template of a child/other view
   *  and assign to main view as a variable
   * @param String $sName Child view name
   * @param String $sView view template
   * @param String $sVarName var name for main view
   */
  public function renderChildView($sName, $sView, $sVarName) {
    $sHTML = $this->renderView($sView, $sName);
    $this->oView->addVar($sVarName, $sHTML);
  }
  /**
   * Render a view template,
   *  empty for main view, if sTargetView specified, will be child / other view
   * @param String $sView view template
   * @param String $sTargetView target view object
   * @return String
   */
  public function renderView($sView, $sTargetView="") {
    if (empty($sTargetView)) {
      return $this->oView->render($sView);
    }

    return $this->getView($sTargetView)->render($sView);
  }

  public function onSetView($sName) {}
  /**
   * Set DB Form Prefix, will be used in template
   * @param String $sPrefix
   */
  public function setViewDBFormPrefix($sPrefix) {
    $this->oView->addVar("sViewDBFormPrefix", $sPrefix);
  }

  public function getViewDBFormPrefix() {
    return $this->oView->getVar("sViewDBFormPrefix");
  }

  public function setView(FlexiView &$oView, $sName="") {
    if (empty($sName)) {
      $this->oView = &$oView;
    }
    if (isset($this->aView[$sName])) {
      throw new Exception("View instance already exists: " . $sName);
    }
    $this->aView[$sName] = &$oView;

    $this->onSetView($sName);
  }

  /**
   * Get view instances
   * @param <type> $sName
   * @return <type>
   */
  public function getView($sName="") {
    if (empty($sName)) return $this->oView;
    if (!isset($this->aView[$sName])) {
      throw new Exception("View instance does not exists: " . $sName);
    }
    return $this->aView[$sName];
  }

  /**
   * create new view instances
   *  will copy existing view to populate controller path and module
   * @param String $sName
   */
  public function getNewView($sName) {
    if (is_null($oView)) {
      throw new Exception("Main view not set");
    }
    $oMainView = $this->oView;
    $oView = new FlexiView();
    $oView->addVar("#module", $oMainView->getVar("#module"));
    $oView->addVar("#method", $oMainView->getVar("#method"));
    $oView->addVar("#modulepath", $oMainView->getVar("#modulepath"));
    $oView->addVar("#moduleurl", $oMainView->getVar("#moduleurl"));
    $oView->addVar("#modulepath", $oMainView->getVar("#modulepath"));
    
    $this->setView($oView, $sName);
  }

  public function getListLinkCol($sName) {
    $this->sListLinkCol = $sName;
  }

  public function setFormFieldPrefix($sPrefix) {
    $this->sFieldPrefix = $sPrefix;
  }

  public function getFormFieldPrefix() {
    return $this->sFieldPrefix;
  }

  
  public function setObjectListManager(FlexiObjectListManager $oManager) {
    $this->oObjectListManager = $oManager;
  }
  
  /**
   * Prepare data for listing
   *  work closely with method prepareListHeader
   * @param array $aCond
   * @param array $aGroupBy
   * @param array $aOrder
   * @param String $sSelect
   * @param int $iLimit
   * @param int $iOffset
   */
  public function getQueryDisplayList($aCond, $aGroupBy, $aOrder, $sSelect, $iLimit, $iOffset) {
    //echo __METHOD__ . ": query select: " . $sSelect . "<br/>\n";
    $aList = $this->oObjectListManager->doTableQuery($aCond, $aGroupBy, $aOrder, $sSelect, $iLimit, $iOffset);
    //may add additional fields
    $aResult = array();
    for($c = 0; $c < count($aList); $c++) {
      $oRow = $aList[$c];
      $aPrimary = $this->getPrimaryLink($oRow);
      //$sLink = $this->oView->getVar("sLoadURL") . "&" . http_build_query($aLink);
      $oResultRow = $this->getDisplayRow($oRow);
      $oResultRow["_link"] = $aPrimary["link"];
      $oResultRow["_primary"] = $aPrimary["primary"];
      $oResultRow[$this->sListLinkCol] = "<a href='javascript:' onClick='" . $aPrimary["link"].  "'>" . $oResultRow[$this->sListLinkCol] . "</a>";
      $aResult[] = $oResultRow;
    }
    return $aResult;
  }

  public function getPrimaryLink($oRow) {
    $oTable = $this->oObjectListManager->getObject();
    $aPrimary = $this->oObjectListManager->getPrimaryValue($oRow);
    $aAlias = array();
    foreach($aPrimary as $sField => $sValue) {
      $aAlias[$oTable->aChild["field"][$sField]->linkname] = $sValue;
    }
    $this->onGetPrimaryLink($aAlias);
    $aLink = $this->jsCleanArray($aAlias);
    $sLink = $this->oView->getVar("sViewDBFormPrefix") . "doLoadObject(" . json_encode($aLink) . ")";
    
    return array("primary" => $aAlias, "link" => $sLink);
  }
  /**
   * passed in value is alias name of primary
   * @param array $aPrimary
   */
  public function onGetPrimaryLink(&$aPrimary) {}

  /*
   * not used
  public function getInputRow($oRow, $sType) {
    $aResult = array();
    $oTable = $this->oObjectListManager->getObject();
    foreach($oRow as $sField => $sValue) {
      $aResult[$sField] = $this->getFieldDisplay($oTable->aChild["field"][$sField], $oRow);
    }
    return $aResult;
  }
   */

  public function getDisplayRow($oRow) {
    $aResult = array();
    $oTable = $this->oObjectListManager->getObject();
    foreach($oRow as $sField => $sValue) {
      $aResult[$sField] = $this->getFieldDisplay($oTable->aChild["field"][$sField], $oRow);
    }
    $this->onGetDisplayRow($aResult, $oRow);
    return $aResult;
  }

  public function onGetDisplayRow(&$oResult, $oRow){}
  
  public function jsCleanArray($aValue, $sep="\"") {
    $aResult = array();
    foreach($aValue as $sKey => $sValue) {
      $aResult[$sKey] = str_replace($sep, "\\" . $sep, $sValue);
    }
    return $aResult;
  }

  public function prepareListHeader() {
    if (is_null($this->oView)) throw new Exception("View not set");
    if (is_null($this->oObjectListManager)) throw new Exception("ObjectListManager not set");
    
    $oObject = $this->oObjectListManager->getObject();
    $aHeaderFields = $this->renderFieldsListHeader($oObject);
    $this->oView->addVar("aFieldHeader", $aHeaderFields);
    
    $aListField = $oObject->getListFields();
    $this->onGetFieldList($aListField);
    $this->oView->addVar("aListFieldName", $aListField);
  }

  public function onGetFieldList(&$aListField) {}

  public function getFormToObjectStore($oRow, $sFormType) {
    $bDebug = false;
    $oForm = array();
    $oTable = $this->oObjectListManager->getObject();
    foreach($oTable->aChild["field"] as $sName => & $oField) {
      $sField = $this->sFieldPrefix . $sName;
      if ($bDebug) echo __METHOD__ . ":Field:" . $sName . "<br/>\n";
      //ensure form has the field
      if ($oField->type=="file-varchar" || $oField->type=="file-text") {
        $sCond = "input".$sFormType;
        //ensure this form field is updatable or insertable or is primary
        if (($sFormType=="update" && $oField->primary) ||
          in_array($oField->$sCond, array("edit","display","hidden"))
        ){
          $aValue = $_FILES[$sField];
          $oForm[$sName] = $aValue;
        }
      } else {
        if (isset($oRow[$sField])) {
          $sCond = "input".$sFormType;
          //ensure this form field is updatable or insertable or is primary
          if (($sFormType=="update" && $oField->primary) ||
            in_array($oField->$sCond, array("edit","display","hidden"))
          ){
            $sValue = $oRow[$sField];
            $oForm[$sName] = $sValue;
          }
        }
      }
    }
    return $oForm;
  }

  public function prepareForm($oRow, $sType) {
    $oObject = $this->oObjectListManager->getObject();
    $aFieldsInput = $this->renderFieldsInput($oObject, $oRow, $sType);
    $this->oView->addVar("aFieldsInput", $aFieldsInput);
    return $aFieldsInput;
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
    $this->onRenderFieldListHeader($aResult);
    return $aResult;
  }

  public function onRenderFieldListHeader(&$aHeaderFields) {}

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
   * @return array()
   */
  public function renderFieldsDisplay(FlexiTableObject $oTable, $oRow) {
    $aResult = array();
    $sTable = $oTable->getTableName();
    foreach($oTable->aChild["field"] as $sName => & $oField) {
      //echo "ok check\n";
      $sOutput = $this->getFieldDisplay($oField, $oRow);
      $sLabel  = $this->getFieldLabel($oField);
      
      $this->onAfterRenderFieldDisplay($sOutput, $sLabel, $oField, $oRow);
      $aResult[$oField->sName] = array("label" => $sLabel, "display" => $sOutput);
    }
    return $aResult;
  }

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
      //echo "check: " . $sCheck . ":" . $sName . "\n";
      if ($oField->$sCheck) {
        //echo "ok check\n";
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
      case "readonly":
      case "display":
        $bRender = true;
        break;
      case "hidden":
      case "none":
        $bRender = false;
        break;
    }

    if (is_null($sLabel)) return "";
    if (strlen($sLabel)==0) return "";
    if (! $bRender) return "";
    //echo "render: " . $oField->getName() . ", type: " . $sType;
    return "<label for=\"field" . $oField->getName() . "\">" . $sLabel . "</label>";
  }

  public function getFieldLabel(FlexiTableFieldObject $oField) {
    $sLabel = $oField->label;
    $this->onFieldLabel($sLabel, $oField);
    return $sLabel;
  }

  public function getFieldInputLabel(FlexiTableFieldObject $oField, $sType) {
    $sLabel = $oField->label;
    $this->onInputFieldLabel($sLabel, $oField, $sType);
    return $sLabel;
  }

  public function renderFieldInputForm(FlexiTableFieldObject $oField, $oRow, $sType) {
    $sInputName = "input" . $sType;
    $sFormInput = $oField->$sInputName;

    $sOutput = ""; $bAddHidden = false;
    switch ($sFormInput) {
      case "edit":
        $oForm = $this->getFieldInput($oField, $oRow);
        $this->onRenderFieldInput($oForm, $oField, $oRow, $sType);
        $sOutput = $this->oView->renderMarkup($oForm, $oForm["#name"]);
        break;
      case "readonly":
      case "display":
        $sOutput = $this->getFieldDisplay($oField, $oRow) . "\n";
        $bAddHidden = true;
        //continue to output hidden field
        break;
      case "hidden":
        $bAddHidden = true;
        break;
      case "none":
        break;
    }
    //var_dump($oRow);
    if ($bAddHidden) {
      $oFieldConfig = clone($oField);
      $oFieldConfig->type = "hidden";
      $oForm = $this->getFieldInput($oFieldConfig, $oRow);
      //var_dump($oForm);
      $this->onRenderFieldInput($oForm, $oField, $oRow, $sType);
      $sOutput .= $this->oView->renderMarkup($oForm, $oForm["#name"]);
    }

    return $sOutput;
  }

  /**
   * get value safe for html display
   * @param FlexiTableFieldObject $oField
   * @param array $oRow
   * @return String
   */
  public function getFieldDisplay(FlexiTableFieldObject $oField, $oRow) {
    $sName = $oField->getName();
    $mValue = $oRow[$sName];

    $bAllowHTML = $oField->allowhtml;
    switch($oField->type) {
      case "select-text":
      case "select-tinyint":
      case "select-smallint":
      case "select-bigint":
      case "select-mediumint":
      case "select-enum":
      case "select-int":
        if ($oField->existsOption($mValue)) {
          $mValue = $oField->getOptionLabel($mValue);
        }
        break;
      case "timestamp-int":
        $sFormat = FlexiDateUtil::getPHPDateTimeFormat(FlexiConfig::$sInputDateTimeFormat);
        $mValue = date($sFormat, $mValue);
        break;
      case "datetime":
        $sFormat = FlexiDateUtil::getPHPDateTimeFormat(FlexiConfig::$sInputDateTimeFormat);
        $mValue = date($sFormat, strtotime($mValue));
        break;
      case "date":
        $sFormat = FlexiDateUtil::getPHPDateTimeFormat(FlexiConfig::$sDisplayDateFormat);
        $mValue = date($sFormat, strtotime($mValue));
        break;
      case "file-varchar":
      case "file-text":
        if (!empty($mValue)) {
          $sURL = FlexiFileUtil::getMediaURL($mValue);
          $mValue = "<a href='" . $sURL . "' target='_blank'>Open</a>";
        }
        $bAllowHTML = true;
        break;
    }

    if (is_null($mValue)) $mValue = "";
    if ($bAllowHTML) {
      if(!empty($oField->allowtag)) {
        $aSafe = $this->getFieldSafeTags($oField);
        $sTag = implode(",", $aSafe["tag"]); $aAttribute = $aSafe["attribute"];
        $mValue = FlexiStringUtil::stripTagsAttributes($mValue, $sTag, $aAttribute);
      }
    } else {
      $mValue = strip_tags($mValue);
    }
    return $mValue;
  }

  
  

  /**
   * check if value entered is safe
   * @param FlexiTableFieldObject $oField
   * @param <type> $oRow
   * @return <type>
   */
  public function isSafeFieldValue(FlexiTableFieldObject $oField, $oRow) {
    $sValue = $oRow[$oField->getName()];
    //todo
    $aSafe = $this->getFieldSafeTags($oField);
    
    return true;
  }
  
  public function getFieldSafeTags(FlexiTableFieldObject $oField) {
    $aResultTag = array();
    $aAttribute = array();
    $aTag = explode(",", $oField->allowtag);
    
    //banned: onmouse..., onclick, link, vlink
    $aAttribute = array(
      "abr", "accept-charset", "accept", "accesskey",
      "action", "align", "href", "alt", "archive",
      "axis", "background", "bgcolor", "cellpadding",
      "cellspacing", "char", "charoff", "checked", "cite", "class",
      "classid", "clear", "code", "codebase", "codetype",
      "color", "cols", "colspan", "compact", "content",
      "coords", "data", "datetime", "declare", "defer", "dir", "disabled",
      "enctype", "face", "for", "frame", "frameborder", "headers",
      "height", "href", "hreflang", "hspace", "http-equiv",
      "hspace", "id", "ismap", "label", "lang", "language",
      "longdesc", "longdesc", "marginheight", "marginwidth",
      "media", "method", "multiple", "name", "noresize",
      "noshade", "nowrap", "profile", "prompt", "readonly", "rel",
      "rev", "rows", "rowspan", "rules", "scheme", "scope",
      "scrolling", "selected", "shape", "size", "span",
      "src", "standby", "start", "style", "summary", "tabindex",
      "target", "text", "title", "type", "usemap", "valign",
      "value", "valuetype", "version", "vspace", "width"
    );
    
    $sOldTag = "<center><bdo><font><isindex><dfn><dir><s><samp><var>";
    $sTableTag = "<table><tbody><td><thead><th><title><tr><tt>";

    //old and basic
    $sBasicTag = $sOldTag . "<strike><a><b><big><blockquote><br><caption>" .
      "<cite><code><dd><del><div><dl><dt>" .
      "<em><h1><h2><h3><h4><h5><h6><hr><i><p><pre><q><small>" .
      "<span><strong><sub><sup><u><ul><li><ol>";
    //basic and table
    $sAdvancedTag = $sBasicTag . $sTableTag . "<area><map><img><ins><kbd><menu>" .
      "<abbr><acronym><address>";
    $sSafeTag = $sAdvancedTag . "<base><body><head><html><meta><basefont>";

    $sFormTag = "<button><fieldset><input><select><form><label><textarea>";
    $sFrameTag = "<iframe><frame><noframes>";

    $sAllTag = $sSafeTag . $sFormTag . $sFrameTag . "<object><script><embed><applet><noscript>";

    $bNoObject = false; $bNoScript = false; $bNoEmbed = false; $bNoApplet = false;
    foreach($aTag as $sTag) {
      switch($sTag) {
        case "all":
          $aResultTag[] = $sAllTag;
          $aAttribute = array(); //allow all
          break;
        case "basic":
          $aResultTag[] = $sBasicTag;
          break;
        case "safe":
          $aResultTag[] = $sSafeTag;
          break;
        case "table":
          $aResultTag[] = $sTableTag;
          break;
        case "form":
          $aResultTag[] = $sFormTag;
          break;
        case "advanced":
          $aResultTag[] = $sAdvancedTag;
          break;
        case "noobject":
          $bNoObject = true;
          break;
        case "noscript":
          $bNoScript = true;
          break;
        case "noembed":
          $bNoEmbed = true;
          break;
        case "noapplet":
          $bNoApplet = true;
          break;
        default:
          $aResultTag[] = "<" . $sTag . ">";
      } //switch
    }//atag

    $bNoObject = false; $bNoScript = false; $bNoEmbed = false; $bNoApplet = false;
    if ($bNoObject || $bNoScript || $bNoEmbed || $bNoApplet) {
      for($c=0; $c < $aTag; $c++) {
        if ($bNoObject) {
          $aTag[$c] = str_replace("<object>", "", $aTag[$c]);
        }
        if ($bNoScript) {
          $aTag[$c] = str_replace("<script>", "", $aTag[$c]);
        }
        if ($bNoEmbed) {
          $aTag[$c] = str_replace("<embed>", "", $aTag[$c]);
        }
        if ($bNoApplet) {
          $aTag[$c] = str_replace("<applet>", "", $aTag[$c]);
        }
      }
    }
    
    return array("tag" => $aResultTag, "attribute" => $aAttribute);
  }
  
  public function getFieldInput(FlexiTableFieldObject $oField, $oRow) {
    $sName = $oField->getName();
    $aResult = array(
      "#name"           => $this->sFieldPrefix . $sName,
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
      case "smallint":
      case "mediumint":
      case "bigint":
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
      case "select-text":
      case "select-tinyint":
      case "select-smallint":
      case "select-bigint":
      case "select-mediumint":
      case "select-enum":
      case "select-int":
        $aResult["#type"] = "select.raw";
        $aResult["#options"] = $oField->getOptions();
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
      case "timestamp":
      case "timestamp-int":
        $aResult["#type"] = "datetime.raw";
        break;

      case "file-varchar":
      case "file-text":
        $aResult["#type"] = "file.raw";
        break;
      case "hidden":
        $aResult["#type"] = "hidden.raw";
        break;
      default:
        throw new Exception("Unsupported type: " . $oField->type);
    }
    
    if (!empty($oField->formsize)) {
      if (substr($oField->type,0,4) == "html" ||
        substr($oField->type,0,4) == "text" ||
        substr($oField->type,0,4) == "json") {

        $aSize = explode(",",$oField->formsize);
        $aResult["#cols"] = $aSize[0];
        if (count($aSize)>=2) $aResult["#rows"] = $aSize[1];
      } else {
        //default
        $aResult["#size"] = $oField->formsize;
      }
    } else {
      
    }

    if (isset($oRow[$sName])) {
      $sValue = $oRow[$sName];
      switch($oField->type) {
        case "date":
        case "datetime":
          //dont need this as actual value is already hidden
          /*
          $sFormat = isset($aResult["#format"]) ? $aResult["#format"] : 
            ($oField->type == "date" ? FlexiConfig::$sInputDateFormat: FlexiConfig::$sInputDateTimeFormat);
          $sFormat = FlexiDateUtil::getPHPDateTimeFormat($sFormat); //fix double i, which only 1 i(min) in php
          if (!empty($sValue)) {
            if (substr($sValue,0, 4)=="0000") {
              //empty date
              $sValue = "";
            } else {
              $iDatetime = strtotime($sValue);
              $sValue = date($sFormat, $iDatetime);
            }
          }
         */
          break;
      }//switch
      
      $aResult["#value"] = $sValue;
    }

    return $aResult;
  }

  

  public function onFieldLabel(&$sLabel, FlexiTableFieldObject &$oField) {}
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
  public function onAfterRenderFieldDisplay(& $sOutput, &$sLabel, FlexiTableFieldObject & $oField, $oRow) {}
}
