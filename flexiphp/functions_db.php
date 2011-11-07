<?php

global $_lockedTable;
$_lockedTable = array();
global $_aFlexiExecutedResult;
$_aFlexiExecutedResult = array();

function dbGetConfirmLock($sName, $iDuration=null, $pdo=null) {
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  $iDuration = empty($iDuration) ? 60: (int)$iDuration;
  $oRow = dbFetchOne("select IS_FREE_LOCK(:name) as isfree;", array(":name" => $sName));
  if ($oRow["isfree"] == 1) {
    dbGetLock($sName, $iDuration, $pdo);
  }
}

function dbGetLock($sName, $iDuration=null, $pdo=null) {
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  $iDuration = empty($iDuration) ? 60: (int)$iDuration;
  dbExecute("SELECT GET_LOCK(:name,:duration)", array(":name"=>$sName, ":duration" => $iDuration), $pdo);
}

function dbUnlock($sName, $pdo=null) {
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  dbExecute("SELECT RELEASE_LOCK(:name)", array(":name"=>$sName), $pdo);
}

function dbBegin($pdo = null) {
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  dbExecute("SET AUTOCOMMIT=0", array(), $pdo);
  dbExecute("START TRANSACTION", array(), $pdo);
  //dbExecute("BEGIN", array(), $conn);
  return $pdo;
}

function dbCommit($pdo = null) {
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  dbExecute("COMMIT", array(), $pdo);
  dbExecute("SET AUTOCOMMIT=1", array(), $pdo);
  return $pdo;
}

function dbRollback($pdo = null) {
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  dbExecute("ROLLBACK", array(), $pdo);
  dbExecute("SET AUTOCOMMIT=1", array(), $pdo);
  return $pdo;
}

function dbLockTable($sTable, $type="write", $pdo=null) {
  global $_lockedTable;
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  if (in_array(strtolower($sTable), $_lockedTable)) return;
  $_lockedTable[] = strtolower($sTable);
  return dbExecute("lock tables " . $sTable . " " . $type, array(), $pdo);
}

function dbUnlockTable($pdo=null) {
  global $_lockedTable;
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  $_lockedTable = array(); //free all tables
  return dbExecute("UNLOCK TABLES", array(), $pdo);
}

function dbCleanValue($sValue) {
  /*
  $aReplace = array(
    "'" => "''",
    "\\" => "\""
  );
  */
  //$aResult = str_replace(array_keys($aReplace), array_values($aReplace), $sValue);
  $mResult = @mysql_escape_string($sValue);
  return $mResult;
}

/**
 * Cleanup invalid names
 * @param String $sName
 * @return String
 */
function dbCleanName($sName) {
  $sResult = preg_replace("/[^0-9a-zA-Z\-\+\_\s]/", "", $sName);
  return $sResult;
}

function parseDBSQLCond(& $sValue) {
  if (is_array($sValue)) {
    throw new Exception("Value cannot be an array, " . print_r($sValue,true));
  }
  $sValue = "'" . dbCleanValue($sValue) . "'";
}

/**
 * Alias of dbAffectedRows();
 * @link dbAffectedRows()
 */
function dbGetLastEffectedRows() {
  dbAffectedRows();
}

function dbGetLastId($pdo=null) {
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  return $pdo->lastInsertId();
}

function dbInsertOrUpdate($aValue, $sTable, $aPrimary = "id", $pdo=null) {
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
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
      $aWhere = FlexiModelUtil::parseSQLCondKeyValue($aPrimary, $oRow);
      $sSQL = "SELECT " . self::getSQLName($aPrimary) . " FROM " . $sTable . " WHERE ";
      $sSQL .= $aWhere["sql"];
      //echo $sSQL;
      //var_dump($aWhere);
      $row = dbFetchOne($sSQL, $aWhere["param"], $pdo);
      if ($row!==false) {
        $bUpdate = true;
      }
    }
  }

  return $bUpdate ? dbUpdate($oRow, $sTable, $aPrimary):
           $this->dbInsert($oRow, $sTable, $aPrimary);
}

function dbInsert($oRow, $sTable, $idField="id", $pdo=null) {
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  
  $bDebug = false;
  //fields
  $aCols = array_keys($oRow);
  $sFields = "";
  $sFieldValues = "";
  $aParam = array();

  if ($bDebug) echo __METHOD__ . ": " . print_r($oRow,true);
  foreach($aCols as $sField) {
    $sFieldRaw = dbCleanName($sField);
    $sFieldName = FlexiModelUtil::parseSQLName($sField);
    $sFields .= empty($sFields) ? "" : ",";
    $sFields .= $sFieldName;

    $sFieldValues .= empty($sFieldValues) ? "" : ",";
    $sFieldValues .= ":" . $sFieldRaw;
    $aParam[":" . $sFieldRaw] = $oRow[$sField];
  }
  $sSQL = "INSERT INTO " . FlexiModelUtil::parseSQLName($sTable) . " (" . $sFields . ") VALUES (" . $sFieldValues . ")";
  
  return dbExecute($sSQL, $aParam, $pdo);
}

