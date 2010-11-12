<?php

abstract class FlexiModel
{
	
	/**
	 * Get Doctrine query object
	 * @param string name
	 * @param string path (optional)
	 * @return Doctrine_Record
	 */
	public function getDBQuery($asName, $asPath)
	{
		$this->loadModel($asName, $asPath);
		return Doctrine_Query::create()->from($asName);
	}
	
	/**
	 * load model
	 * @param string name
	 * @param path (optional)
	 */
	public function loadModel($asName, $asPath)
	{
		FlexiModelUtil::includeModelFile($asName, $asPath);
	}
	
}
