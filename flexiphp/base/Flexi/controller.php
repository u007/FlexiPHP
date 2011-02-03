<?php

class FlexiController
{
	//1: info, err only, 2: info, err, warn, 3: info, err, debug
	public static $iLogLevel = 1;
	
	protected static $oInstance = null;
	protected $env = array();
	
	protected $aControllers = array();
	protected $aControllerPath = array();
  protected $bIsService = false;
  protected static $oActiveControl = null;

  public static $sOutput = null;
	
	private function __construct()
	{
		//request: custom setting
		$this->env = array( 
			"request" => array(),
			"post" => array(),
			"get" => array(),
			"cookie" => array(),
			"file" => array(),
			"session" => array(),
			"framework" => "",
			"basedir" => "."
			);

    $this->setEnvironment(FlexiConfig::$aPost, FlexiConfig::$aGet, FlexiConfig::$aCookie,
      FlexiConfig::$aFiles, FlexiConfig::$sBaseDir, FlexiConfig::$sFramework, FlexiConfig::$aSession);
	}

  public static function getCurrentController() {
    return self::$oActiveControl;
  }

  public static function appendOutput($sContent) {
    if(is_null(self::$sOutput)) {
      self::$sOutput = $sContent;
    } else {
      self::$sOutput .= $sContent;
    }
  }

  public static function flushOutput() {
    self::$sOutput = null;
  }

	public function setEnvironment($post, $get, $cookie, $file, $sBase, $sFramework, $session)
	{
		if ($post != null) $this->env["post"] = &$post;
		if ($get != null) $this->env["get"] = &$get;
		if ($cookie != null) $this->env["cookie"] = &$cookie;
		if ($file != null) $this->env["file"] = &$file;
		$this->env["basedir"] = $sBase;
		$this->env["framework"] = $sFramework;
		$this->env["session"] = & $session;
		
		FlexiConfig::$aMessage = $this->getSession("#messages", array());
	}

  public function setHeader($sName, $sValue) {
    header($sName . ": " . $sValue);
  }

	public function setSession($sName, $mValue)
	{
		global $_SESSION;
		$_SESSION[$sName] = $mValue;
		$this->env["session"][$sName] = $mValue;
	}
	
	public function unsetSession($sName)
	{
		unset($_SESSION[$sName]);
		unset($this->env["session"][$sName]);
	}
	
	//TODO permanent
	public function setRequest($sName, $mValue, $bPermanent=false)
	{
		$this->env["request"][$sName] = $mValue;
	}
	
	public function unsetRequest($sName)
	{
		unset($this->env["request"][$sName]);
	}
	
	public function getAllRequest()
	{
		return array_merge($this->env["get"], $this->env["post"], $this->env["request"]);
	}

  public function getRawPostContent() {
    global $HTTP_RAW_POST_DATA;
    return $HTTP_RAW_POST_DATA;
  }

	public function getRequest($sName, $mDefault=null)
	{
		if (array_key_exists($sName, $this->env["request"])) { return $this->env["request"][$sName]; }
		if (array_key_exists($sName, $this->env["post"])) { return $this->env["post"][$sName]; }
		if (array_key_exists($sName, $this->env["get"])) 	{ return $this->env["get"][$sName]; }
		return $mDefault;
	}
	
	public function getPost($sName, $mDefault=null)
	{
		if (array_key_exists($sName, $this->env["post"])) { return $this->env["post"][$sName]; }
		return $mDefault;
	}
	
	public function getQuery($sName, $mDefault=null)
	{
		if (array_key_exists($sName, $this->env["get"])) { return $this->env["get"][$sName]; }
		return $mDefault;
	}
	
	public function getFile($sName, $mDefault=null)
	{
		if (array_key_exists($sName, $this->env["file"])) { return $this->env["file"][$sName]; }
		return $mDefault;
	}
	
	public function getCookie($sName, $mDefault=null)
	{
		if (array_key_exists($sName, $this->env["cookie"])) { return $this->env["cookie"][$sName]; }
		return $mDefault;
	}
	
	public function getSession($sName, $mDefault=null)
	{
    //var_dump($this->env);
		if (array_key_exists($sName, $this->env["session"]))
		{
			return $this->env["session"][$sName];
		}
		
		return $mDefault;
	}

