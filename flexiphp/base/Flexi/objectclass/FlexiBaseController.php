<?php

abstract class FlexiBaseController
{
	protected $oView = null;
	protected $sMethod = "";
	protected $sModelPath = "";
	//sub template
	protected $sViewName = "";
	protected $sModulePath = "";
	
	//main page template
	protected $sLayoutTemplate = "page";

  protected $sRenderViewName = "";
	
	protected $aViewVars = array();
  protected $sModule = "";

  protected $aRequestData = null;

  protected $oViewManager = null;
  protected $oObjectManager = null;

  protected $aCSS = array();
  protected $aJS  = array();

  /**
   * @param FlexiView $aoView
   * @param String $asMethod
   * @param String $asPath path to controller
   */
	public function __construct($aoView, $asMethod, $asPath)
	{
		$this->oView 				= $aoView;
		$this->sMethod			= $asMethod;
		$this->sViewName 		= $asMethod;
		$this->sModulePath	=	$asPath;
    $this->sModelPath   = $asPath . "/models/";
		
		$this->sModule = substr(get_class($this), 0, -10);
		
		$this->aViewVars["title"] = FlexiConfig::$sPageTitle;
    //$this->checkSetup();
		$this->onInit();
	}

  public function regClientCSS($sPath, $sMedia="all") {
    $this->oView->addCSS($sPath, $sMedia);
  }

  public function regClientStartupScript($sPath, $sType="text/javascript") {
    $this->oView->addJS($sPath, $sType);
  }

  public function setViewManager($sName) {
    $this->oViewManager = $this->getService($sName);
    $this->oViewManager->setView($this->oView);
    $this->oViewManager->setObjectListManager($this->oObjectManager);
    
    return $this->oViewManager;
  }

  public function setObjectManager($sName) {
    $this->oObjectManager = $this->getService($sName);
    $this->oView->addVar("service", $this->oObjectManager);
    return $this->oObjectManager;
  }

  public function loadModelPackage($sName, $sPrefix="") {
    if (FlexiConfig::$sFramework=="modx2") {
      global $modx;
      $modx->addPackage($sName,FlexiConfig::$sBaseDir . 'assets/',$sPrefix);
    }
  }

  /**
   * Set page title
   * @global modX $modx
   * @param String $sTitle
   */
  public function setTitle($sTitle) {
    FlexiConfig::$sPageTitle = $sTitle;

    if (FlexiConfig::$sFramework =="modx" || FlexiConfig::$sFramework =="modx2") {
      global $modx;
      $modx->documentObject['pagetitle'] = $sTitle;
    }
  }

  /**
   * Set html header
   * @param String $sName
   * @param mixed $sValue
   */
  public function setHeader($sName, $sValue) {
    FlexiController::getInstance()->setHeader($sName, $sValue);
  }
	/**
	 * to be run after running controller
   * @param String $sMethod
   * @param boolean $bReturn //success running controller method by returning true/false
	 */
  function afterControl($sMethod, $bReturn = false)
	{
    //echo "aftercontrol, layout: " . $this->sLayoutTemplate;
    $this->oView->addVar("#message", 			FlexiConfig::$aMessage);
//    if (FlexiConfig::$sFramework == "modx" || FlexiConfig::$sFramework =="modx") {
//    }
    $this->onAfterControl($sMethod, $bReturn);
	}
  
  function renderViews() { }

  /**
   * Override, called in @afterControl
   * @param String $sMethod
   * @param String $bReturn
   */
  public function onAfterControl($sMethod, $bReturn = false) {
    
  }

  /**
   * Set the variable name holding the rendered view
   *  the view template name is called using setViewName(...)
   * @param String $sViewVarName
   */
  public function setRenderViewName($sViewVarName) {
    $this->sRenderViewName = $sViewVarName;
  }
  /**
   * Get URL path to load without layout
   * @param mixed $asURL, a url string, or array of hash of parameter to be joined as querystring
   * @param String $asMethod method name
   * @param String $asModule module name
   * @return <type>
   */
  public function ajaxURL($asURL, $asMethod=null, $asModule=null)
  {
    //getting class name without "Controller"
		$sModule 	= is_null($asModule) ? substr(get_class($this), 0, -10) : $asModule;
		$sMethod 	=	is_null($asMethod) ? $this->sMethod : $asMethod;

		//var_dump($sModule);
		$sURL = $asURL;
		if (! empty($sModule) && !empty($sMethod))
		{
			$sURL = "?" . FlexiConfig::$aModuleURL["module"] . "=" . $sModule . "&" . FlexiConfig::$aModuleURL["method"] . "=" . $sMethod . "&" . $sURL;
		}

		return flexiURL($sURL, true);
  }

