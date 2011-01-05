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
			$vars = FlexiArrayUtil::cloneArray($this->aVariables);
		}
		
		$vars["#viewpath"] 	= $asPath;
		$vars["#css"] 			= $this->aHeader["css"];
		$vars["#js"]				= $this->aHeader["js"];
    $vars["#template"]  = $this->sTemplate;

    $sFViewFile = "";
    //try framework.viewname, example: modx2.index
    if (! empty(FlexiConfig::$sFramework)) {
      if (substr($sView, 0, strlen(FlexiConfig::$sFramework)+1) != FlexiConfig::$sFramework.".") {
        //if view name does not start with framework
        $sFViewFile = $this->getViewFile(FlexiConfig::$sFramework.".".$sView, $asPath);
        if (! is_null($sFViewFile))
        {
          //FlexiLogger::info(__METHOD__, "Found: " . FlexiConfig::$sFramework.".".$sView);
          $sViewFile = $sFViewFile;
        }
      }
    }
    //if framework template not found...
    if (empty($sFViewFile)) {
      $sViewFile = $this->getViewFile($sView, $asPath);
      if (is_null($sViewFile))
      {
        FlexiLogger::error(__method__, "View: " . $sView . " is missing...");
        return null;
      }
    }
		
		ob_start();
		require($sViewFile);
		$sResult = ob_get_contents();
		@ob_end_clean();
		
		return $sResult;
	}
	
	public function getViewFile($sView, $asPath=null)
	{
		$sBaseDir = FlexiConfig::$sBaseDir;
		$sViewFile = strtolower($sView);
    $sRootDir = FlexiConfig::$sRootDir;
    //throw new Exception("aspath: " . $asPath);
		if (is_null($asPath))
		{
			$sPath = $this->getVar("#modulepath");
		}
		else
		{
			$sPath = $asPath;
		}
    //FlexiLogger::info(__METHOD__ . ", ");
//    if ($this->getVar("#module")=="FlexiRepoList") {
//      FlexiLogger::info(__METHOD__, "view: " . $sView . ", path: " . $sPath); // . ", #modulepath: " . $this->getVar("#modulepath"));
//    }
		//var_dump($sPath);
    //from direct[path]
		$sViewPath = $sPath . "/views/" . $sViewFile . ".tpl.php";
		if (is_file($sViewPath))
		{
      //echo "file ok";
			return $sViewPath;
		}
    $sViewPath = $sPath . "/views/" . strtolower($sViewFile) . ".tpl.php";
		if (is_file($sViewPath))
		{
			return $sViewPath;
		}

    //from $sRootDir/[path]
    $sViewPath = $sRootDir . "/" . $sPath . "/views/" . $sViewFile . ".tpl.php";
    //echo $sViewPath;
		if (is_file($sViewPath))
		{
			return $sViewPath;
		}
    $sViewPath = $sRootDir . "/" . $sPath . "/views/" . strtolower($sViewFile) . ".tpl.php";
		if (is_file($sViewPath))
		{
			return $sViewPath;
		}

    //from ../$sRootDir/[path]
    if ($sPath != $this->getVar("#modulepath")) {
      $sViewPath = $sRootDir . "/../" . $sPath . "/views/" . $sViewFile . ".tpl.php";
      //echo $sViewPath;
      if (is_file($sViewPath))
      {
        return $sViewPath;
      }
      $sViewPath = $sRootDir . "/../" . $sPath . "/views/" . strtolower($sViewFile) . ".tpl.php";
      if (is_file($sViewPath))
      {
        return $sViewPath;
      }
    }

    //var_dump($this->sTemplate);
		//$sViewPath = $sBaseDir . "/assets/templates/" . $this->sTemplate . "/" . $sViewFile . ".tpl.php";
    $sViewPath = FlexiConfig::$sTemplatePath . "/" . $this->sTemplate . "/" . $sViewFile . ".tpl.php";
    //if (FlexiConfig::$sFramework == "modx2") echo "trying: " . $sViewFile . " @ " . $sViewPath . "\r\n<br/>";
    //echo $sViewPath;
    //FlexiLogger::debug(__METHOD__, "Trying: " . $sViewPath);
		if (is_file($sViewPath))
		{
			return $sViewPath;
		}

    $sViewPath = FlexiConfig::$sTemplatePath . "/" . $this->sTemplate . "/" . strtolower($sViewFile) . ".tpl.php";
		if (is_file($sViewPath))
		{
			return $sViewPath;
		}

    //resolve to root default template
    $sViewPath = $sBaseDir . "/assets/templates/default/" . $sViewFile . ".tpl.php";
    //FlexiLogger::debug(__METHOD__, "Trying: " . $sViewPath);
		if (is_file($sViewPath))
		{
			return $sViewPath;
		}

    $sViewPath = $sBaseDir . "/assets/templates/default/" . strtolower($sViewFile) . ".tpl.php";
    //FlexiLogger::debug(__METHOD__, "Trying: " . $sViewPath);
		if (is_file($sViewPath))
		{
			return $sViewPath;
		}
		
		return null;
	}
	
	
}