  /**
   * Only create class and run method with view rendering,
   *  no rendering of layout
   * @param String $sModule
   * @param String $sMethod
   * @param FlexiView $oView
   * @return array("control"=> object,"return" => boolean);
   */
  public function _run($asModule, $asMethod, $oView) {
    static $bTriggered = false;
    $sModule = empty($asModule) ? "default" : $asModule;
		$sMethod = empty($asMethod) ? "default" : $asMethod;
		//clean method
		$sMethodName = ucwords($sMethod);
		$sMethodName = str_replace(" ", "", $sMethodName);

    FlexiLogger::debug(__METHOD__, "module: " . $sModule . ", method: " . $sMethod);

    $oClass = & self::getControllerInstance($sModule, $oView, $sMethodName, null);
    self::$oActiveControl = $oClass;

    //trigger event for 1st time only
    if (!$bTriggered) {
      //FlexiLogger::info(__METHOD__, "DOC: " . $modx->documentIdentifier);
      $aEventParams = array();
      if (FlexiConfig::$sFramework == "modx") {
        global $modx;
        $aEventParams = array("docid" => $modx->documentIdentifier);
      } else if(FlexiConfig::$sFramework == "modx2") {
        global $modx;
        $aEventParams = array("docid" => $modx->resourceIdentifier, "method" => $modx->resourceMethod);
      }

      FlexiEvent::triggerEvent("onLoadDocument", $aEventParams);
      $bTriggered = true;
    }

    if (is_null($oClass)) {
      FlexiLogger::error(__METHOD__, "module: " . $sModule . " does not exists");
      die("module: " . $sModule . " does not exists");
      return array("return" => false, "control" => & $oClass);
    }
    FlexiLogger::debug(__METHOD__, "calling _runControl");
    //render view as "body" var
    //echo "running control to body";
		$bResult = $oClass->_runControl($sMethod, null, "body");
    //manually render layout
    FlexiLogger::debug(__METHOD__, "called _runControl");
    return array("return" => $bResult, "control" => & $oClass);
  }

  /**
   * use for remote rpc call
   * @param String $asModule
   * @param String $asMethod
   * @return array("status":boolean, "return":mixed, "control":object);
   */
  public function runService($asModule, $asMethod, $aData = array()) {
    $this->bIsService = true;
    $sModule = empty($asModule) ? "default" : $asModule;
		$sMethod = empty($asMethod) ? "default" : $asMethod;
		//clean method
		$sMethodName = ucwords($sMethod);
		$sMethodName = str_replace(" ", "", $sMethodName);

    FlexiLogger::debug(__METHOD__, "module: " . $sModule . ", method: " . $sMethod);

    $oClass = & self::getControllerInstance($sModule, null, $sMethodName, null);
    self::$oActiveControl = $oClass;

    if (is_null($oClass)) {
      FlexiLogger::error(__METHOD__, "module: " . $sModule . " does not exists");
      return array("status" => false, "return" => false, "control" => & $oClass);
    }
    FlexiLogger::debug(__METHOD__, "calling runService");
    //render view as "body" var
		$mResult = $oClass->runService($sMethod, null, $aData);
    //manually render layout
    FlexiLogger::debug(__METHOD__, "called runService");
    return array("status" => true, "return" => $mResult, "control" => & $oClass);
  }

  public function initiateModule($asModule) {
    
  }

	public function run($asModule, $asMethod, $abRenderLayout = true)
	{
		FlexiLogger::debug(__METHOD__, "module: " . $asModule . ", method: " . $asMethod . ", renderlayout:" . ($abRenderLayout ? "yes" : "no"));
		$oView = new FlexiView();
		$aResult = $this->_run($asModule, $asMethod, $oView);
    //FlexiLogger::debug(__METHOD__, "after _run");
    if ($abRenderLayout) { 
      $aResult["control"]->renderLayout(); 
    } else {
      FlexiController::appendOutput($oView->getVar("body"));
    }
    return $aResult["return"];
	}
  
  //Replaced by FlexiEvent::trigger
//  public function triggerEvent($sName) {
//    $aModules = $this->getAllModulesName();
//
//    foreach($aModules as $aInfo) {
//      $sClass = $aInfo["name"] . "Event";
//      if (flexiClassExists($sClass)) {
//        flexiInclude($sClass);
//        //TODO, WHY?
//        //$sClass::trigger($sName);
//      }
//    }
//  }
  public function getAllModulesName($bCached = true) {
    static $aList = array();

    if (count($aList) > 0 && $bCached) {
      return $aList;
    }
    
    $sClassName 	= $sControl . "Controller";
		$sBaseDir 		= $this->env["basedir"];

    $aList = array_merge($aList, flexiDirListGetDirInfo(FlexiConfig::$sModulePath));
    $aList = array_merge($aList, flexiDirListGetDirInfo($sBaseDir . "/modules"));
    $aList = array_merge($aList, flexiDirListGetDirInfo($sBaseDir . "/base"));

    return $aList;
  }

	public static function getControllerInstance($asControl=null, $oView=null, $sMethodName=null, $sModulePath=null)
	{
		$oControl = self::getInstance();
		
		$sControl = empty($asControl) ? $oControl->getRequest("mod") : $asControl;
		$sControl = empty($sControl) ? "default" : $sControl;
		
		if (! isset($oControl->aControllers[$sControl]))
		{
			$oControl->aControllers[$sControl] = $oControl->getNewController($sControl, $oView, $sMethodName, $sModulePath);
		}
		
		return $oControl->aControllers[$sControl];
	}
	
