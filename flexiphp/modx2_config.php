<?php

require_once(dirname(__FILE__) . "/config.base.php");

global $database_type, $database_server, $database_user, $database_password, $dbase;
global $modx;

$aBaseURL = parse_url(MODX_SITE_URL);
$sBaseURL = $aBaseURL["path"] . (substr($aBaseURL["path"],-1)=="/"? "": "/");
$aFlexiSetting = array_merge($aFlexiSetting, array(
  "dbtype" 		=> $database_type,
  "dbhost" 		=> $database_server,
  "dbport" 		=> 3306,
  "dbuser" 		=> $database_user,
  "dbpass" 		=> $database_password,
  "dbname" 		=> str_replace("`","", $dbase),
  "dbprefix" 	=> "modx_",
  "framework" => "modx2",
  "templatepath" => "assets/flexitemplate",
  //"baseurl"   => $modx->config["site_url"],
  "baseurl" => $sBaseURL,
  "rooturl" => $sBaseURL . "flexiphp/",
  "assetsurl" => $sBaseURL . "assets/",
  "support.email" => $modx->config["emailsender"]
  )
);


//$aFlexiSetting["baseurl"] .= substr($base_url,1) . "workspace.html";
if (isset($bIsAdminModule) && $bIsAdminModule) {
  global $iActionId;
  if(empty($iActionId)) { $iActionId = $_GET["a"]; }
  $aFlexiSetting["baseurl"] .= "manager/index.php?a=" . $iActionId;
} else {
  $aFlexiSetting["baseurl"] .= "workspace.html";
}

$aFlexiSetting["url.login"] = $aFlexiSetting["baseurl"] . "login";

ini_set("display_errors", "On");
ini_set("error_reporting", E_ALL & ~E_NOTICE);