  /**
   * Get URL path
   * @param mixed $asURL, a url string, or array of hash of parameter to be joined as querystring
   * @param String $asMethod method name
   * @param String $asModule module name
   * @param boolean $bAjax true to open a url without layout, otherwise
   * @return String
   */
	public function url($asURL, $asMethod=null, $asModule=null, $bAjax = false)
	{
		//getting class name without "Controller"
		$sModule 	= is_null($asModule) ? substr(get_class($this), 0, -10) : $asModule;
		$sMethod 	=	is_null($asMethod) ? $this->sMethod : $asMethod;

    $sURL = "";
    if (gettype($asURL) == "string") {
      $sURL = $asURL;
    } else if (gettype($asURL) == "array") {
      $aParamQuery = array();
      foreach($asURL as $sKey => $sValue)
      {
        $aParamQuery[] = $sKey . "=" . $sValue;
      }
      $sURL = implode("&", $aParamQuery);
    }
		//var_dump($sModule);
		
		if (! empty($sModule) && !empty($sMethod))
		{
			$sURL = "?" . FlexiConfig::$aModuleURL["module"] . "=" . $sModule . "&" . FlexiConfig::$aModuleURL["method"] . "=" . $sMethod . "&" . $sURL;
		}
		
		return flexiURL($sURL, $bAjax);
	}
  
  public function runReferrerControl() {

    //WARNING, THIS COULD LEAD TO INFINITE LOOP
    $sLastURL = FlexiPlatformHandler::getReferrerURL();

    //var_dump($sLastURL);
    //echo __METHOD__ . ":baseurl: " . FlexiConfig::$sBaseURL;
    $iPos = strpos(strtolower($sLastURL), strtolower(FlexiConfig::$sBaseURL));
    //var_dump($iPos);
    if($iPos !== false && $iPos == 0) {
      //is this site
      $aURL = FlexiURLUtil::parseURL($sLastURL);
      $sMethod = "default";
      $sModule = "default";

      $aQuery = $aURL["aQuery"];
      if (count($aQuery) > 0) {

        $sRequestModule =
        $sQueryModule = FlexiConfig::getRequestModuleVarName();
        $sQueryMethod = FlexiConfig::getRequestMethodVarName();
        
        if (isset($aQuery[$sQueryModule]) && ! empty($sQueryModule)) {
          $sModule = $aQuery[$sQueryModule];
        }

        if (isset($aQuery[$sQueryMethod]) && ! empty($sQueryMethod)) {
          $sMethod = $aQuery[$sQueryMethod];
        }
      }
      FlexiLogger::debug(__METHOD__, "running referrer: " . $sModule . "," . $sMethod);
      
      $bResult = $this->runControl($sMethod, $sModule);
      
      FlexiPlatformHandler::getPlatformHandler()->forceDie();
    }
    else {
      //if is not from this site, return to the calling url
      return $this->redirectURL($sLastURL);
    }
  }

  /**
   * Same as runcontrol, and pass on renderviewname
   * @param String $asMethod
   * @param String $asModule
   * @param boolean $abRenderView
   * @return boolean
   */
  public function runControl($asMethod, $asModule = null) {
    //echo "Caling: " .$asModule . ", view: " . $this->sRenderViewName;
    $bResult = $this->_runControl($asMethod, $asModule, $this->sRenderViewName);
    //disable view for this caller
    $this->setRenderViewName("");
    return $bResult;
  }

  /**
   * Run service
   * @param String $asMethod
   * @param String $asModule
   * @return mixed
   */
	public function runService($asMethod, $asModule = null, $aaData = array())
	{
		$sMethod = "service" . ucfirst($asMethod);
		$sModule = get_class($this);

    $aData = array();
    if (!is_null($aaData)) {
      $aData = $aaData;
    }
    $this->aRequestData = $aData;
    
    if (! empty($asModule) && $sModule != $asModule . "Controller") {
      FlexiLogger::debug(__METHOD__, "Calling other class: " . $sModule . "::" . $sMethod);
      $aResult = FlexiController::getInstance()->runService($asModule, $asMethod);
      return $aResult["return"];
    }

		FlexiLogger::debug(__METHOD__, "Calling: " . $sModule . "::" . $sMethod);
		if (! method_exists($this, $sMethod))
		{
			FlexiLogger::error(__METHOD__, "module: " . $sModule . ", method: " . $asMethod . " is missing.");
			return null;
		}
    
    Flexilogger::debug(__METHOD__, "Checking permission: " . $asMethod);
		$this->checkPermission($asMethod);
		Flexilogger::debug(__METHOD__, "Permission (OK): " . $asMethod);
		
    //Flexilogger::debug(__METHOD__, "Setted view");
		$sModuleName = substr($sModule, 0, -10);
		//$this->oView->addVar("#modulepath", FlexiController::getControllerPath($sModuleName));
    Flexilogger::debug(__METHOD__, "Calling beforeControl: " . $asMethod);

    Flexilogger::debug(__METHOD__, "Running method: " . $sMethod);
		$aResult = $this->$sMethod($aData);
		FlexiLogger::debug(__METHOD__, "Return: " . $sModuleName . "::" . $sMethod . ": " . serialize($aResult));

		return $aResult;
	}

