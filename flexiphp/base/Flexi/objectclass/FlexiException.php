<?php

define("ERROR_DATATYPE", 500);
define("ERROR_RETURNVALUE", 600);
define("ERROR_EOF", 450);
define("ERROR_FRAMEWORK", 700);
define("ERROR_UNKNOWNTYPE", 800);
define("ERROR_CONFIGURATION", 550);
define("ERROR_IO_LOCK", 900);

class FlexiException extends Exception
{
	/**
	 * @param string message
	 * @param int	code:
	 * 	0: uncategoriesed code, 500: invalid class type
	 */
	public function __construct($message = "", $code = 0)
	{
		FlexiLogger::error(__METHOD__, $code . ":" . $message);
		parent::__construct($message, $code);
  }
	
}
