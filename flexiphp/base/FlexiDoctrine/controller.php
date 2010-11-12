<?php

class FlexiDoctrineController extends FlexiAdminBaseController
{

  function renderViews()
	{
    if (FlexiConfig::$sFramework == "modx" || FlexiConfig::$sFramework == "modx2") {
      $this->oView->addVar("header", $this->renderView("header"));
      $this->oView->addVar("top", FlexiConfig::getLoginHandler()->getUserLanguage() );
      $this->oView->addVar("left", "left-");
      $this->oView->addVar("right", "-right");

      $this->oView->addVar("footer", "-footer-");
    }

    if (! empty(FlexiConfig::$sFramework))
		{
			$this->setLayout("");
		} else {
      $this->oView->setTemplate(FlexiConfig::$sAdminTemplate);
    }
	}
	
	function methodDefault()
	{
		return true;
	}
	
	function methodGenerateModel()
	{
		Doctrine_Core::generateModelsFromDb('flexiphp/assets/temp/models', array('doctrine'), array('generateTableClasses' => true));
		return true;
	}
}