  /**
   * Run control with custom view variable name or no render view
   * @param String $asMethod
   * @param String $asModule
   * @param String $asRenderViewName
   * @return boolean
   */
	public function _runControl($asMethod, $asModule = null, $asRenderViewName = "")
	{
		$sMethod = "method" . ucfirst($asMethod);
		$sModule = get_class($this);

    if (! empty($asModule) && $sModule != $asModule . "Controller") {
      FlexiLogger::debug(__METHOD__, "Calling other class: " . $sModule . "::" . $sMethod);
      $aResult = FlexiController::getInstance()->_run($asModule, $asMethod, $this->oView);
      return $aResult["return"];
    }

		FlexiLogger::debug(__METHOD__, "Calling: " . $sModule . "::" . $sMethod);
		if (! method_exists($this, $sMethod))
		{
			FlexiLogger::error(__METHOD__, "module: " . $sModule . ", method: " . $asMethod . " is missing.");
      die("method: " . $asMethod . " of module: " . $sModule . " does not exists");
			return false;
		}

		$this->setViewName($asMethod);
    //Flexilogger::info(__METHOD__, "Setting Render view var name: " . $asRenderViewName);
    $this->setRenderViewName($asRenderViewName);
    
    //Flexilogger::info(__METHOD__, "Checking permission: " . $asMethod);
		$this->checkPermission($asMethod);
		Flexilogger::debug(__METHOD__, "Permission (OK): " . $asMethod);
    //Flexilogger::debug(__METHOD__, "Setted view");
		$sModuleName = substr($sModule, 0, -10);
		$this->oView->addVar("#module", $sModuleName);
		$this->oView->addVar("#method", $asMethod);

    $sModulePath = FlexiController::getControllerPath($sModuleName);
    //+1 due to extra /
		//$this->oView->addVar("#modulepath", substr($sModulePath, strlen(FlexiConfig::$sRootDir)+1));
    $this->oView->addVar("#modulepath", $sModulePath);
    //if (FlexiConfig::$bIsAdminPath) echo "root: " . FlexiConfig::$sRootDir . "\r\n<br/>";
    //if (FlexiConfig::$bIsAdminPath) echo "modpath: " . $sModulePath . "\r\n<br/>";

    if ((FlexiConfig::$sFramework == "modx" || FlexiConfig::$sFramework == "modx2")
            && FlexiConfig::$bIsAdminPath) {
      $sRootDir = dirname(FlexiConfig::$sRootDir);
      $this->oView->addVar("#moduleurl",
        (FlexiConfig::$sFramework == "modx2" ? "../" : "") . substr($sModulePath, strlen($sRootDir)+1));
    } else {
      $sRootDir = FlexiConfig::$sRootDir;
      $this->oView->addVar("#moduleurl", substr($sModulePath, strlen($sRootDir)+1));
    }
    
    //Flexilogger::debug(__METHOD__, "Calling beforeControl: " . $asMethod);
		if (! $this->beforeControl($asMethod)) { return false; }
    //Flexilogger::debug(__METHOD__, "Running method: " . $sMethod);
    //echo "b4: " . $this->sLayoutTemplate;
		$bReturn = $this->$sMethod();
    //echo "after: " . $this->sLayoutTemplate;
    //die();
		//FlexiLogger::debug(__METHOD__, "Return: " . $sModuleName . "::" . $sMethod . ": " . serialize($bReturn));
		if (is_null($bReturn)) { throw new FlexiException("No return after for: " . $sModuleName . "::" . $sMethod, ERROR_RETURNVALUE); }
		$this->afterControl($asMethod, $bReturn);
    
    if (!empty($this->sRenderViewName)) {
      //echo "viewvarname: " . $this->sRenderViewName;
      //Flexilogger::info(__METHOD__, "Rendering View: " . $this->sRenderViewName . ":" . $this->sViewName . " method:" . $asMethod);
      $this->oView->addVar($this->sRenderViewName, $this->renderView());
    } else {
      Flexilogger::debug(__METHOD__, "No view to render: " . $this->sRenderViewName);
    }

    //FlexiLogger::info(__METHOD__, "rendered output: " . $this->oView->getVar($this->sRenderViewName));
		return $bReturn;
	}
	