function dbUpdate($oRow, $sTable, $aPrimary="id", $pdo=null) {
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  
  $bDebug =  false;
  $aPrimary = !is_array($mPrimary) ? explode(",", $mPrimary."") : $mPrimary;

  if ($bDebug) echo __METHOD__ . ": primary:" . print_r($aPrimary,true) . "\n<br/>";
  $aWhere = self::parseSQLCondKeyValue($aPrimary, $oRow);
  $sFields = "";
  $aParam = $aWhere["param"];
  foreach($oRow as $sField => $sValue) {
    $sFieldRaw = self::dbCleanName($sField);
    $sFieldName = self::parseSQLName($sField);
    $sFields .= empty($sFields) ? "" : ",";
    $sFields .= $sFieldName . "=:_update_" . $sFieldRaw;
    $aParam[":_update_" . $sFieldRaw] = $oRow[$sField];
  }
  $sSQL = "UPDATE " . self::parseSQLName($sTable) . " SET " . $sFields . " WHERE " . $aWhere["sql"];
  if ($bDebug) echo __METHOD__ . ": sql:" . $sSQL . "\n<br/>";
  if ($bDebug) echo __METHOD__ . ": param:" . print_r($aWhere["param"],true) . "\n<br/>";
  dbExecute($sSQL, $aParam, $pdo);
  //if no exception, shall return true
  return true;
}

function dbFetchAll($sSQL, $aParam=array(), $pdo=null) {
  $bDebug = false;
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  
  if ($bDebug) echo __METHOD__ . ": " . $sSQL . "<br/>\n";
  $sResultSQL = $pdo->parseBindings($sSQL, $aParam);
  //echo "sql: " . $sResultSQL;
  $stmt = $pdo->query($sResultSQL);
  if ($stmt) {
    $aResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $aResult;
  } else {
    $aError = $pdo->errorInfo();
    throw new Exception("Query failed: " . $aError[2] . ":".$sResultSQL);
  }
  return null;
}

function dbFetchOne($sSQL, $aParam=array(), $pdo=null) {
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  $bDebug = false;
  if ($bDebug) echo __METHOD__ . ": " . $sSQL . "<br/>\n";
  if ($bDebug) echo __METHOD__ . ": " . print_r($aParam,true) . "<br/>\n";
  $sResultSQL = $pdo->parseBindings($sSQL, $aParam);
  //echo "sql: " . $sResultSQL;
  if ($bDebug) echo __METHOD__ . ": result: " . $sResultSQL . "<br/>\n";
  $stmt = $pdo->query($sResultSQL);
  if ($stmt) {
    $aResult = $stmt->fetch(PDO::FETCH_ASSOC);
    return $aResult;
  } else {
    $aError = $pdo->errorInfo();
    throw new Exception("Query failed: " . $aError[2] . ":".$sResultSQL);
  }
  return null;
}

function dbQuery($sSQL, $aParam=array(), $pdo=null) {
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  $bDebug = false;
  $sResultSQL = $pdo->parseBindings($sSQL, $aParam);
  $stmt = $pdo->query($sResultSQL);
  return $stmt;
}

function dbAffectedRows() {
  global $_aFlexiExecutedResult;
  if (count($_aFlexiExecutedResult) > 0) return $_aFlexiExecutedResult[count($_aFlexiExecutedResult)-1];
}

function dbExecute($sSQL, $aParam=array(), $pdo=null) {
  $bDebug = false;
  global $_aFlexiExecutedResult;
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  
  $sResultSQL = $pdo->parseBindings($sSQL, $aParam);
  if ($bDebug) echo __METHOD__ . ": " . $sResultSQL . "<br/>\n";
  //echo "sql: " . $sResultSQL;
  $mResult = $pdo->exec($sResultSQL);
  if ($mResult===false) {
    $aError = $xpdo->errorInfo();
    throw new Exception("Query failed: " . $aError[2] . ":".$sResultSQL);
  }
  return $mResult;
}

function dbNewConnection() {
  return FlexiModelUtil::getInstance()->getNewXPDO();
}

function dbGetConnection() {
  return FlexiModelUtil::getInstance()->getXPDO();
}

function dbReconnect(& $pdo=null) {
  if (!is_null($pdo)) return $pdo;
  //there is no reconnect in pdo
  return FlexiModelUtil::getInstance()->getXPDO();
}

function dbClose(& $pdo=null) {
  $pdo = null;
}

register_shutdown_function("dbClose");
