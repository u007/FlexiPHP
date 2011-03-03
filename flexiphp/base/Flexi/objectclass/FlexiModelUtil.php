<?php

class FlexiModelUtil
{
	protected static $oInstance = null;
	protected $env = array();
	private $aSetting = array();
	private $sDSN = "";
	protected $oDb = null;

  public static $aTableId = array();
	
	private function __construct()
	{
		$this->setDBSetting("", "", "", "", "", "");
	}
	
	public function setDBSetting($sType, $sHost, $iPort, $sUser, $sPass, $sDBName)
	{
		$this->aSetting = array("host" => $sHost, "port" => $iPort, "user" => $sUser, "pass" => $sPass, "type" => $sType, "dbname" => $sDBName);
		$aSetting = $this->aSetting;
		if ($aSetting["type"] != "sqlite")
		{
			$this->sDSN = $aSetting["type"] . "://" . urlencode($aSetting["user"]) . ":" . 
				urlencode($aSetting["pass"]) . "@" . urlencode($aSetting["host"]) . ":" . 
				urlencode($aSetting["port"]) . "/" . urlencode($aSetting["dbname"]) . "";
		}
		else if($aSetting["type"] == "sqlite")
		{
			$this->sDSN = $aSetting["type"] . "://" . urlencode($aSetting["dbname"]) . "?mode=0666";
		}
	}

  public static function importRedBean($aData, &$oModel, $aFields) {
    //FlexiLogger::debug(__METHOD__, "importing");
    $aImport = array();
    $sFields = "";
    foreach($aFields as $sKey => $aValue) {
      if (! empty($aValue["#dbfield"]) && array_key_exists($sKey, $aData)) {
        //FlexiLogger::info(__METHOD__, "$sKey: " . $aValue["#type"] . ", val: " . $aData[$sKey]);
        switch($aValue["#type"]) {
          case "date":
          case "date.raw":
            $sFormat = isset($aValue["#format"]) ? $aValue["#format"] : FlexiConfig::$sInputDateFormat;
            $sFuncName = str_replace("-", "", strtoupper($sFormat));
            $sFuncName = str_replace("/", "", $sFuncName);
            $sFuncName = str_replace("/", "", $sFuncName);
            $sFuncName = "getISODateFrom" . $sFuncName;
            //FlexiLogger::debug(__METHOD__, "Func: " . $sFuncName);
            $aImport[$sKey] = FlexiStringUtil::$sFuncName($aData[$sKey]);
            break;
          default:
            $aImport[$sKey] = $aData[$sKey];
        } //switch data type
      }//if dbfield defined
    }
    $oModel->import($aImport, array_keys($aImport));
  }

  public static function validateForm($aForm, $oModel) {
    //FlexiLogger::info(__METHOD__, print_r($oModel->toArray(), true));
    foreach($aForm as $sField => $aValue) {
      $sDBField = isset($aValue["#dbfield"]) ? $aValue["#dbfield"] : "";
      //FlexiLogger::info(__METHOD__, $sDBField . "=" . $oModel->$sDBField);
      if (!empty($sDBField) && isset($aValue["#required"]) && $aValue["#required"]) {
        if (empty($oModel->$sDBField)) {
          FlexiLogger::error(__METHOD__, $sDBField . "=" . $oModel->$sDBField . " is empty?");
          throw new Exception($aValue["#title"] . " cannot be empty");
        }
      }
      if (!empty($sDBField) && !empty($oModel->$sDBField) && $aValue["#type"]=="email") {
        if (! FlexiStringUtil::isValidEmail($oModel->$sDBField)) {
          throw new Exception("Invalid email: " . $oModel->$oModel);
        }
      }
    }//foreach field
  }

  public function getRedBeanLastId() {
    $redbean = $this->getRedBeanDB();
    return $redbean->GetInsertID();
  }

  public function setRedBeanEvent($oClass) {
    $redbean = $this->getRedBeanDB();
    $redbean->addEventListener("update", $oClass);
    $redbean->addEventListener("delete", $oClass);
    $redbean->addEventListener("open", $oClass);
  }

  public function storeRedBean(RedBean_OODBBean $oModel) {
    $redbean = $this->getRedBeanDB();
    return $redbean->store($oModel);
  }

