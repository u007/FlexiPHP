<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class FlexiAdminUserController extends FlexiAdminBaseController {

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
					$oModel = $this->getDBQuery("ModxWebUsers")->where('id=?', $sId)->fetchOne();
					if ($oModel !==false)
					{
						$oModel->Attributes->delete();
            $oModel->Groups->delete();
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

		return $this->redirectControl(null, "default");
	}


  function methodProcessform() {
    if (FlexiConfig::$sFramework == "modx" || FlexiConfig::$sFramework == "modx2") { return $this->methodModXProcessform(); }

    return true;
  }
  function methodModXProcessform() {
    $iId = $this->getRequest("rid");

    $aForm = $this->loadForm();

    if (! empty($iId)) {
      $oModel = $this->getDBQuery("ModxWebUsers")->where("id=?", array($iId))->fetchOne();

      if ($oModel === false) {
        FlexiLogger::error(__METHOD__, "Record not found.");
        $this->addMessage("Record not found", "error");
        return $this->renderForm($aForm);
      }

      $oModel 					= $this->getModelFromForm($oModel, $aForm["form"]);
    } else {
      $oModel 					= $this->getModelFromForm("ModxWebUsers", $aForm["form"]);
    }

    $oModel->Attributes->fullname = $aForm["form"]["txtFullName"]["#value"];

    
		//$oModel->dob 					= "1980-01-01";
		if (! $this->validateFormByModel($aForm["form"], $oModel)
			|| ! $this->validateForm($aForm["form"]))
		{
			FlexiLogger::error(__METHOD__, "Validation failed: " . $oModel->getErrorStackAsString());
			$this->addMessage("Validation failed", "error");
			return $this->renderForm($aForm);
		}
		FlexiLogger::debug(__METHOD__, "ok");

		try
		{
			$oModel->replace();

      $oGroupModel = $this->getModelInstance("ModxWebGroups");
      $oGroupModel->webuser = $oModel->id;
      $oGroupModel->webgroup = 2; //hardcoded to registered user
      $oGroupModel->replace();
			//$this->oView->addVar("message", "saved.");
			$this->addMessage("Saved", "success");

			return $this->runControl("default");
		}
		catch (Exception $e)
		{
			FlexiLogger::error(__METHOD__, "Save failed:" . $e->getMessage());
      $this->addMessage("Error saving: " . $e->getMessage(), "error");
			return $this->runControl("form");
		}
  }

  function loadForm() {
    $aRow = array();
		$rid = $this->getRequest("rid");

		if (! is_null($rid))
		{
			$oModel = $this->getDBQuery("ModxWebUsers u")
        ->leftJoin("u.Attributes a")
        ->where('u.id=?', $rid)->fetchOne();

      if ($oModel !== false) {
        $aRow = array_merge($aRow, $oModel->toArray());
        $aRow["fullname"] = $oModel->Attributes->fullname;
      }
		}

    $aForm = FlexiController::getInstance()->getAllRequest();
    if (isset($aForm["txtPassword"]) && !empty($aForm["txtPassword"])) {
      $aForm["txtPassword"] = md5($aForm["txtPassword"]);
    }
    
    $aRow = array_merge($aRow, $aForm);

    //var_dump($aRow);
		return $this->getFormConfig($aRow);
  }

  function getFormConfig($aRow = null)
	{
		$aForm = array(
			"rid" =>
				array("#type" => "hidden",
					"#default_value" => $this->getRequest("rid"), "#update" => true, "#dbfield" => "id", "#weight" => 3),
      
			"txtUserName" =>
				array("#type" => "textfield", "#title" => "User Name", "#weight" => 5, "#maxlength" => 100,
					"#default_value" => "", "#size" => 30, "#required" => true, "#dbfield" => "username",
          "#update" => true, "#insert" => true),

     "txtFullName" =>
				array("#type" => "textfield", "#title" => "Full Name", "#weight" => 5, "#maxlength" => 255,
					"#default_value" => "", "#size" => 30, "#required" => true, "#dbfield" => "fullname",
          "#update" => true, "#insert" => true),

     "txtPassword" =>
				array("#type" => "password", "#title" => "Password", "#weight" => 5, "#maxlength" => 100,
					"#default_value" => "", "#size" => 30, "#dbfield" => "password",
          "#update" => true, "#insert" => true),
					
			"mysubmit" =>
				array("#type" => "submit.raw", "#value" => "Save", "#weight" => 57,
				),

      "bcancel" =>
				array("#type" => "button.raw", "#value" => "cancel", "#weight" => 58,
          "#attributes" => array("onClick" => "document.location.href='" .
            $this->url(null, "default") . "'")
				)

			);

		//fill in form value
		$this->prepareForm($aForm, $aRow);
		$aForm["formtype"] = array("#type" => "hidden", "#value" => empty($aForm["rid"]["#value"]) ? "insert" : "update");

    if ($aForm["formtype"]["#value"] == "insert") {
      $aForm["txtPassword"]["#required"] = true;
    }
		//var_dump($aForm);
		$aTheForm = array_merge($aForm, array("#type" => "form", "#upload" => true, "#method" => "post",
			"#action" => $this->url("", "processform")));

		$aTheFieldSet = array(
			"#type" => "fieldset",
			"#title" => "Admin: User Form",
			"form" => $aTheForm
		);

		return $aTheFieldSet;
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
    $aForm["form"]["txtPassword"]["#value"] = "";
		$sForm = $this->renderMarkup($aForm);
    //reset password b4 displaying
		$this->oView->addVar("form", $sForm);
		$this->setViewName("form");
		return true;
	}
  
  function methodDefault() {

    switch(FlexiConfig::$sFramework) {
      case "modx":
      case "modx2":
        return $this->runControl("ModXDefault");
        break;

      default:
        
    }
    
    return false;
  }

  function methodModXDefault() {
    if (FlexiConfig::$sFramework != "modx" && FlexiConfig::$sFramework != "modx2") { throw new FlexiException(FlexiConfig::$sFramework, ERROR_FRAMEWORK); }
    $aList = $this->getDBQuery("ModxWebUsers")
      ->execute();

    $this->oView->addVar("list", $aList);
    return true;
  }

}

?>