	/**
	 * Check permission for a calling method
	 * @param string method
	 */
	public function checkPermission($asMethod)
	{
		if (! $this->permission($asMethod))
		{
			//FlexiLogger::error(__METHOD__, "Permission denied: " . get_class($this) . ":" . $asMethod);
      //FlexiController::getInstance()->redirectURL(FlexiConfig::$sBaseURL . "?" .
      //  FlexiConfig::getRequestModuleVarName(). "=FlexiLogin&" . FlexiConfig::getRequestMethodVarName() . "=denied");
      FlexiController::getInstance()->redirectURL(FlexiConfig::$sLoginURL);
      FlexiPlatformHandler::getPlatformHandler()->forceDie();
    }
    FlexiLogger::debug(__METHOD__, "Check permission pass: " . $asMethod);
	}
	
	/**
	 * Get model instance from doctrine
	 * @param string model
	 * @param array form
	 * @param string type: "insert", "update"
	 * @return doctrine_record
	 */
	public function getModelFromForm($amModel, $aForm, $asPath=null)
	{
    if (gettype($amModel) == "string") {
      $oModel = $this->getModelInstance($amModel, $asPath);
    } else {
      
      $oModel = $amModel;
    }
		$sType = $aForm["formtype"]["#value"];

    //echo get_class($oModel);
    //$aKeys = $oModel->identifier();
    $oTable = $oModel->getTable();
    $sTable = $oTable->getTableName();
    $aKeys = $oTable->getIdentifierColumnNames();
    
    //look for record if exists
    //  only if model is string, not the model itself
    if (count($aKeys) > 0 && gettype($amModel) == "string") {
      $aKeyValues = array();
      foreach($aKeys as $sPrimaryKey) {
        $bFoundKey = false;
        
        foreach($aForm as $sKey => $mValue) {
          if (isset($mValue["#dbfield"])) {
            if ($mValue["#dbfield"] == $sPrimaryKey
              || $mValue["#dbfield"] == $sTable . "." . $sPrimaryKey) {
              $bFoundKey = true;
              if (!empty($mValue["#value"])) {
                $aKeyValues[$sPrimaryKey] = $mValue["#value"];
              }
              break;
            }
          }

          if ($bFoundKey) { break; }
        } //forech form

      } //for each key
      
      if (count($aKeys) > 0 && count($aKeyValues) == count($aKeys) ) {
        $sWhere = "";
        $aParam = array();
        foreach($aKeyValues as $sKey => $mValue) {
          $sWhere .= empty($sWhere) ? "" : " and ";
          $sWhere .= $sKey . "=?";
          $aParam[] = $mValue;
        }
        $oModel = $this->getDBQuery($amModel, $asPath)->where($sWhere, $aParam)->fetchOne();
        if ($oModel === false) {
          throw new FlexiException($amModel . ": No such record: " . $sWhere . "," . print_r($aParam,true), ERROR_EOF);
        }

        //assign value from model
        
      } //end if count keys > 0
    } //end if has primary keys
    
		//var_dump($sType);
		$oTable = $oModel->getTable();
		foreach($aForm as $sKey => $mValue)
		{
			if ($sKey[0] != "#")
			{
				if (isset($mValue["#dbfield"]))
				{
					$sField = $mValue["#dbfield"];
					$sTable = "";
					if (strpos($sField, ".")!==false)
					{
						$aField = explode(".", $sField);
						$sTable = $aField[0];
						$sField = $aField[1];
					}

          //echo "table: " . $oTable->getTableName(), ", dbfield: " . $sTable;
					if ((empty($sTable) || $sTable == $oTable->getTableName()) && $oTable->hasColumn($sField))
					{
						//echo "isset: " . $sField .":yes\r\n<br/>" . $sType;
						//var_dump($mValue["#" . $sType]);
						if (isset($mValue["#" . $sType]) && $mValue["#" . $sType])
						{
							//echo "setting: " . $mValue["#dbfield"] . "=" . $mValue["#value"] . "\r\n<br/>";
							$sValue = $mValue["#value"];
							
							if ($mValue["#type"] == "date")
							{
								$sFormat = isset($mValue["#format"]) ? $mValue["#format"] : FlexiConfig::$sInputDateFormat;
								$sFuncName = str_replace("-", "", strtoupper($sFormat));
								$sFuncName = str_replace("/", "", $sFuncName);
								$sFuncName = str_replace("/", "", $sFuncName);
								$sFuncName = "getISODateFrom" . $sFuncName;
								$sValue = FlexiStringUtil::$sFuncName($sValue);
							}
							
							//echo "setting: " . $mValue["#dbfield"] . "=" . $mValue["#value"] . "\r\n<br/>";
							$oModel->$sField = $sValue;
						}
					}
				} //end if dbfield
			} // if is not #
		}
		
		return $oModel;
	}

