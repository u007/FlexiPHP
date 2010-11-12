<?php

session_start();

$aFlexiSetting = array(
	"basedir" 	=> "flexiphp",
	"loglevel" 	=> 2,
	"framework" => "",
	"template" 	=> "default",
	"title"			=> "FlexiPHP Framework",
	//requests
	"post" => & $_POST,
	"get" => & $_GET,
	"cookie" => & $_COOKIE,
	"files" => &$_FILES,
	"session" => $_SESSION,
	//db info
	"dbtype" => "mysql",
	"dbhost" => "localhost",
	"dbport" => 3306,
	"dbuser" => "root",
	"dbpass" => "007890",
	"dbname" => "",

	"starttime" => microtime()
);

require_once(dirname(__FILE__) . "/flexiphp/flexi.include.php");
