<?php

define("ERROR_UNKNOWN", 0);
define("ERROR_DATATYPE", 500);
define("ERROR_RETURNVALUE", 600);
define("ERROR_EOF", 450);
define("ERROR_FRAMEWORK", 700);
define("ERROR_UNKNOWNTYPE", 800);
define("ERROR_INVALIDVALUE", 810);

define("ERROR_CONFIGURATION", 550);
define("ERROR_IO_LOCK", 900);
define("ERROR_FILE_MOVE", 901);

define("ERROR_FILESIZE", 1001);
define("ERROR_FILETYPE", 1002);
define("ERROR_CREATEERROR", 1005);
define("ERROR_WRITE", 1006);

define("ERROR_INVALIDPARAMS", 1100);

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
