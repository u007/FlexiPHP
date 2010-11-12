<?php

class defaultController extends FlexiBaseController 
{
	function onInit()
	{
		//set not page layout
		//$this->setLayout("");
	}
	
	function methodDefault()
	{
		//$this->oView->addVar("top", "this is top");
		
		//echo $this->renderView("default");
		return true;
	}
	
	function methodPaypal()
	{
		
	}
}
