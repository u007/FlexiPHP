<?php

class FlexiBaseView
{
	protected $aHeader = array("css" => array(), "js" => array());
	public $sTemplate = "";
	public $aVariables = array();
	
	public function __construct()
	{
		$this->sTemplate = FlexiConfig::$sTemplateDir;
	}
	
	public function clearVar()
	{
		$this->aVariables = array();
	}
	
	public function removeVar($sName)
	{
		unset($this->aVariables[$sName]);
	}
	
	public function getVar($sName, $mDefault = "")
	{
		if(isset($this->aVariables[$sName]))
		{
			return $this->aVariables[$sName];
		}
		
		return $mDefault;
	}

  public function getVars() {
    return $this->aVariables;
  }
	
	public function addVar($sName, $mValue)
	{
		$this->aVariables[$sName] = $mValue;
	}
	
	public function setVar($aVar)
	{
		$this->aVariables = $aVar;
	}
	
  public function setTemplate($asTemplate) {
    return $this->setLayout($asTemplate);
  }

	public function setLayout($asTemplate)
	{
		$this->sTemplate = $asTemplate;
	}

  public function renderPartialJSString($sView, $aVariables=array(), $sPath = null, $dlimit = "\"") {
    $sOutput = $this->renderPartial($sView, $aVariables, $asPath);
    $sOutput = str_replace("\n", "\\\n", $sOutput);
    $sOutput = str_replace($dlimit, "\\" . $dlimit, $sOutput);
    return $sOutput;
  }

	public function renderPartial($sView, $aVariables=array(), $sPath = null)
	{
		return $this->render($sView, $aVariables, $sPath);
	}

  public function renderJSString($sView, $aVariables=null, $asPath=null, $dlimit = "\"") {
    $sOutput = $this->render($sView, $aVariables, $asPath);
    $sOutput = str_replace("\n", "\\\n", $sOutput);
    $sOutput = str_replace($dlimit, "\\" . $dlimit, $sOutput);
    
    return $sOutput;
  }

	public function render($sView, $aVariables=null, $asPath=null)
	{
		if (! is_null($aVariables))
		{
			$vars = FlexiArrayUtil::cloneArray($aVariables);
		}
		else
		{
      //var_dump($this->aVariables);
			$vars = FlexiArrayUtil::cloneArray($this->aVariables);
		}

		$vars["#viewpath"] 	= $asPath;
		$vars["#css"] 			= $this->aHeader["css"];
		$vars["#js"]				= $this->aHeader["js"];
    $vars["#template"]  = $this->sTemplate;
    
    $sViewFile = ""; //final file

    $sViewFile = $this->getViewFile($sView, $asPath);
    
    if (empty($sViewFile)) {
      FlexiLogger::error(__METHOD__, "View: " . $sView . " is missing...");
      return null;
    }
    
    //echo "rendering view: " . $sViewFile;
		ob_start();
    //FlexiLogger::info(__METHOD__, "found view: " . $sViewFile);
		require($sViewFile);
		$sResult = ob_get_contents();
		@ob_end_clean();
		
		return $sResult;
	}
	
	public function getViewFile($sView, $asPath=null)
	{
    $bDebug = false;
		$sViewFile = strtolower($sView);

    $sModulePath = $this->getVar("#modulepath");
    $sModule     = strtolower($this->getVar("#module"));

    $sTempPath  = is_null($asPath)? $sModulePath: $asPath;
    $sPath = realpath($sTempPath);
    if ($sPath === false) {
      $sPath = realpath(FlexiConfig::$sRootDir . "/" . $sTempPath);
    }
    if ($sPath === false) throw new Exception("Path not found: " . $sTempPath);
    /*
     * 1st, find for framework.view, or view file from path or modulepath
     * 2nd, find all 3 path with module/framework.view and module/view
     * 3rd, find all 3 path for framework.view and view
     * The 3 paths:
     *  then try template folder
     *  then try template/default
     *  then try flexiphp/template/default
     */
    $aPath = array();
    $aPath = array_merge($aPath, $this->getViewPaths($sPath . "/views", $sViewFile));
    //module/
    $aPath = array_merge($aPath, $this->getViewPaths(FlexiConfig::$sTemplatePath . "/" . $this->sTemplate . "/" . $sModule, $sViewFile));
    $aPath = array_merge($aPath, $this->getViewPaths(FlexiConfig::$sTemplatePath . "/default/" . $sModule, $sViewFile));
    $aPath = array_merge($aPath, $this->getViewPaths(FlexiConfig::$sBaseDir . "/assets/templates/default/" . $sModule, $sViewFile));
    //view directly
    $aPath = array_merge($aPath, $this->getViewPaths(FlexiConfig::$sTemplatePath . "/" . $this->sTemplate, $sViewFile));
    $aPath = array_merge($aPath, $this->getViewPaths(FlexiConfig::$sTemplatePath . "/default", $sViewFile));
    $aPath = array_merge($aPath, $this->getViewPaths(FlexiConfig::$sBaseDir . "/assets/templates/default", $sViewFile));
    
    //var_dump($aPath);
    foreach($aPath as $sViewPath) {
      if (is_file($sViewPath . ".tpl.php"))
      {
        return $sViewPath . ".tpl.php";
      }
    }
		return null;
	}

  public function getViewPaths($sPath, $sViewFile) {
    $aPath = array();
    $sLowerView = strtolower($sViewFile);
    
    if (!empty(FlexiConfig::$sFramework)) {
      $aPath[] = $sPath . "/" . FlexiConfig::$sFramework . ".". $sViewFile;
      if(strcmp($sViewFile, $sLowerView)!=0)
        $aPath[] = $sPath . "/" . FlexiConfig::$sFramework . ".". $sLowerView;
    }
    
    $aPath[] = $sPath . "/" . $sViewFile;
    if(strcmp($sViewFile, $sLowerView)!=0)
      $aPath[] = $sPath . "/" . $sLowerView;

    return $aPath;
  }
	
	
}
