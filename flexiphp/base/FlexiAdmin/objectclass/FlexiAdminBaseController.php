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
      $modx->regClientCSS(FlexiConfig::$sFlexiBaseURL ."assets/jquery/jquery-ui-1.8.9.custom.css");
      $modx->regClientCSS(FlexiConfig::$sFlexiBaseURL ."lib/uniform/css/uni-form.css");
      $modx->regClientCSS(FlexiConfig::$sFlexiBaseURL ."lib/uniform/css/default.uni-form.css");

      $modx->regClientCSS(FlexiConfig::$sFlexiBaseURL ."assets/css/modx2.style.css");
      $modx->regClientCSS(FlexiConfig::$sFlexiBaseURL ."assets/css/flexi.api.css");
      $modx->regClientCSS(FlexiConfig::$sAssetsURL ."flexitemplate/oo/style.css");

      $modx->regClientStartupScript(FlexiConfig::$sFlexiBaseURL."assets/jquery/jquery-1.5.min.js");
      $modx->regClientStartupScript(FlexiConfig::$sFlexiBaseURL."assets/jquery/jquery-ui-1.8.9.custom.min.js");
      $modx->regClientStartupScript(FlexiConfig::$sFlexiBaseURL."assets/jquery/jquery.form.js");
      $modx->regClientStartupScript(FlexiConfig::$sFlexiBaseURL ."assets/js/jquery.colorbox-min.js");
      $modx->regClientStartupScript(FlexiConfig::$sFlexiBaseURL."assets/jquery/jquery.noconflict.js");
      
      $modx->regClientStartupScript(FlexiConfig::$sFlexiBaseURL."assets/js/flexi.api.js");

      $modx->regClientStartupScript(FlexiConfig::$sAssetsURL."js/system.js");
    }
	}

  public function methodDefault() {}

  public function permission($sTitle) {
    return FlexiConfig::getLoginHandler()->hasAccessToPolicy("settings");
    //return FlexiConfig::getLoginHandler()->isSuperUser();
    //return FlexiConfig::getLoginHandler()->hasPermission("admin");
  }
}