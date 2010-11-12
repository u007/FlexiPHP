<?php

class FlexiArrayUtil
{
	
	public static function cloneArray($aValue)
	{
		if (!is_array($aValue))
		{
			throw new FlexiException("is not a array:" . serialize($aValue), ERROR_DATATYPE);
		}
		return array_slice($aValue, 0, count($aValue));
	}
	
}
