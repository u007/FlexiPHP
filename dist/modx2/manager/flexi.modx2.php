<?php

define('MODX_API_MODE', true);
define('IN_MANAGER_MODE',true);
$bIsAdminModule = 1;
global $modx;
require_once(dirname(dirname(__FILE__)) . "/index.php");
$modx->initialize('mgr');

if (isset($modx) && is_object($modx) && $modx instanceof modX) {
    if (!$modx->getRequest()) {
        $modx->log(modX::LOG_LEVEL_FATAL,"Could not load the MODx manager request object.");
    }
    $modx->getParser();
    require_once(dirname(dirname(__FILE__)) . "/modx_module.php");
}