<?php

define('MODX_API_MODE', true);
global $modx;
require_once(dirname(__FILE__) . "/index.php");
if (isset($modx) && is_object($modx) && $modx instanceof modX) {
    if (!$modx->getRequest()) {
        $modx->log(modX::LOG_LEVEL_FATAL,"Could not load the MODx manager request object.");
    }
    require_once(dirname(__FILE__) . "/modx_module.php");
}
