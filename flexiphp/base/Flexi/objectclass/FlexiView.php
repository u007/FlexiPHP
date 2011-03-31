<?php

class FlexiView extends FlexiBaseView
{
	
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Add a css file to header
	 * @param string path
	 * @param string media: default:all
	 * 	options: all, braille, embossed, handheld, print, projection,
	 * 	screen, speech, tty, tv
	 * @param string type: default: text/css
	 */
	public function addCSS($sPath, $sMedia="all", $sType="text/css")
	{
		$this->aHeader["css"][] = array("path" => $sPath, "type" => $sType, "media" => $sMedia);
	}
	/**
	 * Add JS file to header
	 * @param string path
	 */
	public function addJS($sPath, $sType="text/javascript")
	{
		$this->aHeader["js"][] = array("path" => $sPath, "type" => $sType);
	}
	
	public function processMarkup(& $aValues, $asName="", $sPath = "")
	{
		$aResult = array();
		
		//var_dump($aValues);
		foreach($aValues as $sName => & $aValue)
		{
			$this->renderFilterMarkup($aValue);
		}
	}
	
	public function renderElement($aValues, $asName, $sPath=null) {
    if (isset($aValues[$asName])) {
      return $this->renderMarkup($aValues[$asName], $asName, $sPath);
    }
    throw new FlexiException("Markup element missing: " . $asName, ERROR_EOF);
  }
  /**
   * Render array of forms via FlexiView
   * @param array $aValues fields
   * @param String $asName field name
   * @param String $asPath path to view, default to module full path
   * @return String HTML
   */
  public function renderMarkup($aValues, $asName="", $sPath = null)
	{
		if (! is_array($aValues))
		{
			throw new FlexiException("Invalid data type, must be an array: " . serialize($aValues), ERROR_DATATYPE);
		}
		$aResult = array();
		
		$sType = isset($aValues["#type"]) ? $aValues["#type"] : "markup";
		
		$bIsMarkup = ($sType=="markup" ? true: false);
		$sThisName = FlexiParser::parseHTMLInputName($asName);
		
		//is a single element
		$adValues = FlexiArrayUtil::cloneArray($aValues);
//    if ($asName == "agendadiv") {
//      var_dump($aValue);
//    }
    //echo "name: " . $asName;
		$this->renderFilterMarkup($adValues);
//
//    if ($asName == "agendadiv") {
//      echo "after";
//    }
		//var_dump($aValue);
		//$this->renderFilterForm($aValue, $asName);
		switch($sType)
		{
			case "select":
			case "select.raw":
			case "textfield":
			case "textfield.raw":
      case "email":
      case "email.raw":
      case "date":
      case "date.raw":
			case "textarea":
			case "textarea.raw":
			case "button":
			case "button.raw":
			case "submit":
			case "submit.raw":
			case "form":
			case "form.raw":
			case "checkbox":
			case "checkbox.raw":
			case "checkboxes":
			case "checkboxes.raw":
			case "radio":
			case "radio.raw":
			case "radios":
			case "radios.raw":
			case "html":
				$this->renderFilterForm($adValues, $asName);
				break;
		}
		
		$sName = FlexiParser::parseHTMLInputName($asName);
		$aVars = array_merge($adValues, array(
			"#name" => $sName)
		);
		
		$sTheme = isset($adValues["#theme"]) ? $adValues["#theme"] : "element." . $sType;
    //echo "sorting: ";
    //print_r($adValues);

    uasort($adValues, "flexiSortByWeight");
//		if ($bIsMarkup)
//		{
//			echo "is markup";
//			$this->processMarkup($aValue, $asName, $sPath);
//		}
		
		foreach($adValues as $sName => $aValue)
		{
			//is a child
			if ($sName[0] != "#")
			{
        //echo "child: " . $sName . "\r\n<br/>";
				$aResult[] = $this->renderMarkup($aValue, $sName, $sPath);
			}
		}
		
		$sChildResult = implode("\r\n", $aResult);
		$aVars["#childs"] = $sChildResult;
		$sResult = $this->render($sTheme, $aVars, $sPath);
		
		return $sResult;
	}

  /**
   * Filtering hook to update markups setting
   * @param array $aValue
   * @return void
   */
	protected function renderFilterMarkup(& $aValue)
	{
		if (isset($aValue["#filterrendered"]))
		{
			if ($aValue["#filterrendered"]) { return; }
		}
		
		
		if (!isset($aValue["#id"]))
		{
			$aValue["#id"] = "id_" . FlexiStringUtil::createRandomPassword(15);
		}
		
		//TODO general markup filter for security
		
		$aValue["#filterrendered"] = true;
	}