  public function insertOrUpdateRedBean(RedBean_OODBBean $oModel) {
    $writer = $this->getRedBeanWriter();
    $type=$oModel->getMeta("type");
		$idfield = $writer->getIDField($type);
    $idvalue = $oModel->$idfield;
    
    FlexiLogger::info(__METHOD__, "trying: " . $type . ": " . $oModel->$idfield);
    if (empty($idvalue)) {
      //if empty, just insert as usual
      return $this->insertRedBean($oModel);
    } else {
      //checking if update
      $bean = $this->getRedBeanModel($type, $idvalue);

      if ($bean != null && $bean !== false && $bean->$idfield == $idvalue) {
        // is existing record
        FlexiLogger::debug(__METHOD__, "Found record for " . $type . ":" . $idvalue);
        return $this->storeRedBean($oModel);
      } else {
        //cannot find record, time to insert, but with specified id
        return $this->insertRedBean($oModel);
      }
    }
    
  }

  public function insertRedBean(RedBean_OODBBean $oModel) {
    $redbean = $this->getRedBeanDB();
    return $redbean->insert($oModel);
  }

  public function getRedBeanFetchOne($sql, $aValues = array()) {
    $query = $this->getRedBeanQuery();
    
    return $query->getRow($sql, $aValues);
  }

  public function getRedBeanFetchAll($sql, $aValues = array()) {
    $query = $this->getRedBeanQuery();
    return $query->get($sql, $aValues);
  }

  public function getRedBeanExecute($sql, $aValues = array()) {
    $query = $this->getRedBeanQuery();
    return $query->exec($sql, $aValues);
  }

  
  public function getRedBeanModel($sName, $mCond = null) {
    $redbean = $this->getRedBeanDB();
    $adapter = $this->getRedBeanQuery();
    $dbtype = $adapter->getDatabase()->getDatabaseType();
    
    if (is_null($mCond)) {
      return $redbean->dispense($sName);
    } else {
      if (is_array($mCond)) {
        $aCond = array();
        foreach($mCond as $sKey => $sValue) {
          switch($dbtype) {
            case "mysql":
              $aCond[] = "`" . $sKey . "`=:$sKey";
              $aCondValue[":" . $sKey] = $sValue;
              break;
          }
        }
        $aRow = $this->getRedBeanFetchOne(
          "select * from " . $sName . " where " . implode(" and ", $aCond)
          , $aCondValue);
        //FlexiLogger::info(__METHOD__, "sql: " . "select * from " . $sName . " where " . implode(" and ", $aCond) . ", cond: " . print_r($aCondValue,true));
        if (is_null($aRow)) { return null; }
        //FlexiLogger::info(__METHOD__, "Not null, suppose ok!");
        $aBeans = $redbean->convertToBeans($sName, array($aRow));
        //FlexiLogger::info(__METHOD__, "Rows: " . print_r($aBeans,true));
        return array_shift($aBeans);
      } else {
        //load by primary field / id
        return $redbean->load($sName, $mCond);
      }
    }//has condition
  }

  public function getRedBeanQuery() {
    return $this->getRedBeanToolbox()->getDatabaseAdapter();
  }

  public function setRedBeanTableIdField($sTable, $sField) {
    self::$aTableId[$sTable] = $sField;
  }

  public function getRedBeanWriter() {
    return self::getRedBeanToolbox()->getWriter();
  }

  public function getRedBeanToolbox($frozen = false) {
    static $toolbox = null;

    if ($toolbox == null) {
      $aSetting = $this->aSetting;
      $dsn = "mysql:host=" . $aSetting["host"] . ";dbname=" . $aSetting["dbname"];
      //$toolbox = RedBean_Setup::kickstartDev( $dsn,$aSetting["user"],$aSetting["pass"] );
      $pdo = new RedBean_Driver_PDO($dsn, $aSetting["user"], $aSetting["pass"]);
      $adapter = new RedBean_Adapter_DBAdapter( $pdo );

      if (strpos($dsn,"pgsql")===0) {
        //todo postgresql ver
        $writer = new FlexiReadBeanPostgreSQLWriter( $adapter, $frozen );
      }
			else if (strpos($dsn,"sqlite")===0){
        //todo sqlite version
        $writer = new FlexiReadBeanSQLiteWriter( $adapter, $frozen );
			}
			else {
        $writer = new FlexiReadBeanMySQLWriter( $adapter, $frozen );
			}
      
      $redbean = new RedBean_OODB( $writer );
			$toolbox = new RedBean_ToolBox( $redbean, $adapter, $writer );
    }
    //die('toolbox:' . serialize($toolbox));
    return $toolbox;
  }

  public function getRedBeanDB() {
    return $this->getRedBeanToolbox()->getRedBean();
  }

  public function initRedbean() {
    R::setup($this->sDSN);
  }


