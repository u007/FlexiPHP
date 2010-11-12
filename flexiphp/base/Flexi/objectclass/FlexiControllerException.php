<?php

class FlexiControllerException extends Exception
{
	/**
	 * @param string message
	 * @param int	code:
	 * 	0: uncategoriesed code, 1: invalid class type
	 */
	public function __construct($message = "", $code = 0)
	{
		FlexiLogger::error(__METHOD__, $code . ":" . $message);
		parent::__construct($message, $code);
  }
	
}
