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

class LocalisedTextBaseTest extends LocalisedTextBase
{	
	var	$database = array(
			"en"=>array	(
				"menu_club_country"	=>	array("View by Country",50),
				"menu_clubs"		=>	array("Clubs",50),
				"menu_feet"			=>	array("Clubs",80)
						),
			"da"=>array (
				"menu_clubs"		=>	array("Jongleringsklubber", 60),
				"menu_feet"			=>	array("Jongleringsklubber", 60)
						)
			);
	
	function LocalisedTextBaseTest()
	{		
	}

	function setLocalisedText($language, $textKey, $textValue)
	{			
		$this->database[$language][$textKey][0] = $textValue;				
	}
	
	function getLocalisedText($textKey, $language)
	{
		$table = $this->database[$language];
		if ($table[$textKey])
		{
			$row = $table[$textKey];
			return $row [0];
		}
		else
			return false;				
	}	
	
	function getUpdateTime($textKey, $language)
	{
		$table = $this->database[$language];
		if ($table[$textKey])
		{
			$row = $table[$textKey];
			return $row [1];
		}
		return 0;			
	}	
	
	function getKeyList($language)
	{
		$table = $this->database[$language];		
		$keys = array();
		while (list ($key, $val) = each ($table))
			array_push($keys, $key);
			
		return $keys;
	}
}

?>