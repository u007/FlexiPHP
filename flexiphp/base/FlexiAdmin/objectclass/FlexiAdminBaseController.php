<?php

class FlexiAdminBaseController extends FlexiBaseController {
  
  function onInit()
	{
		//set not page layout
    $this->oView->setTemplate(FlexiConfig::$sAdminTemplate);
    if (FlexiConfig::$sFramework == "modx2") {
      global $modx;
      //die("flexiurl :" . FlexiConfig::$sFlexiBaseURL);
      $modx->regClientCSS(FlexiConfig::$sFlexiBaseURL ."assets/css/colorbox.css");
      $modx->regClientCSS(FlexiConfig::$sFlexiBaseURL ."assets/css/smoothness/jquery-ui-1.8.4.custom.css");
      $modx->regClientCSS(FlexiConfig::$sFlexiBaseURL ."assets/css/modx2.style.css");
      $modx->regClientCSS(FlexiConfig::$sFlexiBaseURL ."assets/css/flexi.api.css");

      $modx->regClientStartupScript(FlexiConfig::$sFlexiBaseURL ."assets/js/jquery-1.4.2.min.js");
      $modx->regClientStartupScript(FlexiConfig::$sFlexiBaseURL ."assets/js/jquery-ui-1.8.2.custom.min.js");
      $modx->regClientStartupScript(FlexiConfig::$sFlexiBaseURL ."assets/js/jquery.colorbox-min.js");
      $modx->regClientStartupScript(FlexiConfig::$sFlexiBaseURL ."assets/js/flexi.api.js");
    }
    
	}

  public function methodDefault() {}

  public function permission($sTitle) {
    return FlexiConfig::getLoginHandler()->hasAccessToPolicy("settings");
    //return FlexiConfig::getLoginHandler()->isSuperUser();
    //return FlexiConfig::getLoginHandler()->hasPermission("admin");
  }
}