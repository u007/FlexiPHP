<?php

abstract class FlexiModel
{
	protected $oDb = null;
	
	function __construct()
	{
		
	}
	
	/**
	 * to replace or add result
	 * @param string find
	 * @param array (key=>values)
	 * @param array of FlexiSearchResult[]
	 */
	protected function onFind($asFind, $params, & $aResults) {}
	/**
	 * @param string type: "insert" / "update"
	 * @return boolean true / false
	 */
	abstract function validate($sType);
	
	/**
	 * Any code before validation occur
	 */
	protected function beforeValidate() {}
	
	/**
	 * @return boolean: true: proceed, false otherwise
	 */
	protected function beforeSave() {}
	
	/**
	 * Any followup code after save
	 */
	protected function afterSave() {}
	
	/**
	 * @return boolean: true: proceed, false otherwise
	 */
	protected function beforeDelete($params) {}
	
	/**
	 * Any cleanup code after delete
	 */
	protected function afterDelete($params) {}
	
	/**
	 * Set database
	 * @param link
	 */
	function setDB($oDb)
	{
		$this->oDb = $oDb;
	}
	
	/**
	 * Get DB
	 * @param boolean force new connection if did not exists
	 * @return link
	 */
	function getDB($bNew = false)
	{
		if (!isset($this->oDb))
		{
			$this->oDb = FlexiModelUtil::getDBInstance($bNew);
		}
		return $this->oDb;
	}
	
}
