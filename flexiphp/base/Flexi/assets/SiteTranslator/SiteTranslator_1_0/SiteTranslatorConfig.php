<?
/*
$Author: colineberhardt $
$Date: 2004/11/16 10:26:59 $
$Revision: 1.2 $
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

//require_once("LocalisedTextBaseMySQL.php");
require_once("LocalisedTextBaseDemo.php");

class SiteTranslatorConfig
{	
	//the default language is the language which text entries are 
	//initially created in 
	var $defaultLanguage = "en";
	
	//the default charset
	var $defaultCharset = "iso-8859-1";
	
	//languages which are supported in the text base. The key is the standard
	//two letter language identifier. The array provides
	//the full name for the language, a boolean which determines whether
	//the text should be output right-to-left, and the character set required.
	//
	//Each language requires ints own table in the database with the
	//schema provided in LocalisedTextBaseMySQL
	var $languages = array(
		"en"=>	array("English",	false,	""),
		"de"=>	array("German",		false,	""),
		"ru"=>	array("Russian",	false,	"koi8-r")
		);			
	
	//languages which are not displayed to the visitor 
	//(i.e. they are under development)
	var $languagesDisabled = "";		
	
	//the query string used to select languages
	var $languageSelect = "lang";
	
	//the translators, the key is their username, the first element is their password
	//and the next is the languages which they can tranlsate, the final field
	//determines whether they are editors	
	//NOTE: you do not need to specify languages for editors - they can edit anything!
	var $translators = array(
		"trans_de"		=> 	array("de_pass", 	"de", 	false),
		"trans_ru"		=> 	array("ru_pass", 	"ru", 	false),
		"trans_all"		=> 	array("all_pass", 	"ru,de",false),
		"editor"		=> 	array("editor_pass","", 	true)
		);
			
	//translate tag	- the # symbol is replaced with the translation URL
	var $translateTag =	"<a style=\"font-size:9px; background:yellow\" href=\"#\">translate</a>";
	
	//the absolute location of the translation directory
	var $translationDir = "[your_site]/translation/";
	
	//the absolute directories where flag images are stored
	var $flagHomeSmall = "[your_site]/img/flags/16px/";
	var $flagHomeLarge = "[your_site]/img/flags/32px/";
	
	//the text base is the class which provides persistance
	var $textBase = null;

	//variables specific to the MySQL text base	
	var $mysqlServer = 		"localhost";
	var $mysqlUsername = 	"root";
	var $mysqlPassword = 	"";
	var $mysqlDb = 			"";
	//the name of the table used to store text in a specific lanuage, the # is replaced
	//with the two character language code
	var $mysqlTableName = "text_#";
	
	//the constructor simply creates an instance of the persistant data store
	function SiteTranslatorConfig()
	{
		//$this->textBase = new LocalisedTextBaseMySQL($this);
		$this->textBase = new LocalisedTextBaseDemo($this);
	}
}

?>