  /**
   * validate form array values by model object
   * @param array $aForm
   * @param object $oModel a redbean model
   * @return boolean true for validate ok, false otherwise
   */
	public function validateFormByModel(& $aForm, & $oModel)
	{
		
		try {
			$bValid = $oModel->isValid();
		}
		catch (Exception $e)
		{
			$this->addMessage("Exception validating form: " . $e->getMessage(), "error");
			return false;
		}
		
		if (!$bValid)
		{
			$bFoundInvalid = false;
			$aErrorStack = $oModel->getErrorStack();
			
			//var_dump($oModel->getErrorStack());
			$iCnt = 0;
			//var_dump($aErrorStack->toArray());
			foreach($aErrorStack as $sKey => $aError)
			{
				$sTargetField = $oModel->getTable()->getTableName() . "." . $sKey;
				//$this->addMessage("Field " . $sKey . ": " . implode(",", $aError), "error");
				foreach($aForm as $sFieldKey => & $mFieldValue)
				{
					if ($sFieldKey[0] != "#")
					{
            //if not #dbfield, skip to next sfieldkey
            if (!isset($mFieldValue["#dbfield"])) {
              continue;
            }
						$sField = $mFieldValue["#dbfield"];
						$sTable = "";
						if (strpos($sField, ".")!==false)
						{
							$aField = explode(".", $sField);
							$sTable = $aField[0];
							$sField = $aField[1];
							$sTableField = $sTable . "." . $sField;
						}
						else
						{
							//form doesnt contain table, therefore void table name
							$sTargetField = $sKey;
              $sTableField = $sField;
						}
						//echo "finding: " . $sTableField . ", loop: " . $sTargetField . "\r\n<br/>";
						if ($sTableField == $sTargetField)
						{
							$sError = "";
							foreach($aError as $sErrorType)
							{
								//echo "errotype: " . $sErrorType;
								switch($sErrorType)
								{
									case "length":
										$sError .= "Field length: " . strlen($mFieldValue["#value"]) . " exceed maximum length: " . $mFieldValue["#maxlength"];
                    $bFoundInvalid = true;
										break;
									default:
										$sError .= FlexiModelUtil::getErrorStackLabel($sErrorType) . "\r\n<br/>";
										$bFoundInvalid = true;
								}
							}
							$mFieldValue["#notice"] = array("msg" => $sError);
							break;
						}
					}
				}
			}
			
			if ($bFoundInvalid) { return false; }
		}

    FlexiLogger::debug(__METHOD__, "is valid");
		return true;
	}

  /**
   * validate a form value populated by $_REQUEST
   * @param array $aForm merged with request value
   * @return boolean 
   */
	public function validateForm(& $aForm)
	{
		$bOK = true;
		foreach($aForm as $sKey => & $mValue)
		{
			FlexiLogger::debug(__METHOD__, "validating: " . $sKey . ",val: " . @$mValue["#value"]);
			//is a form field, and is not already set value
			if ($sKey[0] != "#")
			{
				$bRequired = isset($mValue["#required"]) ? $mValue["#required"] : false;
				
				//echo $sKey. "\r\n<br>";
				if ($bRequired && (! isset($mValue["#value"]) || (isset($mValue["#value"]) && strlen($mValue["#value"])==0)))
				{
					//echo "is empty!";
          //var_dump($mValue["#value"]);
          FlexiLogger::debug(__METHOD__, "validating: " . $sKey . ", is empty");
					if (!isset($mValue["#notice"])) { $mValue["#notice"] = array("msg" => ""); }
					$mValue["#notice"]["msg"] .= flexiT("field is required") . "\r\n<br/>";
					$bOK = false;
				}

        if ($mValue["#type"] == "email" && !empty($mValue["#value"])) {
          $bValid = FlexiStringUtil::isValidEmail($mValue["#value"]);
          if (!$bValid) {
            $bOK = false;
            if (!isset($mValue["#notice"])) { $mValue["#notice"] = array("msg" => ""); }
            //echo "invalid email";
          FlexiLogger::debug(__METHOD__, "validating: " . $sKey . ", invalid email");
            $mValue["#notice"]["msg"] .= flexiT("field must be an email") . "\r\n<br/>";
          }
        }
			}
		}
		
		return $bOK;
	}

