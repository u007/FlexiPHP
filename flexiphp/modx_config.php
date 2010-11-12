<?php

require_once("config.base.php");

global $database_type, $database_server, $database_user, $database_password, $dbase;
global $base_url, $modx;

$aFlexiSetting = array_merge($aFlexiSetting, 
	array("dbtype" 		=> $database_type,
				"dbhost" 		=> $database_server,
				"dbport" 		=> 3306,
				"dbuser" 		=> $database_user,
				"dbpass" 		=> $database_password,
				"dbname" 		=> str_replace("`","", $dbase),
				"dbprefix" 	=> "modx_",
				"framework" => "modx",
				"templatepath" => "assets/flexitemplate",
        "basedir" 	=> dirname(__FILE__),
        "support.email" => $modx->config["emailsender"]
				)
);


if (strlen($base_url) > 0) {
  //$aFlexiSetting["baseurl"] .= substr($base_url,1) . "workspace.html";
  if (isset($bIsAdminModule) && $bIsAdminModule) {
    $aFlexiSetting["baseurl"] .= substr($base_url,1) . "manager/index.php?a=" . $_GET["a"] . "&id=" . $_GET["id"];
  } else {
    $aFlexiSetting["baseurl"] .= substr($base_url,1) . "workspace.html";
  }
}

$aFlexiSetting["url.login"] = $aFlexiSetting["baseurl"] . "login";

ini_set("display_errors", "On");
ini_set("error_reporting", E_ALL & ~E_NOTICE);
