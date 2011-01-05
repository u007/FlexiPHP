<?php

class FlexiRepoListController extends FlexiAdminBaseController
{
  public function onInit() {
    parent::onInit();
  }
	
	function renderViews()
	{
		$this->oView->addVar("header", $this->renderView("header"));
		$this->oView->addVar("top", FlexiConfig::getLoginHandler()->getUserLanguage() );
		$this->oView->addVar("left", "left-");
		$this->oView->addVar("right", "-right");
		
		$this->oView->addVar("footer", "-footer-");
	}
	
	function methodOp()
	{
		$sOp = $this->getRequest("op");
		
		if ($sOp == "delete")
		{
			$aCheck = $this->getRequest("checkList");
			
			if (count($aCheck) < 1)
			{
				$this->addMessage("No record selected", "warn");
			}
			else
			{
				foreach($aCheck as $sId)
				{
					$oModel = $this->getDBQuery("RepositoryListValuesTable")->where('id=?', $sId)->fetchOne();
					if ($oModel !==false)
					{
						$oModel->delete();
						$this->addMessage("Deleted record: " . $sId, "success");
					}
					else
					{
						$this->addMessage("No such record: " . $sId, "error");
					}
					//$oModel->delete();
					//$oModel = $this->getModelFromForm("RepositoryListValuesTable", $aForm["form"]);
				}
			}
		}
		else
		{
			return $this->runControl("default");
		}
		
		return $this->redirectControl(null, "default");
	}
	
	function methodProcessForm()
	{
		$aForm = $this->loadForm();
		//var_dump($aForm["form"]);
		if (! $this->validateForm($aForm["form"]))
		{
			FlexiLogger::error(__METHOD__, "Validation failed");
			$this->addMessage("Validation failed", "error");
			return $this->renderForm($aForm);
		}
		//var_dump($this->getRequest("txtListKey"));
		$oModel = $this->getModelFromForm("RepositoryListValuesTable", $aForm["form"]);
		
		if (!$oModel->isValid())
		{
			FlexiLogger::error(__METHOD__, "Validation failed.");
			$this->addMessage("Validation failed.", "error");
			return $this->runControl("form");
		}
		
		try
		{
			$oModel->replace();
			//$this->oView->addVar("message", "saved.");
			$this->addMessage("Saved", "success");
			
			return $this->redirectControl(null, "default");
		}
		catch (Exception $e)
		{
			FlexiLogger::error(__METHOD__, "Save failed:" . $e->getMessage());
			return $this->runControl("form");
		}
		
	}
	
	function formListValues($aRow = null)
	{
    $sFilter = $this->getFilter();
		$aForm = array(
			"rid" =>
				array("#type" => "hidden", 
					"#default_value" => $this->getRequest("rid"), "#update" => true, "#dbfield" => "id", "#weight" => 3,),
			"txtListKey" => 
				array("#type" => "textfield", "#title" => "Key", "#weight" => 5, "#maxlength" => 255,
					"#default_value" => $sFilter, "#size" => 30, "#description" => "Key Name", "#required" => true,
					"#insert" => true, "#update" => true, "#dbfield" => "listkey"),
					
			"txtListValue" => 
				array("#type" => "textfield", "#title" => "Value", "#weight" => 6, "#maxlength" => 255,
				"#default_value" => "", "#size" => 30, "#description" => "Field value",
					"#insert" => true, "#update" => true, "#dbfield" => "listvalue"), 
			
			"txtLabel" => 
				array("#type" => "textarea", "#title" => "Label", "#default_value" => "", "#weight" => 6, "#maxlength" => 1000,
				"#cols" => 30, "#rows" => 4, "#description" => "To display", "#required" => true,
				"#insert" => true, "#update" => true, "#dbfield" => "listlabel"
				),
			
			"txtWeight" => 
				array("#type" => "textfield", "#title" => "Weight", "#default_value" => "999", "#weight" => 7, "#size" => 30,
				"#insert" => true, "#update" => true, "#dbfield" => "weight"),
			
			"mybutt12" => 
				array("#type" => "submit", "#value" => "Save", "#weight" => 57,
				)
				
			);
		
		//fill in form value
		$this->prepareForm($aForm, $aRow);
		
		
		$aForm["formtype"] = array("#type" => "hidden", "#value" => empty($aForm["rid"]["#value"]) ? "insert" : "update");
		
		//var_dump($aForm);
		$aTheForm = array_merge($aForm, array("#type" => "form", "#upload" => true, "#method" => "post",
			"#action" => $this->url("", "processform")));
		
		$aTheFieldSet = array(
			"#type" => "fieldset",
			"#title" => "Repository List Form",
			"form" => $aTheForm
		);
		
		return $aTheFieldSet;
	}
	