  public function mapModelToForm($oModel, & $aForm) {

    return;
    foreach($aForm as $sKey => & $mValue)
		{

			//is a form field, and is not already set value
			if ($sKey[0] != "#" && ! isset($mValue["#value"]))
			{
				
				if (isset($mValue["#dbfield"]))
				{

					$sField = $mValue["#dbfield"];

					if (isset($aValues[$sField]))
					{
						$sValue = $aValues[$sField];

						if ($mValue["#type"] == "date")
						{
							$sFormat = isset($mValue["#format"]) ? $mValue["#format"] : FlexiConfig::$sInputDateFormat;

							$sSep = "-";
							if (strpos($sFormat, "/") !== false) { $sSep = "/"; }
							else if (strpos($sFormat, ".") !== false) { $sSep = "."; }

							$sFuncName = str_replace("-", "", strtoupper($sFormat));
							$sFuncName = str_replace("/", "", $sFuncName);
							$sFuncName = str_replace(".", "", $sFuncName);

							$sFuncName = "get" . $sFuncName . "FromISODate";
							//echo "calling : " . $sFuncName;
							$sValue = FlexiStringUtil::$sFuncName($sValue, $sSep);
						}

						$mValue["#value"] = $sValue;
					}
				}
			}

			FlexiLogger::debug(__METHOD__, "trying: " . $sKey . ",val: " . @$mValue["#value"]);
			//notice msg
			if (isset($aNotice[$sKey]))
			{
				$mValue["#notice"] = $aNotice[$sKey];
			}
		}
  }

  public function prepareFormWithModel(& $aForm, $aModel = null) {
    FlexiFormUtil::mergeFormWithModel($aForm, $aModel);
  }

  public function prepareFormWithData(& $aForm, $aData = null, $sExclude=null) {
    FlexiFormUtil::mergeFormWithDataArray($aForm, $aData, $sExclude);
  }
	
	public function prepareForm(& $aForm, $asValues=null)
	{
		return FlexiFormUtil::mergeFormWithData($aForm, $asValues);
	}
	/**
	 * to be extended to run b4 running controller
	 * @param string method
	 * @return boolean: true: continue
	 */
	public function beforeControl($asMethod)
	{
		return true;
	}

  /**
   * Get View object
   * @return FlexiView
   */
	public function getView()
	{
		return $this->oView;
	}
	/**
   * Add a View variable
   * @param String $asName
   * @param mixed $amValue
   */
	public function setViewVar($asName, $amValue)
	{
		$this->aViewVars[$asName] = $amValue;
	}
  /**
   * do not render any layout or view
   */
  public function disableView() {
    $this->setLayout("");
    $this->setViewName("");
  }

	/**
	 * Setting layout for overall page layout for this controller
	 * @param string layout name
	 */
	public function setLayout($asLayoutTemplate)
	{
    //if (empty($asLayoutTemplate)) throw new Exception("someone make empty layout");
		$this->sLayoutTemplate = $asLayoutTemplate;
	}
	
	/**
	 * Setting view template to output for this method of the controller
	 * @param string name
	 */
	public function setViewName($asName)
	{
		$this->sViewName = $asName;
	}
	
