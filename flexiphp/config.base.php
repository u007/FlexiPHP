<?php

if (ini_get("log_errors_max_len")!=0) {
  ini_set("log_errors_max_len", 10240);
}

if (ini_get("magic_quotes_gpc")=="1") {
  ini_set("magic_quotes_gpc", 0);
}

$bIsDev = !empty($_SERVER["HTTP_HOST"]) && $_SERVER["HTTP_HOST"] == 'localhost' ? true : false;
global $aFlexiSetting;
//dumpStack();
if (!isset($aFlexiSetting)) $aFlexiSetting = array();
    /*** return the full address ***/

$sProtocol = @$_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
$aFlexiSetting = array_merge(array(
  "rootdir"   => getcwd(), //working directory
	"basedir" 	=> dirname(__FILE__), //flexiphp path
  "protocol"  => $sProtocol,
	//REQUEST_URI WONT WORK ON IIS, too bad :)
	//	this must be trimmed down to framework dependant.
	//	for modx, it uses alias, so all query string will be discarded
	//"baseurl"		=> "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"],
  "baseurl"   => $sProtocol.'://'. (isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "localhost") . isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME']: "/",
	"moduleurl" => array("path" => "mod=[mod]&method=[method]", "module"=> "mod", "method" => "method"),
	"loglevel" 	=> 3,
	"framework" => "",
	"template" 	=> "default",
	"title"			=> "FlexiPHP Framework",
	
	"timezone"	=> "",
	"input_dateformat" => "dd-mm-yy",

  "templatepath" => "flexiphp/assets/templates",
	"modulepath" => getcwd() . "/modules",
	"defaultlanguage" => "en",
	//requests
	"post" => & $_POST,
	"get" => & $_GET, 
	"cookie" => & $_COOKIE,
	"files" => &$_FILES,
	"session" => & $_SESSION,
	//db info
	"dbtype" 		=> "",
	"dbhost" 		=> "",
	"dbport" 		=> null,
	"dbuser" 		=> "",
	"dbpass" 		=> "",
	"dbname" 		=> "",
	"dbprefix" 	=> "",
	
	"logfile"		=> getcwd() . "/flexiphp.log",
	"starttime" => microtime())
  , $aFlexiSetting);