	/**
	 * load the form from "rid",
	 * 	into formListValues
	 * @return array
	 */
	function loadForm()
	{
		$aRow = null;
		$rid = $this->getRequest("rid");
    //var_dump($rid);
		if (! is_null($rid))
		{
			$aList = $this->getDBQuery("RepositoryListValuesTable")->where('id=?', $rid)->execute();
			
			if (count($aList) > 0)
			{
				$aRow = $aList[0]->toArray();
			}
		}
		//getting values from form
		if (! is_null($aRow))
		{
      $aReq = FlexiController::getInstance()->getAllRequest();
      unset($aReq["id"]); //avoiding use of id
			$aRow = array_merge($aRow, $aReq);
		}
		else
		{
			$aRow = FlexiController::getInstance()->getAllRequest();
      unset($aRow["id"]); //avoiding use of id
		}
		
		return $this->formListValues($aRow);
	}
	
	/**
	 * load form directly for a direct or fresh form
	 */
	function methodForm()
	{
		return $this->renderForm($this->loadForm());
	}
	
	/**
	 * actual form renderer,
	 * @param array form
	 */
	function renderForm($aForm)
	{
		$sForm = $this->renderMarkup($aForm);
		
		$this->oView->addVar("form", $sForm);
    $this->setViewName("form");
		return true;
	}

  function getFilter() {
    static $sFilter = null;
    if (is_null($sFilter)) {
      $sFilter = $this->getRequest("filter_listkey", null);
      if (is_null($sFilter)) {
        $sFilter = $this->getSession("repolist_filter", "");
      } else {
        $this->setSession("repolist_filter", $sFilter);
      }
    }
    return $sFilter;
  }


	function methodDefault()
	{
		$iPage = (int)$this->getRequest("page", 1);
		$iRecordPerPage = 20;
		
		$aKeys = $this->getDBQuery("RepositoryListValuesTable")->select("distinct(listkey) as listkey")->
			orderBy("listkey asc")->execute();
		
		$this->oView->addVar("aKeys", $aKeys);
		$oQuery = $this->getDBQuery("RepositoryListValuesTable")->
			limit($iRecordPerPage)->offset(($iPage-1)*$iRecordPerPage)->orderBy("listkey asc, weight asc, listvalue asc");
		$oCountQuery = $this->getDBQuery("RepositoryListValuesTable");
    
		$sFilter = $this->getFilter();
		if (!empty($sFilter))
		{
			$oQuery->where("listkey=?", $sFilter);
      $oCountQuery->where("listkey=?", $sFilter);
		}
		
		$this->oView->addVar("filter_listkey", $sFilter);
		$aList = $oQuery->execute();
		//for more condition, add where clause 
		$iCount = FlexiModelUtil::getRecordCount($oCountQuery);
		//var_dump($iCount);
		
		$this->oView->addPaging($iCount, $iRecordPerPage, $iPage, array(), null);
		
		$this->oView->addVar("aList", $aList->toArray());

    if (FlexiConfig::$sFramework == "modx" || FlexiConfig::$sFramework == "modx2") {
      $sForm = $this->renderMarkup($this->loadForm());
      $this->oView->addVar("form", $sForm);
    }
		return true;
	}
	
	public function permission($sMethod)
	{
		return true;
	}
	
}
