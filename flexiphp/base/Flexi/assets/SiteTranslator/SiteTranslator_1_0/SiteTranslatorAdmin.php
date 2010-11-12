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

require_once ("SiteTranslator.php");

class SiteTranslatorAdmin extends SiteTranslator
{
	
	function login($username, $password)
	{
		$translators = &$this->config->translators;
				
		if ($translators[$username][0]!=$password)
			return false;
		
		//see if this is an editor			
		if ($translators[$username][2])
		{
			//assemble a complete language list (bar the default language)
			$languageArray = $this->getLanguageList();
			$languageList = "";
			while (list ($keyLanguage, $valLanguage) = each ($languageArray))
				if ($keyLanguage != $this->config->defaultLanguage)
					$this->addItemToCSVList($languageList, $keyLanguage);	
			
			$_SESSION["translatorLanguages"]=$languageList;
			$_SESSION["isEditing"]=true;
		}
		else
		{
			$_SESSION["translatorLanguages"]=$translators[$username][1];
			$_SESSION["isTranslating"]=true;				
		}
		return true;
	}
	
	function logout()
	{
		$_SESSION["isTranslating"]=false;
		$_SESSION["isEditing"] = false;
	}
	
	function addKeyValuePair(&$array, $key, $val) 
	{ 
	   $tempArray = array($key => $val); 
	   $array = array_merge ($array, $tempArray); 
	} 
	
		
	function SiteTranslatorAdmin()
	{
		parent::SiteTranslator();		
	}
	
	function getKeyList($sort = "date")
	{
		$textBase = &$this->config->textBase;
		return $textBase->getKeyList($this->config->defaultLanguage, $sort);
	}
	
	function getLanguageList()
	{
		return $this->config->languages;
	}
	
		
	function isEditor()
	{
		return $_SESSION["isEditing"];
	}
	
		
	function deleteText($key)
	{
		$textBase = &$this->config->textBase;
		
		//delete this entry from each language text base
		reset ($this->config->languages);
		while (list ($keyLang, $valLang) = each ($this->config->languages))
			$textBase->deleteText($key, $keyLang);
		
	}
	
	function isTranslated($key, $language)
	{						
		//find the localised text for this key
		$textBase = &$this->config->textBase;
		$localisedText = $textBase->getLocalisedText($key, $language);		
		return ($localisedText!==false);		
	}
	
	function isUpToDate($key, $language)
	{						
		$textBase = &$this->config->textBase;
		
		//get the translated date of the default language
		$dateDefault = $textBase->getUpdateTime($key, $this->config->defaultLanguage);
		
		//get the translated date of this language
		$date = $textBase->getUpdateTime($key, $language);
		
		return ($dateDefault>$date) ? false : true;
	}
	
	function setLocalisedText($language, $textKey, $textValue, $blnUpdateTime = true)
	{
		$textBase = &$this->config->textBase;
		$textBase->setLocalisedText($language, $textKey, $textValue, $blnUpdateTime);
	}
	
	function getLocalisedText($language, $textKey)
	{
		$textBase = &$this->config->textBase;
		return $textBase->getLocalisedText($textKey, $language);	
	}
	
	function getTranslatorLanguages()
	{
		return $_SESSION["translatorLanguages"];
	}
	
	
	function getTranslationStatus($sort = "date")
	{			
		$textBase = &$this->config->textBase;
		
		$translatedKeys = array();			
		$nonTranslatedKeys = array();			
		$translatedKeysRequiringUpdate = array();	
		
		$keyArray = $this->getKeyList($sort);		
		foreach($keyArray as $key)
		{
			$strLanguagesTranslated = "";
			$strLanguagesNotTranslated = "";
			$strLanguagesRequireUpdate = "";
			
			//check the translated state of each langauge
			$language = strtok ($_SESSION["translatorLanguages"],",");
			while($language)
			{
				if (! $this->isTranslated($key, $language) )
				{								
					$this->addItemToCSVList($strLanguagesNotTranslated, $language);
				}
				else
				{					
					if (! $this->isUpToDate($key, $language))
					{
						$this->addItemToCSVList($strLanguagesRequireUpdate, $language);	
					}
					else
					{	
						$this->addItemToCSVList($strLanguagesTranslated, $language);	
					}
					
				}
				$language = strtok (",");
			}
			
			$wordCount = $textBase->getWordCount($key, $this->config->defaultLanguage);
			$lastUpdate = $textBase->getLastUpdate($key, $this->config->defaultLanguage);
			
			if (strlen($strLanguagesNotTranslated)>0)
				$this->addKeyValuePair($nonTranslatedKeys, $key,
					array($strLanguagesNotTranslated, $wordCount, $lastUpdate));
				
			if (strlen($strLanguagesRequireUpdate)>0)
				$this->addKeyValuePair($translatedKeysRequiringUpdate, $key,
					array($strLanguagesRequireUpdate, $wordCount, $lastUpdate));
				
			if (strlen($strLanguagesTranslated)>0)
				$this->addKeyValuePair($translatedKeys, $key,
					array($strLanguagesTranslated, $wordCount, $lastUpdate));
		}
		
		return array ($translatedKeys, $nonTranslatedKeys, $translatedKeysRequiringUpdate);
	}
	
}

?>