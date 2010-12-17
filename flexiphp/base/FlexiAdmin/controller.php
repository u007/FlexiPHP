<?php

class FlexiAdminController extends FlexiAdminBaseController
{

  function methodBlank() {
    $this->setLayout("");
    return false;
  }

  function renderViews()
	{
    if (FlexiConfig::$sFramework == "modx" || FlexiConfig::$sFramework == "modx2") {
      $this->oView->addVar("header", $this->renderView("header"));
      $this->oView->addVar("top", FlexiConfig::getLoginHandler()->getUserLanguage() );
      $this->oView->addVar("left", "left-");
      $this->oView->addVar("right", "-right");

      $this->oView->addVar("footer", "-footer-");
    }
	}


  function methodPhpInfo() {
    return true;
  }
  
	function methodDefault()
	{
		return true;
	}
	
}
