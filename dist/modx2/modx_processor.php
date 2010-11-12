<?php

/*
 * This is an example of how flexiphp can be initialise via several lines of codes and config.
 * It is assuming that the parent framework start the session, therefore session will not
 * be handled within flexicontroller...
 * This file is called from flexi.init plugin of modx2
 * @author James
 * @version 1.0
 * @url http://www.mercstudio.com
 */

require_once("modx_header.php");

CMSLoginHandler::checkSession();

$sModule = $oFlexi->getRequest(FlexiConfig::$aModuleURL["module"]);
$sMethod = $oFlexi->getRequest(FlexiConfig::$aModuleURL["method"]);
$oFlexi->run($sModule, $sMethod, false);