  public function newXPDOModel($sClass) {
    $xpdo = $this->getXPDO();
    return $xpdo->newObject($sClass);
  }

  public function getXPDOModel($sClass, $aCond=array()) {
    $xpdo = $this->getXPDO();
    return $xpdo->getObject($sClass, $aCond);
  }

  public function addXPDOModelPath($sName, $sPath, $sPrefix=null) {
    $xpdo = $this->getXPDO();
    return $xpdo->setPackage($sName, $sPath, $sPrefix);
    //return $xpdo->addPackage($sName,$sPath,$sPrefix);
  }

  public function generateXPDOClass($sSchemaFile, $sModelDir) {
    $xpdo = $this->getXPDO();
    $manager= $xpdo->getManager();
    $generator= $manager->getGenerator();
    return $generator->parseSchema($sSchemaFile, $sModelDir);
  }

  public function generateXPDO($sTable, $sPath, $sPrefix) {
    $xpdo = $this->getXPDO();
    $manager= $xpdo->getManager();
    $generator= $manager->getGenerator();
    $sSchemaFile = $sPath . "/" . (empty($sPrefix)? "default": $sPrefix) . ".mysql.schema.xml";
    $sModelDir   = $sPath . "/models/";
    $generator->writeSchema($sSchemaFile, $sTable, 'xPDOObject', $sPrefix);
    $generator->parseSchema($sSchemaFile, $sModelDir);

    return array($sSchemaFile, $sModelDir);
  }


  public function getXPDO() {
    
    if (is_null($this->xpdo)) {
      if(FlexiConfig::$sFramework == "modx2") {
        global $modx;
        $this->xpdo = & $modx;
      } else {
        $this->xpdo = new FlexiXPDO();
      }
    }
    //$this->xpdo->setDebug(true);
    //$this->xpdo->setLogLevel(xPDO :: LOG_LEVEL_DEBUG);
    //$this->xpdo->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');
    //$this->xpdo->setLogTarget('ECHO');

    return $this->xpdo;
  }

	public function getDB($bNew = false)
	{
		if($this->oDb == null)
		{
			$this->oDb = $this->getNewDB();
			return $this->oDb;
		}
		
		if ($bNew) { return $this->getNewDB(); }
		return $this->oDb;
	}
	
	public function getNewDB()
	{
		$aSetting = $this->aSetting;
		//$manager = Doctrine_Manager::getInstance();
		//var_dump($this->sDSN);
		return Doctrine_Manager::connection($this->sDSN);
		/*
		require_once(FlexiConfig::$sBaseDir . "/lib/u007adodb.php");
		$aSetting = $this->aSetting;
		$sDSN = "driver=" . $aSetting["type"] . "; host=" . $aSetting["host"] . ";";
		if (!empty( $aSetting["port"])) { $sDSN .= "port=" . $aSetting["port"]; }
		$sDSN .="user=" . $aSetting["user"] . "; pass=" . $aSetting["pass"] . ";";
		
		return AdoConnection($sDSN);
		*/
	}
	
	/**
	 * Get Current Doctrine connection
	 * @return link
	 */
	public function getCurrentDB()
	{
		return Doctrine_Manager::getInstance()->getCurrentConnection();
	}
	
	public function closeDB($oConn)
	{
		Doctrine_Manager::getInstance()->closeConnection($oConn);
	}
	
	public static function getDBInstance($bNew = false)
	{
		$oModel = self::getInstance();
		return $oModel->getDB($bNew);
	}
	
	public static function getInstance()
	{
		if (self::$oInstance == null)
		{
			self::$oInstance = new FlexiModelUtil();
		}
		return self::$oInstance;
	}
	
	/**
	 * Get table model 
	 * @param string - name of model
	 * @param string - path to module of the model
	 * 
	 */
	public static function getTableInstance($asName, $asPath)
	{
		$sModelClass = $asName . "Table";
		if(! class_exists($sModelClass))
		{
			$oInstance = self::getInstance();
			//var_dump($sModelClass);
			$sModelFile = $oInstance->getModelFile($sModelClass, $asPath);
			if (is_null($sModelFile))
			{
				FlexiLogger::error(__method__, "Table Model: " . $asName . " of " . $asPath . " is missing...");
				return null;
			}
			require_once($sModelFile);
		}
		
		return new $sModelClass();
	}
	
	
	public static function includeModelFile($asName, $asPath)
	{
		$sModelClass = $asName;
		if(! class_exists($sModelClass))
		{
			$oInstance = self::getInstance();
			$sModelFile = $oInstance->getModelFile($sModelClass, $asPath);
			if (is_null($sModelFile))
			{
				FlexiLogger::error(__METHOD__, "Model: " . $asName . " of " . $asPath . " is missing...");
				throw new FlexiException("Model: " . $asName . " of " . $asPath . " is missing...");
			}
			else
			{
        FlexiLogger::debug(__METHOD__, "Including model: " . $sModelFile);
				require_once($sModelFile);
				return;
			}
		}
	}
	/**
	 * Get model 
	 * @param string - name of model
	 * @param string - path to module of the model
	 * 
	 */
	public static function getModelInstance($asName, $asPath)
	{
		$sModelClass = $asName;
		self::includeModelFile($asName, $asPath);
		
		return new $sModelClass();
	}

