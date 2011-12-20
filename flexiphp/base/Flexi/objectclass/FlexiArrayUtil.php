<?php

class FlexiArrayUtil
{
	
  public static function clonePartialKey($aList, $aKey) {
    $aResult = array();
    foreach($aList as $oRow) {
      $oNewRow = array();
      foreach($aKey as $sKey) {
        $oNewRow[$sKey] = $oRow[$sKey];
      }
      $aResult[] = $oNewRow;
    }//foreach list
    return $aResult;
  }
  
	public static function cloneArray($aValue)
	{
		if (!is_array($aValue))
		{
			throw new FlexiException("is not a array:" . serialize($aValue), ERROR_DATATYPE);
		}
		return array_slice($aValue, 0, count($aValue));
	}
	
}
