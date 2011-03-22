<?php

class FlexiBaseViewManager {
  protected $oView = null;
  protected $oObjectListManager = null;
  protected $sFieldPrefix = "field";
  protected $sListLinkCol = "id";
  
  public function  __construct($aParam) {
    //parent::__construct($aParam);
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

  public function setView(FlexiView &$oView) {
    $this->oView = $oView;
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
    $sLink = "doLoadObject(" . json_encode($aLink) . ")";
    
    return array("primary" => $aAlias, "link" => $sLink);
  }
  /**
   * passed in value is alias name of primary
   * @param array $aPrimary
   */
  public function onGetPrimaryLink(&$aPrimary) {}

  public function getInputRow($oRow, $sType) {
    $aResult = array();
    $oTable = $this->oObjectListManager->getObject();
    foreach($oRow as $sField => $sValue) {
      $aResult[$sField] = $this->getFieldDisplay($oTable->aChild["field"][$sField], $oRow);
    }
    return $aResult;
  }

  public function getDisplayRow($oRow) {
    $aResult = array();
    $oTable = $this->oObjectListManager->getObject();
    foreach($oRow as $sField => $sValue) {
      $aResult[$sField] = $this->getFieldDisplay($oTable->aChild["field"][$sField], $oRow);
    }
    return $aResult;
  }
  
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
    $this->oView->addVar("aFieldHeader", $this->renderFieldsListHeader($oObject));

    $aListField = $oObject->getListFields();
    $this->oView->addVar("aListFieldName", $aListField);
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

    $sOutput = "";
    switch ($sFormInput) {
      case "edit":
        $oForm = $this->getFieldInput($oField, $oRow);
        $this->onRenderFieldInput($oForm, $oField, $oRow, $sType);
        $sOutput = $this->oView->renderMarkup($oForm, $oForm["#name"]);
        break;
      case "readonly":
        $sOutput = $this->getFieldDisplay($oField, $oRow) . "\n";
        //continue to output hidden field
      case "hidden":
        $oFieldConfig = clone($oField);
        $oFieldConfig->type = "hidden";
        $oForm = $this->getFieldInput($oFieldConfig, $oRow);
        $this->onRenderFieldInput($oForm, $oField, $oRow, $sType);
        $sOutput .= $this->oView->renderMarkup($oForm, $oForm["#name"]);
        break;
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

    switch($oField->type) {
      case "select-text":
      case "select-tinyint":
      case "select-int":
        if (!empty($oField->options)) {
          $aOptions = explode("\n", $oField->options);
          $aResultOptions = array();
          foreach($aOptions as $sOption) {
            $aOption = explode("=", $sOption);
            $sKey = $aOption[0];
            $sLabel = count($aOption) > 1? $aOption[1]: $sKey;
            if ($mValue == $sKey) $mValue = $sLabel;
          }
        }
        break;
    }

    if ($oField->allowhtml) {
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
      case "select-int":
      case "select-tinyint":
        $aResult["#type"] = "select.raw";
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

    if (!empty($oField->formsize)) {
      switch($oField->type){
        case "html":
        case "text":
        case "json":
          $aSize = explode(",",$oField->formsize);
          $aResult["#rows"] = $aSize[0];
          if (count($aSize)>=2) $aResult["#cols"] = $aSize[1];
          break;

        case "select-text":
        case "select-int":
        case "select-tinyint":
          if (!empty($oField->options)) {
            $aOptions = explode("\n", $oField->options);
            $aResultOptions = array();
            foreach($aOptions as $sOption) {
              $aOption = explode("=", $sOption);
              $sKey = $aOption[0];
              $sValue = count($aOption) > 1? $aOption[1]: $sKey;
              $aResultOptions[$sKey] = $sValue;
            }
            $aResult["#options"] = $aResultOptions;
          }
          
          break;
        default:
          $aResult["#size"] = $oField->formsize;
      }
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
