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

require_once("SiteTranslatorConfig.php");

//abstract persistance class
class LocalisedTextBase
{
	function LocalisedTextBase() {}
	function setLocalisedText($language, $textKey, $textValue, $blnUpdateTime) {}
	function getLocalisedText($textKey, $language) {}
	function getKeyList($language, $sort) {}
	function getLastUpdate($key, $language) {}
	function getUpdateTime($textKey, $language) {}
	function getWordCount($key, $language) {}
	function addText($language, $key, $text) {}
	function deleteText($key, $language) {}
}

class SiteTranslator
{
	var $config = null;
		
	/**
	* Constructor, sets up the session variables that keep track of the
	* vistors langugae preferences.
	*/
	function SiteTranslator()
	{
		$this->config = new SiteTranslatorConfig();
		session_start();
		
		//if this is the visitors first hit - determine their language
		if (! $_SESSION["visitorLanguage"])
			$_SESSION["visitorLanguage"]=$this->determineLanguage();
			
		//if a language selection query string has been passed, change language
		if ($_GET[$this->config->languageSelect])
			$_SESSION["visitorLanguage"] = $_GET[$this->config->languageSelect];
		
	}
	
	/**
	* Returns the text which $textKey is associated with in the language which
	* the visitor is currently browsing in. If it is not available in this language
	* the default language for the website is used.
	* If the visitor is an editor or translator a small 'translate' link is
	* appended to the text
	* 
	* @param	string $textKey			 	the key for this block of text
	* @param	bool   $suppressTranslate	if true, the 'translate' link is omitted
	* @return	string the localised text
	*/
	function getLocalisedText($textKey, $suppressTranslate = false)
	{						
		$textBase = &$this->config->textBase;
		
		//find the localised text for this key
		$blnTextIsLocalised = true;
		$localisedText = $textBase->getLocalisedText($textKey, $_SESSION["visitorLanguage"]);
		
		if ($localisedText==false)
		{		
			$blnTextIsLocalised = false;
			//if it does not exist, query the default language
			$localisedText = $textBase->getLocalisedText($textKey, $this->config->defaultLanguage);
			
			//if it does not exits in the default language - it should never have been used!
			if ($localisedText==false)
				return false;
		}
		
		//add span tags for right to left languages
		if ($this->config->languages[$_SESSION["visitorLanguage"]][1]==true && $blnTextIsLocalised)
			$localisedText = '<span dir="RTL">'.$localisedText.'</span>';		
			
		//if we in edit mode - add translation tags
		if( ($_SESSION["isTranslating"] || $_SESSION["isEditing"]) && !$suppressTranslate)
		{
			$translateLink = $this->config->translationDir."index.php?key=".$textKey;
			$translateText = str_replace("#", $translateLink, $this->config->translateTag);
			$localisedText = $translateText.$localisedText;
		}	
			
		return $localisedText;
	}
	
	/**
	* Adds a new block of text which can be retreived by the specified key
	*
	* @param	string $key		the key for this item of text
	* @param	string $text	the localisable text
	* @return	bool	true if the text has been successfully stored
	*/
	function addText($key, $text)
	{
		$textBase = &$this->config->textBase;
		
		//firstly, check whether this item already exists in the database
		if ($textBase->getLocalisedText($key, $this->config->defaultLanguage)!==false)
			return false;
			
		//if not - add it
		$textBase->addText($this->config->defaultLanguage, $key, $text);		
		return true;
	}
	
	/**
	* Determines whether an editor or translator are currently logged in
	*
	* @return	bool	true if editor or translator are logged in
	*/
	function isLoggedIn()
	{
		return ( $_SESSION["isTranslating"] || $_SESSION["isEditing"]);
	}
	
