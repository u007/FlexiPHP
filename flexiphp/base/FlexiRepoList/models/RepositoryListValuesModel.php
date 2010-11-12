<?php

class RepositoryListValuesModel extends FlexiModel
{
	
	public function getByKeys($sKey)
	{
		return $aKeys = $this->getDBQuery("RepositoryListValuesTable", "flexiphp/base/FlexiRepoList")->where("listkey=?", $sKey)->
			orderBy("weight asc")->execute();
	}
	
	public function getOptionsRowsByKey($sKey)
	{
		$aList = $this->getByKeys($sKey);
		return $aList->toArray();
	}
	
	public function getOptionsArrayByKey($sKey)
	{
		$aList = $this->getOptionsRowsByKey($sKey);
		
		$aResult = array();
		foreach( $aList as $aRow )
		{
			$aResult[(String)$aRow["listvalue"]] = $aRow["listlabel"];
		}
		
		return $aResult;
	}
	
}
