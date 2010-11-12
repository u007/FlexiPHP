<?php

class MyTestController extends FlexiBaseController
{

	public function onInit()
	{
		//echo "init";
	}
	
	public function methodIndex()
	{
		
	}
	
	public function methodYyy()
	{
		//echo "haha";
		$this->oView->addVar("xyz","this is good stuff");
	}
	
	public function onEvent($asEventType, $aoParam)
	{
		
	}
	
	
}