	/**
	* Determines the most preferable language to display to the visitor.
	* 
	* @return	string	the two letter language code
	*/
	function determineLanguage()
	{			
		//split up the language parameters
		$eachLanguage = explode("," , $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
		for ($i=0;$i<count($eachLanguage);$i++)
		{
			//determine if we support this language
			reset ($this->config->languages);
			while (list ($key, $val) = each ($this->config->languages))
			{
				if (strpos ($eachLanguage[$i],$key)!==FALSE)
				{
					//languages are listed in preference order, so we return on the first match
					return $key;
		    	}
			}
		}	
	
		//none found? - return the default
		return $this->config->defaultLanguage;
	}

	/**
	* Specfies the language that the visitor wishes to browse in
	* 
	* @param	string $language	the two letter language code
	*/
	function setLanguage($language)
	{		
		//check that this language is supported and not disabled
		if ($this->config->languages[$language] && !$this->config->languagesDisabled[$language])
			$_SESSION["visitorLanguage"] = $language;		
	}
	
	/**
	* Gets the current language being browsed in
	* 
	* @return	string	the two letter language code
	*/
	function getLanguage()
	{		
		return $_SESSION["visitorLanguage"];
	}
	
	/**
	* Gets the character set required to render this language
	* 
	* @return	string	the META tag which defines the character set
	*/
	function getCharSet()
	{
		$charset = $this->config->languages[$_SESSION["visitorLanguage"]][2];
		if ($charset) return $charset;
		
		//if no charset specified - return the default
		return $this->config->defaultCharset;
	}
	
	
	
	/**
	* Returns the languages which the specified text is available in
	* 
	* @param	string $key	the key for this block of text
	* @return	string	A CSV list of languages 
	*/
	function getLocalisedLanguages($key)
	{
		$localisedLanguages = "";
		$textBase = &$this->config->textBase;
		
		reset ($this->config->languages);
		while (list ($keyLang, $valLang) = each ($this->config->languages))
		{
			if (strpos($this->config->languagesDisabled,$key)===false)
			{
				if ($textBase->getLocalisedText($key, $keyLang)!==false)
					$this->addItemToCSVList($localisedLanguages,$keyLang);	
			}
		}
	
		return $localisedLanguages;
	}
	
	/**
	* Displays a flag for the specified language, if a link text is provided
	* the flag will hyperlink to the specified location
	* 
	* @param	string $language 	the two letter language code
	* @param	bool   $blnSmall 	whether to output a small (16x8) or large (32x16) flag
	* @param	string $strLinkText the desired hyperlink for this flag
	*/
	function showFlag($language, $blnSmall = true, $strLinkText="")
	{
		$strImgLink = $blnSmall ? $this->config->flagHomeSmall.$language : $this->config->flagHomeLarge.$language;
		if ($strLinkText!="")
		{
			//add the language selection to the link text
			//if an '?' symbol appears, the page has a query string, so we must append to it
			if (strpos($strLinkText,"?")===false)
				$strLinkText.="?".$this->config->languageSelect."=".$language;
			else
				$strLinkText.="&".$this->config->languageSelect."=".$language;		
					
			$openLink = "<a href=\"$strLinkText\">";
			$closeLink = '</a>';
		}
		if ($blnSmall)
			return "$openLink<img border=\"0\" src=\"".$this->config->flagHomeSmall.$language.".png\" width=\"16\" height=\"8\" hspace=\"2\">$closeLink";
		else
			return "$openLink<img border=\"0\" src=\"".$this->config->flagHomeLarge.$language.".png\" width=\"32\" height=\"16\" hspace=\"2\">$closeLink";
	}
	
	
	/**
	* Displays a flags for the specified languages, if a link text is provided
	* the flag will hyperlink to the specified location
	* 
	* @param	string $languages 	A CSV of required languages (if null, all supported languages will be output)
	* @param	bool   $blnSmall 	whether to output a small (16x8) or large (32x16) flag
	* @param	string $strLinkText the desired hyperlink for this flag
	*/
	function showFlags($languages="", $blnSmall = true, $strLinkText="")
	{		
		//if no languages are provided - use the language list
		if ($languages=="")
		{
			reset ($this->config->languages);
			while (list ($key, $val) = each ($this->config->languages))
			{
				//do not display disabled languages				
				if (strpos($this->config->languagesDisabled,$key)===false)
					$this->addItemToCSVList($languages,$key);		
			}
		}
				
		//create the image list
		$strReturn = "";
		$language = strtok ($languages,",");
		while($language)
		{
			$strReturn.=$this->showFlag($language, $blnSmall, $strLinkText);
			$language = strtok (",");
		}
		return $strReturn;
	}
	
	/**
	* Gets the default language
	* 
	* @return	string	the two letter language code
	*/
	function getDefaultLanguage()
	{
		return $this->config->defaultLanguage;
	}
	
	/**
	* Gets the name of the specified language, e.g. 'en' returns 'English'
	* 
	* @param	string	$key	the two letter language code
	* @return	string	the language name
	*/
	function getLanguageName($key)
	{
		return $this->config->languages[$key][0];
	}
	
	/**
	* Concatonates the supplied item onto the supplied CSV list
	* 
	* @param	string	&$csvList	the existing CSV list
	* @param	string	$item		the item to append
	*/
	function addItemToCSVList(&$csvList, $item) 
	{ 
	   	if (strlen($csvList)==0)
			$csvList=$item;
		else	
			$csvList.=",".$item;	
	} 		
}

?>