  /**
	 * Get Doctrine query object
	 * @param string name
	 * @param string path (optional)
	 * @return Doctrine_Record
	 */
	public static function getDBQuery($asName, $asPath = null)
	{
		self::loadModel($asName, $asPath);
    FlexiLogger::debug(__METHOD__, "Loaded model: " . $asName);
		return Doctrine_Query::create()->from($asName);
	}

  /**
	 * load model
	 * @param string name
	 * @param path (optional)
	 */
	public static function loadModel($asName, $sPath = null)
	{
    $sModelName = $asName;
    if (strpos($sModelName, " ") !== false) {
      list($sModelName, $sAlias) = explode(" ", $sModelName, 2);
    }

		self::includeModelFile($sModelName, $sPath);
	}

	public function getModelFile($asName, $asPath)
	{
		$sBaseDir = FlexiConfig::$sBaseDir;
    $sRootDir = FlexiConfig::$sRootDir;
		$sModelFile = $asName;
		
		$sModelPath = $asPath . "/models/" . $sModelFile . ".php";
		//echo "sModelPath: [" . $sModelPath . "]";
    //if (FlexiConfig::$sFramework == "modx2") { echo "sModelPath: [" . $sModelPath . "]"; }
		if (is_file($sModelPath))
		{
			return $sModelPath;
		}
		if (is_file(strtolower($sModelPath)))
		{
			return strtolower($sModelPath);
		}

    $sModelPath = $sRootDir . "/" . $asPath . "/models/" . $sModelFile . ".php";
    //if (FlexiConfig::$sFramework == "modx2") { echo "sModelPath: [" . $sModelPath . "]"; }
		//echo "sModelPath: [" . $sModelPath . "]";
		if (is_file($sModelPath))
		{
			return $sModelPath;
		}
		if (is_file(strtolower($sModelPath)))
		{
			return strtolower($sModelPath);
		}

    $sModelPath = $sRootDir . "/../" . $asPath . "/models/" . $sModelFile . ".php";
    //if (FlexiConfig::$sFramework == "modx2") { echo "sModelPath: [" . $sModelPath . "]"; }
		//echo "sModelPath: [" . $sModelPath . "]";
		if (is_file($sModelPath))
		{
			return $sModelPath;
		}
		if (is_file(strtolower($sModelPath)))
		{
			return strtolower($sModelPath);
		}
		
		return null;
	}
	
	public function getRecordCount(& $oRecord, $sSelect = "count(*) as cnt", $sCol = "cnt")
	{
		$oResult = $oRecord->select($sSelect)->execute();
		$aRow = $oResult->toArray();
		if (count($aRow) > 0)
		{
			return $aRow[0][$sCol];
		}
		
		return 0;
	}
  
  public static function getErrorStackArray($aErrorStack) {
    $aResult = array();
    foreach($aErrorStack as $sKey => $aError)
		{
      $aResult[$sKey] = implode(",", $aError);
    }

    return $aResult;
  }

  public static function getErrorStackString($aErrorStack) {
    $sResult = "";
    foreach($aErrorStack as $sKey => $aError)
		{
      $sResult .= $sKey . ": ";
      
      $sError = "";
      foreach($aError as $sErrorType) {
        $sError .= empty($sError) ? "" : ", ";
        $sError .= self::getErrorStackLabel($sErrorType);
      }

      $sResult .= $sError . "\r\n<br>";
    }

    return $sResult;
  }

  public static function getErrorStackLabel($sErrorType) {
    switch($sErrorType)
    {
      case "type":
        return "Invalid input format";
      case "notnull":
        return "Field is required";
      case "length":
        return "Field length exceed";
      default:
        return "Error: " . $sErrorType;
    }
  }
}
