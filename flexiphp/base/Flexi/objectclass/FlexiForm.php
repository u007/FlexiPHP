<?php

class FlexiForm 
{
	private $oInstance = null;
	private $aForms = array();
	
	function __construct()
	{
	
	}
	
	/**
	 * parse array based form
	 */
	public function parseForm($aForm)
	{
		
	}
	
	public function renderForm(& $aForms, $sFormId="")
	{
		$aResult = array();
		foreach($aForms as $sName => $aForm)
		{
			$sType = isset($aForm["#type"]) ? $aForm["#type"] : "markup";
			$sFName = "renderForm" . ucfirst(strtolower($sType));
			$aResult[] = $this->$sFName($sName, $aForm);
		}
		
		return implode("\r\n", $aResult);
	}
	
	protected function renderFormTextfield($asName, & $aForm)
	{
		$sName = FlexiParser::parseHTMLInputName($asName);
		
		$mValue = isset($aForm["#value"]) ? $aForm["#value"] : "";
		$mValue = FlexiParser::parseHTMLInputValue($mValue);
		
		$sTheme = isset($a
		
		return 
			(isset($aForm["#prefix"]) ? $aForm["#prefix"] : "") .
			"<input type=\"text\" name=\"" . $sName . "\"" . 
			(empty($mValue) ? "" : "value=\"" . $mValue . "\"") . 
			">" . 
			(isset($aForm["#suffix"]) ? $aForm["#suffix"] : "");
	}
	
	
	
	public static function getInstance()
	{
		if (!isset(self::$oInstance))
		{
			self::$oInstance = new FlexiForm();
		}
		
		return self::$oInstance;
	}
	
}

