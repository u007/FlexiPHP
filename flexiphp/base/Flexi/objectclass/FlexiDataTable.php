<?php

abstract class FlexiDataTable
{
	protected $sTable = "";
	protected $_data = array();
	
	public function __construct($sTable)
	{
		$this->sTable = FlexiConfig::$sDBPrefix . $sTable;
	}
	
	/**
	 * map database table field name with object name of class
	 * @return array("dbfield" => "objectfield");
	 */
	public abstract array function getDataMap();
	
	function __set($asName, $asValue)
	{
		$sValue = $this->onBeforeSet($asValue);
		$this->_data[$asName] = $sValue;
	}
	
	/**
	 * Override before setting value
	 * @param string name
	 * @param mixed value
	 * @return mixed value
	 */
	function onBeforeSet($asName, & $asValue)
	{
		return $asValue;
	}
	
	function __get($asName)
	{
		$sValue = isset($this->_data[$asName]) ? $this->_data[$asName] : null;
		$sValue = $this->onBeforeGet($asName, $sValue);
		
		return $sValue;
	}
	
	/**
	 * Override before getting value
	 * @param string name
	 * @param mixed value
	 * @return mixed
	 */
	function onBeforeGet($asName, & $asValue)
	{
		return $asValue;
	}
	
	function __isset($asName) {
		return isset($this->_data[$asName]);
	}

	function __unset($name) {
		unset($this->_data[$name]);
	}
	
}