	public function getNewController($sControl, $oView, $sMethodName, $sModulePath)
	{
		$sClassName 	= $sControl . "Controller";
		$sBaseDir 		= $this->env["basedir"];
		$sModulePath	= "";
    //echo "trying: " . $sControl;
    FlexiLogger::debug(__METHOD__, "Env.BaseDir: " . $sBaseDir . ", cwd: " . getcwd() .
            ", modpath: " . FlexiConfig::$sModulePath);
    
    FlexiLogger::debug(__METHOD__, "Checking sModulePath: " . FlexiConfig::$sModulePath . "/" . $sControl);
    FlexiLogger::debug(__METHOD__, "Checking sModulePath: " . $sBaseDir . "/modules/" . $sControl);
    FlexiLogger::debug(__METHOD__, "Checking sModulePath: " . $sBaseDir . "/base/" . $sControl);

		if (is_file(FlexiConfig::$sModulePath . "/" . $sControl . "/controller.php"))
		{
			$sModulePath = FlexiConfig::$sModulePath . "/" . $sControl;
			require_once($sModulePath . "/controller.php");
		}
    else if (is_file(FlexiConfig::$sModulePath . "/" . strtolower($sControl) . "/controller.php"))
		{
			$sModulePath = FlexiConfig::$sModulePath . "/" . strtolower($sControl);
			require_once($sModulePath . "/controller.php");
		}
		else if (is_file($sBaseDir . "/modules/" . $sControl . "/controller.php"))
		{
			$sModulePath = $sBaseDir . "/modules/" . $sControl;
      FlexiLogger::debug(__METHOD__, "Checking sModulePath: " . $sModulePath);
			require_once($sModulePath . "/controller.php");
		}
    else if (is_file($sBaseDir . "/modules/" . strtolower($sControl) . "/controller.php"))
		{
			$sModulePath = $sBaseDir . "/modules/" . strtolower($sControl);
      FlexiLogger::debug(__METHOD__, "Checking sModulePath: " . $sModulePath);
			require_once($sModulePath . "/controller.php");
		}
		else if(is_file($sBaseDir . "/base/" . $sControl . "/controller.php"))
		{
			$sModulePath = $sBaseDir . "/base/" . $sControl;
      FlexiLogger::debug(__METHOD__, "Checking sModulePath: " . $sModulePath);
			require_once($sModulePath . "/controller.php");
		}
    else if(is_file($sBaseDir . "/base/" . strtolower($sControl) . "/controller.php"))
		{
			$sModulePath = $sBaseDir . "/base/" . strtolower($sControl);
      FlexiLogger::debug(__METHOD__, "Checking sModulePath: " . $sModulePath);
			require_once($sModulePath . "/controller.php");
		}

    
		if (! class_exists($sClassName))
		{
      //echo "not found: " . $sClassName;
			FlexiLogger::error(__METHOD__, "module: " . $sControl . " is missing.");
			return;
		}
		
		FlexiLogger::debug(__METHOD__, "found module: " . $sControl . " at " . $sModulePath);
    //echo "found module: " . $sControl . " at " . $sModulePath;
    //echo "setting controllerpath: " . $sControl . "=>" . $sModulePath;
		$this->aControllerPath[strtolower($sControl)] = $sModulePath;

    //echo "initialising class: " . $sClassName;
		$oClass = new $sClassName($oView, $sMethodName, $sModulePath);
    //echo "xxxx";
		if (! is_subclass_of($oClass, "FlexiBaseController"))
		{
      //echo "no class of: " . $sClassName;
			throw new FlexiException("Class: " . $sClassName . " is not of type FlexiBaseController", 500);
		}
		//echo "ok: " .$sClassName;
		return $oClass;
	}
	
	public static function getControllerPath($asControl)
	{
    $sControl = strtolower($asControl);
		$oControl = self::getInstance();
    if (!isset($oControl->aControllerPath[$sControl])) {
      throw new Exception("Controller path not found: " . $sControl);
    }
		return $oControl->aControllerPath[$sControl];
	}

  public static function redirectURL($sURL) {
		header("location: " . $sURL);
    die();
		return false;
  }

	public function __destruct()
	{
		//if view is rendered, reset messages
		if (FlexiConfig::$bRenderedNotice && ! empty(FlexiConfig::$aMessage))
		{
			FlexiConfig::$aMessage = array();
		}
		$this->setSession("#messages", FlexiConfig::$aMessage);
	}
	
	public static function getInstance()
	{
		if (self::$oInstance == null)
		{
			self::$oInstance = new FlexiController();
		}
		return self::$oInstance;
	}
	
}
