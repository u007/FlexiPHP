<?php

class FlexiParser
{
	
	public static function parseNoHTML($mValue)
	{
		$sResult = strip_tags($mValue);
		return $sResult;
	}
	
	public static function parseHTMLInputName($mValue)
	{
		$sResult = strip_tags($mValue);
		$sResult = preg_replace("/[^0-9A-Za-z_\-\[\]]/", "", $sResult);
		return $sResult;
	}
	
	public static function parseHTMLInputValue($mValue, $sep = "\"")
	{
		if (is_null($mValue)) { return null; }
		
		if (is_array($mValue))
		{
			$mResult = array();
			foreach($mValue as $sValue)
			{
				$mResult[] = str_replace($sep, $sep.$sep, $sValue);
			}
			
		}
		else
		{
			$mResult = str_replace($sep, $sep.$sep, $mValue);
		}
		
		return $mResult;
	}
	
	
	public static function parseFunctionName($mValue)
	{
		$sResult = strip_tags($mValue);
		$sResult = preg_replace("/[^0-9A-Za-z_]/", "", $sResult);
		return $sResult;
	}
	
}