  /**
   * Filtering hook to cleanup a single form field setting and value
   *  before being displayed
   * @param array $aValue
   * @param String $asName field name
   */
	protected function renderFilterForm(& $aValue, $asName)
	{
		$this->renderFilterMarkup($aValue);
		$mValue = null;
		
		if ($aValue["#type"] != "form")
		{
      $bIsTextArea = $aValue["#type"] == "textarea" || $aValue["#type"]=="html"
        || $aValue["#type"] == "textarea.raw" || $aValue["#type"] == "html.raw" ? true: false;
      
			if (isset($aValue["#value"]) && !$bIsTextArea)
			{
				$mValue = $aValue["#value"];
				$aValue["#value"] = FlexiParser::parseHTMLInputValue($mValue);
			}
			if (isset($aValue["#default_value"]) && !$bIsTextArea)
			{
				$mValue = $aValue["#default_value"];
				$aValue["#default_value"] = FlexiParser::parseHTMLInputValue($mValue);
			}
		}
		
		if ($aValue["#type"] == "checkbox" || $aValue["#type"] == "checkbox.raw")
		{
			if(! isset($aValue["#return_value"]))
			{
				$aValue["#return_value"] = 1;
			}
		}
		
		if ($aValue["#type"] == "form")
		{
			$aValue["#method"] = isset($aValue["#method"]) ? $aValue["#method"] : "get";
			
			if ($aValue["#upload"])
			{
				$aValue["#method"] = "post";
				$aValue["#enctype"] = "multipart/form-data";
			}
		}
		
		//throw checking
		if (($aValue["#type"] == "select" || $aValue["#type"] == "select.raw") && !is_null($mValue))
		{
			$bMultiple = isset($aValue["#multiple"]) ? $aValue["#multiple"] : false;
			if ($bMultiple && !is_array($mValue))
			{
				throw new FlexiException("Form:" . $asName . " value must be of type array for multiple select:" . serialize($mValue), ERROR_DATATYPE);
			}
		}
		
		if (($aValue["#type"] == "checkboxes" || $aValue["#type"] == "checkboxes.raw") && !is_null($mValue) && !is_array($mValue))
		{
			throw new FlexiException("Form:" . $asName . " value must be of type array for checkboxes:" . serialize($mValue), ERROR_DATATYPE);
		}
	}
	
	
	public function addPaging($iTotalRecord, $iRecordPerPage, $iPage=1, $aParams = array(), $sURL = null)
	{
		$this->addVar("totalrecords", 	$iTotalRecord);
		$this->addVar("rowsperpage", 		$iRecordPerPage);
		$this->addVar("page", 					$iPage);
		$this->addVar("params", 				$aParams);
		$this->addVar("url",						$sURL);
	}

  public function ajaxURL($aaParam = array(), $asMethod=null, $asModule=null, $asURL="")
	{
		$aParam = is_null($aaParam) ? array() : $aaParam;
		//getting class name without "Controller"
		$sModule 	= is_null($asModule) ? $this->getVar("#module") : $asModule;
		$sMethod 	=	is_null($asMethod) ? $this->getVar("#method") : $asMethod;
		//var_dump($sModule);
		//var_dump($asURL);
		$sURL = "";
		if (! empty($sModule) && !empty($sMethod))
		{
			//echo "not empty";
			$sURL = "?" . FlexiConfig::$aModuleURL["module"] . "=" . $sModule . "&" . FlexiConfig::$aModuleURL["method"] . "=" . $sMethod;
		}

		$aParamQuery = array();
		foreach($aParam as $sKey => $sValue)
		{
			$aParamQuery[] = $sKey . "=" . $sValue;
		}

		$sURL .= empty($sURL) ? "?" : "&";
		$sURL .= implode("&", $aParamQuery);

		$sURL .= empty($sURL) ? "?" : "&";
		$sURL .= $asURL;

		return flexiURL($sURL, true);
	}

	public function url($aaParam = array(), $asMethod=null, $asModule=null, $asURL="", $bAjax = false)
	{
		$aParam = is_null($aaParam) ? array() : $aaParam;
		//getting class name without "Controller"
		$sModule 	= is_null($asModule) ? $this->getVar("#module") : $asModule;
		$sMethod 	=	is_null($asMethod) ? $this->getVar("#method") : $asMethod;
		//var_dump($sModule);
		//var_dump($asURL);
		$sURL = "";
		if (! empty($sModule) && !empty($sMethod))
		{
			//echo "not empty";
			$sURL = "?" . FlexiConfig::$aModuleURL["module"] . "=" . $sModule . "&" . FlexiConfig::$aModuleURL["method"] . "=" . $sMethod;
		}
		
		$aParamQuery = array();
		foreach($aParam as $sKey => $sValue)
		{
			$aParamQuery[] = $sKey . "=" . $sValue;
		}
		
		$sURL .= empty($sURL) ? "?" : "&";
		$sURL .= implode("&", $aParamQuery);
		
		$sURL .= empty($sURL) ? "?" : "&";
		$sURL .= $asURL;
		
		return flexiURL($sURL, $bAjax);
	}

	public function renderTextField($mValue, $asName = "", $asId = null, $aAttribute = array()) {
		return $this->renderInputBox($mValue, $asName, $asId, $aAttribute);
	}

  public function renderInputBox($mValue, $asName = "", $asId = null, $aAttribute = array())
	{
		$aForm = array(
			"#type" => "textfield",
			"#value" 					=> $mValue,
			"#attributes"			=> $aAttribute
		);
		
		if (!empty($asId))
		{
			$aForm["#id"] = $asId;
		}
		//var_dump($this->getVar("#modulepath"));
		return $this->renderMarkup($aForm, $asName, $this->getVar("#modulepath"));
	}

