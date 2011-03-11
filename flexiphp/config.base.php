<?php

$bIsDev = !empty($_SERVER["HTTP_HOST"]) && $_SERVER["HTTP_HOST"] == 'localhost' ? true : false;
global $aFlexiSetting;
//dumpStack();
$aFlexiSetting = array(
  "rootdir"   => getcwd(),
	"basedir" 	=> dirname(__FILE__),
	//REQUEST_URI WONT WORK ON IIS, too bad :)
	//	this must be trimmed down to framework dependant.
	//	for modx, it uses alias, so all query string will be discarded
	//"baseurl"		=> "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"],
  "baseurl"   => (isset($_SERVER["HTTPS"]) ? (! empty($_SERVER["HTTPS"]) ? "https" : "http") : "http") . "://" . (isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "localhost") . "/",
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
	"starttime" => microtime()
);
