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

function dbInsertOrUpdate($aValue, $sTable, $idField = "id", $pdo=null) {
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  
  $pdo->insertOrUpdateXPDO($sTable, $aValue, $idField);
}

function dbInsert($aValue, $sTable, $idField="id", $pdo=null) {
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  $pdo->insertXPDO($sTable, $aValue, $idField);
}

function dbUpdate($aValue, $sTable, $idField="id", $pdo=null) {
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  $pdo->updateXPDO($sTable, $aValue, $idField);
}

function dbFetchAll($sSQL, $aParam=array(), $pdo=null) {
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  
  $aResult =$pdo->getXPDOFetchAll($sSQL, $aParam);
  return $aResult;
}

function dbFetchOne($sSQL, $aParam=array(), $pdo=null) {
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  $bDebug = false;
  
  return $pdo->getXPDOFetchOne($sSQL, $aParam);
}

function dbAffectedRows() {
  global $_aFlexiExecutedResult;
  if (count($_aFlexiExecutedResult) > 0) return $_aFlexiExecutedResult[count($_aFlexiExecutedResult)-1];
}

function dbExecute($sSQL, $aParam=array(), $pdo=null) {
  global $_aFlexiExecutedResult;
  $pdo = is_null($pdo) ? FlexiModelUtil::getInstance()->getXPDO(): $pdo;
  
  
  return $pdo->getXPDOExecute($sSQL, $aParam);
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
