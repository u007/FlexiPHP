<?
/*
$Author: colineberhardt $
$Date: 2004/11/12 16:30:18 $
$Revision: 1.1 $
$Name:  $

Copyright (C) 2004  C.N.Eberhardt (webmaster@jugglingdb.com)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

/*
Provides a means of persisting text in a MySQL database

The following schema should be used:

CREATE TABLE **table name** (
   textKey varchar(50) NOT NULL,
   textValue text NOT NULL,
   lastUpdate datetime DEFAULT '0000-00-00 00:00:00'  NOT NULL,
   PRIMARY KEY (textKey)
)


*/

class LocalisedTextBaseMySQL extends LocalisedTextBase
{
	var $config;
	var $connection; 
		
	function executeQuery($strQuery)
	{
		//select the database
		if (!mysql_select_db($this->config->mysqlDb, $this->connection))
			die('Could not select database: '. mysql_error());		
			
		//run the query
		if (! ($result = mysql_query($strQuery,$this->connection)) )
			die("Error in query {$strQuery} :". mysql_error());		
			
		return $result;
	}
		
	function LocalisedTextBaseMySQL($config)
	{
		$this->config = $config;
		
		//create a connection to the MySQL server
		$this->connection = mysql_connect($config->mysqlServer, $config->mysqlUsername, $config->mysqlPassword);
		if (!$this->connection)  die('Could not connect to server: ' . mysql_error());
	}
	
	function getTableName($language)
	{
		return str_replace("#", $language, $this->config->mysqlTableName);
	}
	
	function setLocalisedText($language, $textKey, $textValue, $blnUpdateTime)
	{			
		$tableName = $this->getTableName($language);
		//determine  whether this key exists in the database
		if ($this->getLocalisedText($textKey, $language)===false)
		{
			//if not - create it
			$this->addText($language, $textKey, $textValue);
		}
		else
		{
			//if it does - just update it
			$strUpdateTime="";
			if ($blnUpdateTime) $strUpdateTime=", lastUpdate=NOW()";
			$strQuery = "UPDATE $tableName SET textValue='$textValue' $strUpdateTime WHERE textKey='$textKey'";
			$this->executeQuery($strQuery);	
		}
	}
	
	function addText($language, $key, $text)
	{
		$tableName = $this->getTableName($language);
		$strQuery = "INSERT INTO $tableName (textKey, textValue, lastUpdate) VALUES ('$key', '$text', NOW())";
		$this->executeQuery($strQuery);	
	}
	
	
	function getLocalisedText($textKey, $language)
	{
		$tableName = $this->getTableName($language);
		$strQuery = "SELECT textValue FROM $tableName WHERE textKey='$textKey'";
		$result = $this->executeQuery($strQuery);
				
		//if there were no results - this text does not exist
		if (mysql_num_rows($result)==0)
			return false;
			
		//otherwise - return the text
		$myrow = mysql_fetch_array($result);
		return $myrow["textValue"];
	}	
	
	function getUpdateTime($textKey, $language)
	{
		$tableName = $this->getTableName($language);		
		$strQuery = "SELECT lastUpdate FROM $tableName WHERE textKey='$textKey'";
		$myrow = mysql_fetch_array($this->executeQuery($strQuery));
		return $myrow["lastUpdate"];
	}
	
	function getKeyList($language, $sort = "date")
	{		
		$tableName = $this->getTableName($language);
		
		//work out the ordering
		$strOrder = "";		
		if ($sort=="name") $strOrder = "ORDER BY textKey";
		if ($sort=="words") $strOrder = "ORDER BY words ASC";
		if ($sort=="date") $strOrder = "ORDER BY lastUpdate DESC";
		
		//query the database
		$strQuery = "SELECT textKey, length(textValue)/5 as words FROM $tableName $strOrder";
		$result = $this->executeQuery($strQuery);
		
		//create the key array
		$keys = array();
		while ($myrow = mysql_fetch_array($result))
		{
			array_push($keys, $myrow["textKey"] );
		}		
		return $keys;
	}
	
	function getWordCount($key, $language)
	{
		//assumes that the mean word length is 5 characters long
		$tableName = $this->getTableName($language);	
		$strQuery = "SELECT length(textValue)/5 as words FROM $tableName WHERE textKey='$key'";
		$myrow = mysql_fetch_array($this->executeQuery($strQuery));
		return (int)$myrow["words"];
	}
	
	function getLastUpdate($key, $language)
	{
		$tableName = $this->getTableName($language);	
		$strQuery = "SELECT lastUpdate FROM $tableName WHERE textKey='$key'";
		$myrow = mysql_fetch_array($this->executeQuery($strQuery));
		return $myrow["lastUpdate"];
	}
	
	function deleteText($key, $language)
	{
		$tableName = $this->getTableName($language);	
		$strQuery = "DELETE FROM $tableName WHERE textKey='$key'";
		$this->executeQuery($strQuery);
	}
}
?>