	public function renderCheckBox($mValue, $asName = "", $bChecked=false, $asId = null, $aAttribute = array())
	{
		$aForm = array(
			"#type" => "checkbox",
			"#return_value" => $mValue,
			"#value" 					=> $bChecked ? $mValue : "",
			"#attributes"			=> $aAttribute
		);
		
		if (!empty($asId))
		{
			$aForm["#id"] = $asId;
		}
		//var_dump($this->getVar("#modulepath"));
		return $this->renderMarkup($aForm, $asName, $this->getVar("#modulepath"));
	}
	
	public function renderCheckAllBox($asCheckBoxName, $asName = "", $bChecked=false, $asId = null, $aAttribute = array())
	{
		$sCheckBoxName = $asCheckBoxName;
		$sCheckBoxName = str_replace("[", "\[", $sCheckBoxName);
		$sCheckBoxName = str_replace("]", "\]", $sCheckBoxName);
		
		$sSelect = "input[name=" . $sCheckBoxName . "]";
		if (!isset($aAttribute["onClick"]))
		{
			$aAttribute["onClick"] = "javascript:doCheckAllCheckBox('" . $sSelect . "',this);";
		}
		$aForm = array(
			"#type" => "checkbox",
			"#return_value" => "1",
			"#value" 					=> $bChecked ? "1" : "",
			"#attributes"			=> $aAttribute
		);
		
		if (!empty($asId))
		{
			$aForm["#id"] = $asId;
		}
		
		return $this->renderMarkup($aForm, $asName, $this->getVar("#modulepath"));
	}
	
	public function renderFlexiLink($asLink, $asTitle="", $asMethod=null, $asModule=null, $asURL="", $aParam = array(), $asTarget="", $asName = "", $asId = null, $aAttribute = array())
	{
		$sLink = $this->url($aParam, $asMethod, $asModule, $asLink);
		
		return $this->renderLink($sLink, $asTitle, $asTarget, $asName, $asId, $aAttribute);
	}
	
	public function renderLink($sLink, $asTitle="", $asTarget="", $asName = "", $asId = null, $aAttribute = array())
	{
		$sTitle = empty($asTitle) ? $sLink : $asTitle;
		
		$aMarkup = array(
			"#type" => "link", 
			"#path"				=> $sLink,
			"#title" 			=> $sTitle,
			"#target" 		=> $asTarget,
			"#attributes"	=> $aAttribute);
		
		if (!empty($asId))
		{
			$aForm["#id"] = $asId;
		}
		
		return $this->renderMarkup($aMarkup, $asName, $this->getVar("#modulepath"));
	}

  public function renderFormHiddenRaw($asName, $asValue, $asId = null, $aAttribute = array())
	{
		$aForm = array(
			"#type" 			=> "hidden.raw",
			"#value"			=> $asValue,
			"#attributes"	=> $aAttribute);

		if (!empty($asId))
		{
			$aForm["#id"] = $asId;
		}

		return $this->renderMarkup($aForm, $asName, $this->getVar("#modulepath"));
	}

	public function renderFormHidden($asName, $asValue, $asId = null, $aAttribute = array())
	{
		$aForm = array(
			"#type" 			=> "hidden",
			"#value"			=> $asValue,
			"#attributes"	=> $aAttribute);
		
		if (!empty($asId))
		{
			$aForm["#id"] = $asId;
		}
		
		return $this->renderMarkup($aForm, $asName, $this->getVar("#modulepath"));
	}

  public function renderFormTextField($asName, $asValue, $asId = null, $aAttribute = array())
	{
		$aForm = array(
			"#type" 			=> "textfield",
			"#value"			=> $asValue,
			"#attributes"	=> $aAttribute);

		if (!empty($asId))
		{
			$aForm["#id"] = $asId;
		}
		
		return $this->renderMarkup($aForm, $asName, $this->getVar("#modulepath"));
	}
	
	public function renderFormSelectRaw($asName, $asValue, $aOptions=array(), $asId = null, $aAttribute = array())
	{
		$aForm = array(
			"#type" 			=> "select.raw",
			"#value"			=> $asValue,
			"#options"		=> $aOptions,
			"#attributes"	=> $aAttribute);
			
		if (!empty($asId))
		{
			$aForm["#id"] = $asId;
		}
		
		return $this->renderMarkup($aForm, $asName, $this->getVar("#modulepath"));
	}
	
	public function renderFormSelect($asName, $asValue, $aOptions=array(), $asId = null, $aAttribute = array())
	{
		$aForm = array(
			"#type" 			=> "select",
			"#value"			=> $asValue,
			"#options"		=> $aOptions,
			"#attributes"	=> $aAttribute);
			
		if (!empty($asId))
		{
			$aForm["#id"] = $asId;
		}
		
		return $this->renderMarkup($aForm, $asName, $this->getVar("#modulepath"));
	}
	
}
