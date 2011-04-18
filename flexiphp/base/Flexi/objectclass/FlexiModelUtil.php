<?php

class FlexiModelUtil
{
	protected static $oInstance = null;
	protected $env = array();
	private $aSetting = array();
	private $sDSN = "";
	protected $oDb = null;
  protected $xpdo = null;

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


  public function getXPDOLastId() {
    $xpdo = $this->getXPDO();
    return $xpdo->lastInsertId();
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

  public static function parseSQLName($mName) {
    if (FlexiConfig::$sDBType=="mysql") {
      $sResult = "";
      $aName = is_array($mName) ? $mName: explode(",", $mName);
      foreach($aName as $sName) {
        $sResult .= empty($sResult) ? "":",";
        $aSingleName = explode(".", $sName);
        
        $sAliasName = "";
        foreach($aSingleName as $sOneName) {
          $sAliasName .= empty($sAliasName) ? "": ".";
          $sAliasName .= "`" . mysql_real_escape_string($sOneName) . "`";
        }
        $sResult .= $sAliasName;
      }
      return $sResult;
    }
    return $mName;
  }

  public static function parseSQLKey($sKey, $sValue) {
    $bDebug = false;
    $result = ""; $aParam = array();
    $aCond = explode(":", $sKey);

    //default
    $sType = "and";
    $sOperator = "=";
    if(count($aCond)==1) {
      $sField = $sKey;
      $sOperator = "=";
      $sType = "and";
      //not :s
    } else if(count($aCond) == 2) {
      if ($bDebug) echo __METHOD__ . ":Is 2 condition<br/>\n";
      if (strtolower($aCond[0])=="and" || strtolower($aCond[0])=="or") {
        $sType = $aCond[0];
        $sField = $aCond[1];
        if ($bDebug) echo __METHOD__ . ":with type cond<br/>\n";
        $sOperator = "=";
      } else {
        $sField = $aCond[0];
        $sOperator = $aCond[1];
        if ($bDebug) echo __METHOD__ . ":without type condition<br/>\n";
      }
      //2condition
    } else if(count($aCond) >=3) {
      if ($bDebug) echo __METHOD__ . ":Is 3 condition<br/>\n";
      if (strtolower($aCond[0])=="and" || strtolower($aCond[0])=="or") {
        $sType = $aCond[0];
        $sField = $aCond[1];
        $sOperator = $aCond[2];
        if ($bDebug) echo __METHOD__ . ":with type<br/>\n";
      } else {
        $sField = $aCond[0];
        $sOperator = $aCond[1];
        if ($bDebug) echo __METHOD__ . ":without type<br/>\n";
        //wats up with aCond[2]? todo...
      }
      
    } //3condition or more
    if ($bDebug) echo __METHOD__ . ":result type: " . $sType . "<br/>\n";
    
    //$sParamName = ":" . $sField . FlexiStringUtil::createRandomPassword(4);
    $sParamName = ":" . preg_replace("/[^a-zA-Z0-9_]/", "_", $sField) . FlexiStringUtil::createRandomPassword(4);

    switch(strtolower(trim($sOperator))) {
      case "isnull":
      case "is null":
        $sSQL = $sField . " IS NULL";
        break;
      case "isnotnull":
      case "is not null":
        $sSQL = $sField . " IS NOT NULL";
        break;
      default:
        $sSQL = $sField . " " . $sOperator . " " . $sParamName;
    }
    
    $aParam[$sParamName] = $sValue;
    return array(
      "type"  => $sType,
      "sql"   => $sSQL,
      "param" => $aParam
    );
  }

  
  public function insertOrUpdateXPDO($sTable, $oRow, $mPrimary=array()) {
    $bUpdate = false; $sWhere = "";

    $aPrimary = !is_array($mPrimary) ? explode(",", $mPrimary."") : $mPrimary;
    if (count($aPrimary) > 0) {
      $bHasID = true;
      //check if all id fields exists value
      foreach($aPrimary as $sIdField) {
        if (!isset($oRow[$sIdField])) {
         $bHasID = false; break; 
        }
        if (strlen($oRow[$sIdField]."") ==0) {
          $bHasID = false; break;
        }
      }
      
      if ($bHasID) {
        $aWhere = self::parseSQLCondKeyValue($aPrimary, $oRow);
        $sSQL = "SELECT " . self::getSQLName($aPrimarySQL) . " FROM " . $sTable . " WHERE ";
        $sSQL .= $aWhere["sql"];
        $row = $this->getXPDOFetchOne($sSQL, $aWhere["param"]);
        if (!is_null($row)) {
          $bUpdate = true;
        }
      }
    }

    return $bUpdate ? $this->updateXPDO($oRow, $sTable, $aPrimary):
             $this->insertXPDO($oRow, $sTable, $aPrimary);
  }

  public function updateXPDO($sTable, $oRow, $mPrimary="id") {
    $bDebug =  false;
    
    $aPrimary = !is_array($mPrimary) ? explode(",", $mPrimary."") : $mPrimary;

    if ($bDebug) echo __METHOD__ . ": primary:" . print_r($aPrimary,true) . "\n<br/>";
    $aWhere = self::parseSQLCondKeyValue($aPrimary, $oRow);
    $sFields = "";
    foreach($oRow as $sField => $sValue) {
      $sFields .= empty($sFields) ? "" : ",";
      $sFields .= self::parseSQLName($sField) . "=";

      if (is_null($sValue)) { $sFields .="null"; } else {
        $sFields .=self::getSQLValue($sValue);
      }
    }
    $sSQL = "UPDATE " . self::parseSQLName($sTable) . " SET " . $sFields . " WHERE " . $aWhere["sql"];
    if ($bDebug) echo __METHOD__ . ": sql:" . $sSQL . "\n<br/>";
    if ($bDebug) echo __METHOD__ . ": param:" . print_r($aWhere["param"],true) . "\n<br/>";

    $this->getXPDOExecute($sSQL, $aWhere["param"]);
    //if no exception, shall return true
    return true;
  }

  public function insertXPDO($sTable, $oRow, $aPrimary=array()) {
    $bDebug = false;
    //fields
    $aCols = array_keys($oRow);
    $sFields = "";

    if ($bDebug) echo __METHOD__ . ": " . print_r($oRow,true);
    foreach($aCols as $sField) {
      $sFields .= empty($sFields) ? "" : ",";
      $sFields .= self::parseSQLName($sField);
    }
    //values
    $aCols = array_values($oRow);
    $sFieldValues = "";
    foreach($aCols as $sField) {
      $sFieldValues .= empty($sFieldValues) ? "" : ",";
      $sFieldValues .= self::getSQLValue($sField);
    }
    $sSQL = "INSERT INTO " . self::parseSQLName($sTable) . " (" . $sFields . ") VALUES (" . $sFieldValues . ")";
    if ($bDebug) echo __METHOD__ . ": " . $sSQL;
    return $this->getXPDOExecute($sSQL);
  }
  /**
   * Execute sql
   * @param String $sSQL
   * @param array $aParam
   * @return int # of records effected
   */
  public function getXPDOExecute($sSQL, $aParam=array()) {
    $bDebug = false;
    $xpdo = $this->getXPDO();
    $sResultSQL = $xpdo->parseBindings($sSQL, $aParam);
    if ($bDebug) echo __METHOD__ . ": " . $sResultSQL . "<br/>\n";
    //echo "sql: " . $sResultSQL;
    $mResult = $xpdo->exec($sResultSQL);
    if ($mResult===false) {
      $aError = $xpdo->errorInfo();
      throw new Exception("Query failed: " . $aError[2] . ":".$sResultSQL);
    }
    return $mResult;
  }
  
  public function getXPDOFetchOne($sSQL, $aParam=array()) {
    $bDebug = false;
    $xpdo = $this->getXPDO();
    if ($bDebug) echo __METHOD__ . ": " . $sSQL . "<br/>\n";
    $sResultSQL = $xpdo->parseBindings($sSQL, $aParam);
    //echo "sql: " . $sResultSQL;
    $stmt = $xpdo->query($sResultSQL);
    if ($stmt) {
      $aResult = $stmt->fetch(PDO::FETCH_ASSOC);
      return $aResult;
    } else {
      $aError = $xpdo->errorInfo();
      throw new Exception("Query failed: " . $aError[2] . ":".$sResultSQL);
    }
    return null;
  }
  
  public function getXPDOFetchAll($sSQL, $aParam=array()) {
    $bDebug = false;
    $xpdo = $this->getXPDO();
    if ($bDebug) echo __METHOD__ . ": " . $sSQL . "<br/>\n";
    $sResultSQL = $xpdo->parseBindings($sSQL, $aParam);
    //echo "sql: " . $sResultSQL;
    $stmt = $xpdo->query($sResultSQL);
    if ($stmt) {
      $aResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $aResult;
    } else {
      $aError = $xpdo->errorInfo();
      throw new Exception("Query failed: " . $aError[2] . ":".$sResultSQL);
    }
    return null;
  }

  public static function parseSQLCondKeyValue($aKey, $oRow) {
    $aResult = array();
    foreach($aKey as $sField) {
      $aResult[$sField] = $oRow[$sField];
    }
    return self::parseSQLCond($aResult);
  }

  public static function parseSQLCond($aValue) {
    $bDebug = false;
    $result = ""; $aParam = array();
    if ($bDebug) echo __METHOD__ . ":count: " . count($aValue) . "<br/>\n";
    foreach($aValue as $sKey=> $mValue) {
      if (is_array($mValue)) {
        $aInnerCond = self::parseSQLCond($mValue);
        if ($bDebug) echo __METHOD__ . ":Is array: " . print_r($aInnerCond,true) . "<br/>\n";
        $aCond = self::parseSQLKey($sKey, "");
        $result .= empty($result) ? "": " " . $aCond["type"] . " ";
        $result .= "(" . $aInnerCond["sql"] . ")";
        $aParam = array_merge($aParam, $aInnerCond["param"]);
      } else {
        $aCond = self::parseSQLKey($sKey, $mValue);
        if ($bDebug) echo __METHOD__ . ":Is string: " . print_r($aCond,true) . "<br/>\n";
        $result .= empty($result) ? "": " " . $aCond["type"] . " ";
        $result .= $aCond["sql"];
        $aParam = array_merge($aParam, $aCond["param"]);
      } //is string
    } //foreach
    return array("sql" => $result, "param" => $aParam);
  } //function
	
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

  public static function parseFieldType($sFieldType) {
    $sType = "";
    $sPrecision = "";
    $aOption = array();
    $bUnsigned = false;
    if (substr($sFieldType,0,7)=="varchar") {
      $sType = "varchar";
      $sTemp = str_replace(array("varchar","(",")"), array("","",""), $sFieldType);
      if (!empty($sTemp)) {
        $sPrecision = $sTemp;
      }
    } else if (substr($sFieldType,0,4) == "char") {
      $sType = "char";
      $sTemp = str_replace(array("char","(",")"), array("","",""), $sFieldType);
      if (!empty($sTemp)) {
        $sPrecision = $sTemp;
      }
    } else if (substr($sFieldType,0,8) == "tinytext") {
      $sType = "tinytext";
      $sTemp = str_replace(array("tinytext","(",")"), array("","",""), $sFieldType);
      if (!empty($sTemp)) {
        $sPrecision = $sTemp;
      }
    } else if (substr($sFieldType,0,19) == "mediumtext") {
      $sType = "mediumtext";
      $sTemp = str_replace(array("mediumtext","(",")"), array("","",""), $sFieldType);
      if (!empty($sTemp)) {
        $sPrecision = $sTemp;
      }
    } else if (substr($sFieldType,0,8) == "longtext") {
      $sType = "longtext";
      $sTemp = str_replace(array("longtext","(",")"), array("","",""), $sFieldType);
      if (!empty($sTemp)) {
        $sPrecision = $sTemp;
      }
    } else if (substr($sFieldType,0,4) =="text") {
      $sType = "text";
      $sTemp = str_replace(array("text","(",")"), array("","",""), $sFieldType);
      if (!empty($sTemp)) {
        $sPrecision = $sTemp;
      }
    } else if (substr($sFieldType,0,8)== "tinyblob") {
      $sType = "tinyblob";
      $sTemp = str_replace(array("tinyblob","(",")"), array("","",""), $sFieldType);
      if (!empty($sTemp)) {
        $sPrecision = $sTemp;
      }
    } else if (substr($sFieldType,0,10)=="mediumblob") {
      $sType = "mediumblob";
      $sTemp = str_replace(array("mediumblob","(",")"), array("","",""), $sFieldType);
      if (!empty($sTemp)) {
        $sPrecision = $sTemp;
      }
    } else if (substr($sFieldType,0,8)=="longblob") {
      $sType = "longblob";
      $sTemp = str_replace(array("longblob","(",")"), array("","",""), $sFieldType);
      if (!empty($sTemp)) {
        $sPrecision = $sTemp;
      }
    } else if (substr($sFieldType,0,4)=="blob") {
      $sType = "blob";
      $sTemp = str_replace(array("blob","(",")"), array("","",""), $sFieldType);
      if (!empty($sTemp)) {
        $sPrecision = $sTemp;
      }
    } else if (substr($sFieldType,0,7)=="tinyint") {
      $sTemp = str_replace(array("tinyint","(",")", "unsigned"), array("","","",""), $sFieldType);
      $sType = "tinyint";
      if (!empty($sTemp)) {
        $sPrecision = $sTemp;
      }
      $bUnsigned = strpos($sFieldType, "unsigned") !== false;
    } else if (substr($sFieldType,0,9)== "mediumint") {
      $sTemp = str_replace(array("mediumint","(",")", "unsigned"), array("","","",""), $sFieldType);
      $sType = "mediumint";
      if (!empty($sTemp)) {
        $sPrecision = $sTemp;
      }
      $bUnsigned = strpos($sFieldType, "unsigned") !== false;
    } else if (substr($sFieldType,0,8)=="smallint") {
      $sTemp = str_replace(array("smallint","(",")", "unsigned"), array("","","",""), $sFieldType);
      $sType = "smallint";
      if (!empty($sTemp)) {
        $sPrecision = $sTemp;
      }
      $bUnsigned = strpos($sFieldType, "unsigned") !== false;
    } else if (substr($sFieldType,0,6)=="bigint") {
      $sTemp = str_replace(array("bigint","(",")", "unsigned"), array("","","",""), $sFieldType);
      $sType = "bigint";
      if (!empty($sTemp)) {
        $sPrecision = $sTemp;
      }
      $bUnsigned = strpos($sFieldType, "unsigned") !== false;
    } else if (substr($sFieldType,0,3)== "int" || substr($sFieldType,0,7)=="integer") {
      $sTemp = str_replace(array("int","(",")", "unsigned"), array("","","",""), $sFieldType);
      $sType = "int";
      if (!empty($sTemp)) {
        $sPrecision = $sTemp;
      }
      $bUnsigned = strpos($sFieldType, "unsigned") !== false;
    } else if (substr($sFieldType,0,5)=="float") {
      $sTemp = str_replace(array("float","(",")", "unsigned"), array("","","",""), $sFieldType);
      $sType = "float";
      if (!empty($sTemp)) {
        $sPrecision = $sTemp;
      }
      $bUnsigned = strpos($sFieldType, "unsigned") !== false;
    } else if (substr($sFieldType,0,6)=="double") {
      $sTemp = str_replace(array("double","(",")", "unsigned"), array("","","",""), $sFieldType);
      $sType = "double";
      if (!empty($sTemp)) {
        $sPrecision = $sTemp;
      }
      $bUnsigned = strpos($sFieldType, "unsigned") !== false;
    } else if (substr($sFieldType,0,7)=="decimal") {
      $sTemp = str_replace(array("decimal","(",")", "unsigned"), array("","","",""), $sFieldType);
      $sType = "decimal";
      if (!empty($sTemp)) {
        $sPrecision = $sTemp;
      }
      $bUnsigned = strpos($sFieldType, "unsigned") !== false;
    } else if (substr($sFieldType,0,4)== "enum") {
      $sTemp = str_replace(array("enum","(",")"), array("","",""), $sFieldType);
      $sType = "enum";
      if (!empty($sTemp)) {
        $aOption = explode(",", $sTemp);
      }
    } else if (substr($sFieldType,0,4)=="date") {
      //$sTemp = str_replace(array("date"), array(""), $sFieldType);
      $sType = "date";
    } else if (substr($sFieldType,0,8)=="datetime") {
      //$sTemp = str_replace(array("datetime"), array(""), $sFieldType);
      $sType = "datetime";
    } else if (substr($sFieldType,0,9)=="timestamp") {
      //$sTemp = str_replace(array("datetime"), array(""), $sFieldType);
      $sType = "timestamp";
    } else {
      throw new Exception("Unsupported type: " . $sFieldType);
    }

    return array(
      "type"=> $sType,
      "precision" => $sPrecision,
      "unsigned" => $bUnsigned,
      "option" => $aOption);
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

  public static function getSQLName($mName) {
    switch(FlexiConfig::$sDBType) {
      case "mysql":
        if (is_array($mName)) {
          foreach($mName as & $sName) {
            $sName = "`" . mysql_real_escape_string($sName) . "`";
          }
          return implode(",", $mName);
        } else {
          return "`" . mysql_real_escape_string($mName) . "`";
        }
    }
    return $sName;
  }

  public static function getSQLValue($sValue) {
    switch(FlexiConfig::$sDBType) {
      case "mysql":
        return "'" . self::getSQLRaw($sValue) . "'";

    }
    return $sName;
  }

  public static function getSQLRaw($sValue) {
    switch(FlexiConfig::$sDBType) {
      case "mysql":
        return mysql_real_escape_string($sValue);

    }
    return $sName;
  }

  public static function getDefaultSQL($mDefault, $bCanNull) {
    $mDefault = is_null($mDefault)? "": $mDefault;
    if ($bCanNull) {
      if (strlen($mDefault) == 0) {
        return "";
      } else {
        return "DEFAULT " . $mDefault;
      }
    } else {
      //cannot null
      if (strlen($mDefault) == 0) {
        return "NOT NULL";//IGNORE DEFAULT
      } else {
        return "NOT NULL DEFAULT " . $mDefault;
      }
    }
  }

  public static function getSQLValueStatement($sValue) {
    if (is_null($sValue)) return "NULL";
    if (strlen($sValue) == 0) return "''";
    if(substr(ltrim($sValue),0,1) == "'") return $sValue;//already got delimiter
    return self::getSQLValue($sValue);
  }
  
  public static function existsTable($sName) {
    $sSQL = "select * from " . self::getSQLName($sName);
    try {
      self::getInstance()->getXPDOFetchOne($sSQL);
      return true;
    } catch (Exception $e) {
      return false;
    }
  }

  /**
   * Get table schema
   * @param String $sName
   * @return array ["Field", "Type", "Null", "Key", "Default", "Extra"]
   */
  public static function getTableSchema($sName, $bCache=true) {
    static $aCache = array();
    if ($bCache && isset($aCache[$sName])) return $aCache[$sName];
    switch(FlexiConfig::$sDBType) {
      case "mysql":
        $sSQL = "describe " . self::getSQLName($sName);
        $aList = self::getInstance()->getXPDOFetchAll($sSQL);
        break;
      default:
        throw new Exception(__METHOD__ . ": unhandled dbtype: " . FlexiConfig::$sDBType);
    }
    if ($bCache) $aCache[$sName] = $aList;

    return $aList;
  }

  public static function getTableList() {
    static $aCache = array();
    if ($bCache && isset($aCache[$sName])) return $aCache[$sName];
    switch(FlexiConfig::$sDBType) {
      case "mysql":
        $sSQL = "show tables";
        $aList = self::getInstance()->getXPDOFetchAll($sSQL);
        break;
      default:
        throw new Exception(__METHOD__ . ": unhandled dbtype: " . FlexiConfig::$sDBType);
    }

    $aResult = array();
    foreach($aList as $oTable) {
      foreach($oTable as $sKey=>$sValue) {
        $aResult[] = array("name" => $sValue);
      }
    }

    if ($bCache) $aCache[$sName] = $aResult;

    return $aResult;
  }


  public static function getObjectManager() {
    static $oManager = null;
    if (is_null($oManager)) {
      $oManager = new FlexiObjectManager();
      $oManager->setPath(FlexiConfig::$sRepositoryDir);
    }
    return $oManager;
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

  public function  __sleep() {
    $aKey = array_keys(get_object_vars($this));

    $aResult = array();
    foreach($aKey as $sKey) {
      if ($sKey != "xpdo") $aResult[] = $sKey;
    }
    return $aResult;
  }
}