	public function renderLayout()
	{
    //echo "class: " . get_class($this) . ", layout: " . $this->sLayoutTemplate;
    FlexiLogger::debug(__METHOD__, $this->sLayoutTemplate);
		foreach($this->aViewVars as $sKey => $mValue)
		{
			$this->oView->addVar($sKey, $mValue);
		}
		
		//putting up messages
		$this->oView->addVar("#message", FlexiConfig::$aMessage);
    //echo "layout: " . $this->sLayoutTemplate;
		if (! $this->beforeRender()) { return; }
		//echo "layout: " . $this->sLayoutTemplate;
		if (! empty($this->sLayoutTemplate))
		{
      //echo "has layout";
			$this->oView->addVar("header", $this->oView->render("header", null, $this->sModulePath));
			$this->oView->addVar("notice", $this->oView->render("notice", null, $this->sModulePath));
      //manually called in 
			//$this->oView->addVar("body", $this->renderView());
			$this->oView->addVar("footer", $this->oView->render("footer", null, $this->sModulePath));
			//echo "ppp";
      if (FlexiConfig::$sFramework == "modx2") {
        //FlexiController::appendOutput("is render layout: " . $this->sLayoutTemplate);
        FlexiController::appendOutput($this->oView->render($this->sLayoutTemplate, null, $this->sModulePath));
      } else {
        //echo "ouput: " . $this->sLayoutTemplate . " from: " . $this->sModulePath;
        echo $this->oView->render($this->sLayoutTemplate, null, $this->sModulePath);
      }
		}
		else
		{
      //echo __METHOD__.": no layout, viewname: " . $this->sRenderViewName;
      if (FlexiConfig::$sFramework == "modx2") {
        //FlexiController::appendOutput("is render non-layout");
        FlexiController::appendOutput($this->oView->getVar($this->sRenderViewName));
      } else {
        echo $this->oView->getVar($this->sRenderViewName);
      }
		}
		
		$this->afterRender();
		$this->unsetSession("#messages");
	}
	
	/**
	 * call before rendering layout
	 * @return boolean: true to continue, false to stop
	 */
	public function beforeRender()
	{
		return true;
	}
	
	/**
	 * call before rendering layout
	 * @param String method called, without "Action" tail
	 */
	public function afterRender() { }
	
	/**
   * Render array of forms via FlexiView
   *  template is based on #type,
   *  or defined as #theme, example: #theme=>xx , resolve to xx.tpl.php
   * @param array $aValues fields
   * @param String $asName field name
   * @param String $asPath path to view, default to module full path
   * @return String HTML
   */
	public function renderMarkup($aValues, $asName="", $asPath=null)
	{
    //die("pass: " . $asPath . ", mpath: " . $this->sModulePath);
		$sPath = is_null($asPath) ? $this->sModulePath : $asPath;
		
		return $this->oView->renderMarkup($aValues, $asName, $sPath);
	}
	/**
	 * render a view from current modules/your_modulename/views/*.tpl.php
   *  or from assets/flexitemplate/your_templatename/*.tpl.php
	 * @param string view name (optional)
	 * @param array variables (optional)
	 * @param String path (optional)
	 */
	public function renderView($asView="", $aVars=null, $asPath=null)
	{
		$sView = empty($asView) ? $this->sViewName : $asView;
		//echo "view template: " . $sView;
		if (empty($sView)) { return ""; }
		$sPath = is_null($asPath) ? $this->sModulePath : $asPath;
		return $this->oView->render($sView, $aVars, $sPath);
	}

  /**
	 * render a view
	 * @param string view name (optional)
	 * @param array variables (optional)
	 * @param String path (optional)
	 */
	public function renderViewPartial($asView="", $aVars=null, $asPath=null)
	{
		$sView = empty($asView) ? $this->sViewName : $asView;

		if (empty($sView)) { return ""; }
		$sPath = is_null($asPath) ? $this->sModulePath : $asPath;

		return $this->oView->renderPartial($sView, $aVars, $sPath);
	}

  public function getAllRequest() {
    return FlexiController::getInstance()->getAllRequest();
  }

	public function getRequest($sName, $mDefault=null)
	{
		return FlexiController::getInstance()->getRequest($sName, $mDefault);
	}
	
	public function getPost($sName, $mDefault=null)
	{
		return FlexiController::getInstance()->getPost($sName, $mDefault);
	}
	
	public function getQuery($sName, $mDefault=null)
	{
		return FlexiController::getInstance()->getQuery($sName, $mDefault);
	}
	
	public function getFile($sName, $mDefault=null)
	{
		return FlexiController::getInstance()->getFile($sName, $mDefault);
	}
	
	public function getCookie($sName, $mDefault=null)
	{
		return FlexiController::getInstance()->getCookie($sName, $mDefault);
	}
	
	public function getSession($sName, $mDefault=null)
	{
		return FlexiController::getInstance()->getSession($sName, $mDefault);
	}
	
	public function setSession($sName, $mValue)
	{
		return FlexiController::getInstance()->setSession($sName, $mValue);
	}
	
	public function setRequest($sName, $mValue, $bPermanent = false)
	{
		return FlexiController::getInstance()->setRequest($sName, $mValue, $bPermanent);
	}
	
