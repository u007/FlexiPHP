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

/*
This class ....
*/

class LocalisedTextBaseDemo extends LocalisedTextBase
{
	var $config;	
	var $textBase = array
	(
		//English text
		"en" => array (
			 "country_algeria" => "Algeria" , 
			 "country_argentina" => "Argentina",  
		     "country_australia" => "Australia" , 
			 "country_austria" => "Austria"  ,
			 "country_belgium" => "Belgium"  ,
			 "country_brazil" => "Brazil"  ,
			 "country_canada" => "Canada" , 
			 "country_chile" => "Chile"  ,
			 "country_china" => "China"  ,
			 "country_colombia" => "Colombia"  ,
			 "country_czech_republic" => "Czech Republic"  ,
			 "country_denmark" => "Denmark"  ,
			 "country_ecuador" => "Ecuador"  ,
			 "country_ethiopia" => "Ethiopia"  ,
			 "country_finland" => "Finland"  ,
			 "country_france" => "France"  ,
			 "country_germany" => "Germany"  ,
			 "country_greece" => "Greece"  ,
			 "country_hungary" => "Hungary"  ,
			 "country_ireland" => "Ireland"  ,
			 "country_israel" => "Israel" , 
			 "country_italy" => "Italy"  ,
			 "country_japan" => "Japan"  ,
			 "country_korea" => "Korea"  ,
			 "country_mexico" => "Mexico"  ,
			 "country_netherlands" => "Netherlands"  ,
			 "country_new_zealand" => "New Zealand"  ,
			 "country_norway" => "Norway"  ,
			 "country_peru" => "Peru"  ,
			 "country_poland" => "Poland" , 
			 "country_portugal" => "Portugal"  ,
			 "country_russia" => "Russia"  ,
			 "country_singapore" => "Singapore"  ,
			 "country_slovenia" => "Slovenia"  ,
			 "country_south_africa" => "South Africa"  ,
			 "country_spain" => "Spain"  ,
			 "country_sweden" => "Sweden"  ,
			 "country_switzerland" => "Switzerland"  ,
			 "country_turkey" => "Turkey"  ,
			 "country_united_kingdom" => "United Kingdom"  ,
			 "country_united_states" => "United States" , 
			 "country_uruguay" => "Uruguay"  ,
			 "country_venezuela" => "Venezuela"  ,
			 "title" => "SiteTranslator Demo" ,
			 "article" =>	"<h2>What Is A Fire Torch?</h2>
<p>It is simply a club which, instead of the usual bell end, has a
(usually non-asbestos) flame proof wick screwed or bolted on to a
metal sheath. The torch is lit by dipping the wick in fuel and the
fuel then burns without damaging the wick (in principle).							
<p>This article is on the use of torches: what they are, how to
use them (safely), what to use for fuel, how to make colored flames,
etc."
		),
		 	
		//German text
		"de" => array (
		 	 "country_algeria" => "Algerien"  ,
			 "country_australia" => "Australien"  ,
			 "country_austria" => "Österreich"  ,
			 "country_belgium" => "Belgien"  ,
			 "country_brazil" => "Brasilien"  ,
			 "country_canada" => "Kanada"  ,
			 "country_chile" => "Chile"  ,
			 "country_colombia" => "Kolumbien"  ,
			 "country_czech_republic" => "Tschechische Republik"  ,
			 "country_denmark" => "Dänemark"  ,
			 "country_ecuador" => "Equador"  ,
			 "country_ethiopia" => "Äthiopien"  ,
			 "country_finland" => "Finnland"  ,
			 "country_france" => "Frankreich"  ,
			 "country_germany" => "Deutschland"  ,
			 "country_greece" => "Griechenland"  ,
			 "country_hungary" => "Ungarn"  ,
			 "country_ireland" => "Irland" , 
			 "country_japan" => "Japan"  ,
			 "country_netherlands" => "Niederlande"  ,
			 "country_new_zealand" => "Neu Seeland"  ,
			 "country_norway" => "Norwegen"  ,
			 "country_singapore" => "Singapur"  ,
			 "country_south_africa" => "Südafrika"  ,
			 "country_spain" => "Spanien"  ,
			 "country_sweden" => "Schweden"  ,
			 "country_switzerland" => "Schweiz"  ,
			 "country_turkey" => "Türkei"  ,
			 "country_united_kingdom" => "Vereintes Königreich (UK)"  ,
			 "country_united_states" => "Vereinigte Staaten"  ,
			 "country_uruguay" => "Uruguay"  ,
			 "article" => "<h2>Was ist eine Fackel?</h2>
<p>Es ist eine einfache Keule, die anstelle des üblichen Bauches einen
(normalerweise Asbestfreien) unbrennbaren Docht, der an einer Metallstange
festgeschraubt ist. Die Fackel wird zum Brennenden gebracht, indem man
die Spitze in Brennflüssigkeit taucht und dann anzündet. Die
Brennflüssigkeit sollte dann (im Prinzip) verbrennen ohne den
Docht zu beschädigen.
<p>Dieser Artikel handelt vom Gebrauch von Fackeln, was sie sind,
wie man sie (sicher) benutzt, wie man farbige Flammen macht, und so weiter."
		),
		
		//Russian text
		"ru" => array (
			 "country_argentina" => "áÒÇÅÎÔÉÎÁ"  ,
			 "country_australia" => "á×ÓÔÒÁÌÉÑ"  ,
			 "country_austria" => "á×ÓÔÒÉÑ"  ,
			 "country_belgium" => "âÅÌØÇÉÑ"  ,
			 "country_brazil" => "âÒÁÚÉÌÉÑ"  ,
			 "country_canada" => "ëÁÎÁÄÁ"  ,
			 "country_chile" => "þÉÌÉ"  ,
			 "country_colombia" => "ëÏÌÕÍÂÉÑ"  ,
			 "country_czech_republic" => "þÅÛÓËÁÑ ÒÅÓÐÕÂÌÉËÁ"  ,
			 "country_denmark" => "äÁÎÉÑ"  ,
			 "country_ecuador" => "üË×ÁÄÏÒ"  ,
			 "country_ethiopia" => "üÆÉÏÐÉÑ" , 
			 "country_finland" => "æÉÎÌÑÎÄÉÑ" , 
			 "country_france" => "æÒÁÎÃÉÑ"  ,
			 "country_germany" => "çÅÒÍÁÎÉÑ"  ,
			 "country_greece" => "çÒÅÃÉÑ"  ,
			 "country_hungary" => "÷ÅÎÇÒÉÑ"  ,
			 "country_ireland" => "éÒÌÁÎÄÉÑ"  ,
			 "country_israel" => "éÚÒÁÉÌØ"  ,
			 "country_italy" => "éÔÁÌÉÑ"  ,
			 "country_japan" => "ñÐÏÎÉÑ"  ,
			 "country_netherlands" => "îÉÄÅÒÌÁÎÄÙ"  ,
			 "country_new_zealand" => "îÏ×ÁÑ úÅÌÁÎÄÉÑ"  ,
			 "country_norway" => "îÏÒ×ÅÇÉÑ"  ,
			 "country_peru" => "ðÅÒÕ"  ,
			 "country_portugal" => "ðÏÒÔÕÇÁÌÉÑ"  ,
			 "country_singapore" => "óÉÎÇÁÐÕÒ"  ,
			 "country_south_africa" => "àÖÎÁÑ áÆÒÉËÁ"  ,
			 "country_spain" => "éÓÐÁÎÉÑ"  ,
			 "country_sweden" => "û×ÅÃÉÑ"  ,
			 "country_switzerland" => "û×ÅÊÃÁÒÉÑ"  ,
			 "country_turkey" => "ôÕÒÃÉÑ"  ,
			 "country_united_kingdom" => "÷ÅÌÉËÏÂÒÉÔÁÎÉÑ"  ,
			 "country_united_states" => "óÏÅÄÉÎÅÎÎÙÅ ûÔÁÔÙ"  ,
			 "country_uruguay" => "õÒÕÇ×ÁÊ"  ,
			 "country_venezuela" => "÷ÅÎÅÓÕÜÌÁ"  
		 )
	 );


		
		
	function LocalisedTextBaseDemo($config)
	{
		$this->config = $config;
	}
	
	
	function setLocalisedText($language, $textKey, $textValue, $blnUpdateTime)
	{			
		//not supported
	}
	
	function addText($language, $key, $text)
	{
		//not supported
	}
	
	
	function getLocalisedText($textKey, $language)
	{
		$table = $this->textBase[$language];
		
		//this text does not exist
		if (!$table[$textKey])
			return false;
		
		return $table[$textKey];
	}	
	
	function getUpdateTime($textKey, $language)
	{
		//not supported
	}
	
	function getKeyList($language, $sort = "date")
	{		
		$table = $this->textBase["en"];
		
		//create the key array
		$keys = array();
		while (list ($key, $val) = each ($table))
		{
			array_push($keys, $key);
		}				
		return $keys;
	}
	
	function getWordCount($key, $language)
	{
		//assumes that the mean word length is 5 characters long
		$table = $this->textBase[$language];			
		return (int) (strlen($table[$key])/5);
	}
	
	function getLastUpdate($key, $language)
	{
		//not supported
	}
	
	function deleteText($key, $language)
	{
		//not supported
	}
}
?>