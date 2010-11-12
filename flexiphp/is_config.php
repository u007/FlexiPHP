<?php

require_once("config.base.php");

global $HOST, $DATABASENAME, $USER, $PASSWORD, $tableprefix;
//global SITE_EMAIL, SITE_URL

$aFlexiSetting = array_merge($aFlexiSetting, 
	array("dbtype" 		=> "mysql",
				"dbhost" 		=> $HOST,
				"dbport" 		=> 3306,
				"dbuser" 		=> $USER,
				"dbpass" 		=> $PASSWORD,
				"dbname" 		=> $DATABASENAME,
				"dbprefix" 	=> $tableprefix,
				"framework" => "iscript",
				"templatepath" => "assets/flexitemplate",
        "basedir" 	=> dirname(__FILE__),
        "support.email" => SITE_EMAIL
				)
);

//var_dump($aFlexiSetting["baseurl"]);
if (isset($bIsAdminModule) && $bIsAdminModule) {
  $aFlexiSetting["baseurl"] .= "admin/flexi.is.php";
} else {
  $aFlexiSetting["baseurl"] .= "flexi.is.php";
}
//var_dump($aFlexiSetting["baseurl"]);

$aFlexiSetting["url.login"] = $aFlexiSetting["baseurl"] . "login";

ini_set("display_errors", "On");
ini_set("error_reporting", E_ALL & ~E_NOTICE);