	public function addFormMessage($sName, $mValue, $sType = "info")
	{
		FlexiConfig::$aFormMessage[$sName] = array("msg" => $mValue, "type" => $sType);
	}
	/**
	 * Add msg to display
	 * @param string message
	 * @param string type: "info", "warn", "error"
	 */
	public function addMessage($asMsg, $asType="info")
	{
		FlexiPlatformHandler::getPlatformHandler()->addMessage($asMsg, $asType);
	}
	/**
	 * Get login handler for logged in user / guest user
	 * @return FlexiLoginBaseHandler
	 */
	public function getLoginHandler()
	{
		return FlexiConfig::getLoginHandler();
	}
	
	/**
	 * load model
	 * @param string name
	 * @param path (optional)
	 */
	public function loadModel($asName, $asPath = null)
	{
    $sPath = empty($asPath) ? $this->sModulePath : $asPath;
    return FlexiModelUtil::loadModel($asName, $sPath);
	}

  public function returnJSON($bStatus, $mReturn = null, $sMessage = "", $mParam = null) {
    $aResult = $this->returnResult($bStatus, $mReturn, $sMessage, $mParam);
    return json_encode($aResult);
  }

  public function returnJSONInTextArea($bStatus, $mReturn = null, $sMessage = "", $mParam = null) {
    $aResult = $this->returnResult($bStatus, $mReturn, $sMessage, $mParam);
    $sResult = json_encode($aResult);
    return "<textarea>\n" . $sResult . "\n</textarea>";
  }

  public function returnResult($bStatus, $mReturn = null, $sMessage = "", $mParam = null) {
    return array(
      "status" => $bStatus ? 1 : 0,
      "return" => $mReturn,
      "msg" => $sMessage,
      "params" => $mParam
    );
  }

	/**
	 * Get Doctrine query object
	 * @param string name
	 * @param string path (optional)
	 * @return Doctrine_Record
	 */
	public function getDBQuery($asName, $asPath = null)
	{
    $sPath = empty($asPath) ? $this->sModulePath : $asPath;
    return FlexiModelUtil::getDBQuery($asName, $sPath);
	}
	
	public function getModelInstance($asName, $asPath=null)
	{
		$sPath = empty($asPath) ? $this->sModulePath : $asPath;
		return FlexiModelUtil::getModelInstance($asName, $sPath);
	}
	
	public function getTableInstance($asName, $asPath=null)
	{
		$sPath = empty($asPath) ? $this->sModulePath : $asPath;
		return FlexiModelUtil::getTableInstance($asName, $sPath);
	}
	
	public function redirectControl($aaParam=array(), $asMethod = null, $asModule = null, $bAjax = false)
	{
		$aParam = is_null($aaParam) ? array() : $aaParam;
		$sModule 	= is_null($asModule) ? $this->sModule : $asModule;
		$sMethod 	=	is_null($asMethod) ? $this->sMethod : $asMethod;
		$sURL = "";
		
		
		if (! empty($sModule) && !empty($sMethod))
		{
			$sURL = "?" . FlexiConfig::$aModuleURL["module"] . "=" . $sModule . "&" . FlexiConfig::$aModuleURL["method"] . "=" . $sMethod;
		}
		
		$aParamQuery = array();
		foreach($aParam as $sKey => $sValue)
		{
			$aParamQuery[] = $sKey . "=" . $sValue;
		}
		
		$sURL .= empty($sURL) ? "?" : "&";
		$sURL .= implode("&", $aParamQuery);
		
		$sURL .= empty($sURL) ? "?" : "&";
		//$sURL .= $asURL;
		//echo "url: " . $sURL;
		//die();
		return $this->redirectURL(flexiURL($sURL, $bAjax));
	}
	
	public function redirectURL($sURL)
	{
    $this->setLayout(""); //no rendering layout on redirect
    return FlexiController::redirectURL($sURL);
	}

  public function getService($sName) {
    return FlexiService::getService($sName, $sName . "Manager");
  }
	
	public function onInit() {}
	public function onEvent($sEventType, $oParam) {}


  public function checkSetup() {
    //check if
    if (!is_dir($this->sModelPath)) {
      if (!mkdir($this->sModelPath, 0777, true)) {
        throw new FlexiException("unable to create model: " . $this->sModelPath, ERROR_CREATEERROR);
      }
    }
  }
	/**
	 * Override to control permission
	 * @param string method
	 * @return boolean: true: ok, false: no
	 */
	public function permission($sMethod) { return true; }
	
  /**
   * NOT USED;
   * Override to do custom redirect on permission error
   */
  public function onPermissionError() {
    //
  }

	public function unsetSession($sName)
	{
		FlexiController::getInstance()->unsetSession($sName);
	}
	
	abstract public function methodDefault();
}
