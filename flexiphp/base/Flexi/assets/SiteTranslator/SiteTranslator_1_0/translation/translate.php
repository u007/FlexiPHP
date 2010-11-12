<?
/*
$Author: colineberhardt $
$Date: 2004/11/12 16:30:20 $
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

	require("../SiteTranslatorAdmin.php");
	$translator = new SiteTranslatorAdmin();
?>

<html>
<body>
<?
	if (!$translator->isLoggedIn())
	{
		echo "<h2>Access denied</h2>";
	}
	else
	{
	
		if ($_POST["submit"])
		{		
			if ($translator->isEditor())
			{
				if ($_POST["delete"]=="CHECKED")
				{
					$translator->deleteText($_POST["key"]);
				}
				else
				{
					$languages = $translator->getTranslatorLanguages().",".$translator->getDefaultLanguage();
					
					//update the localised text for each language that this editor manages
					$language = strtok ($languages,",");
					while($language)
					{
						//does the editor wish to update this text?
						if ($_POST[$language."_update_text"]=="CHECKED")
						{
							//do they wish to update the timestamp for this record
							$blnUpdateTime = ($_POST[$language."_update_date"]=="CHECKED");
							$translator->setLocalisedText($language, $_POST["key"], $_POST[$language."_text"], $blnUpdateTime);
						}
						$language = strtok (",");
					}
				}
			}
			else
			{
				//update the localised text for each language that this editor manages
				$language = strtok ($translator->getTranslatorLanguages(),",");
				while($language)
				{
					$translator->setLocalisedText($language, $_POST["key"], $_POST[$language."_text"]);
					$language = strtok (",");
				}
			}
				
			echo "<h3>The database has been updated</h3>";
			echo "You will have to refresh the menu on the left hand side to see the changes";
		
		}
		else
		{
			?>
			<form method="post" action="<? echo $PHP_SELF ?>">
			<INPUT VALUE="<? echo $_GET["key"] ?>" READONLY TYPE="HIDDEN" NAME="key" SIZE=10>
			<?
								
				if ($translator->isEditor())
				{
					echo '<INPUT TYPE="CHECKBOX" VALUE="CHECKED" NAME="delete"><b>Delete this text entry</b></INPUT>';	
					
					//show the default text
					ShowTextArea($_GET["key"], $translator->getDefaultLanguage(), false, true);
					
					//show an edit box for each of the languages that this editor can translate
					$language = strtok ($translator->getTranslatorLanguages(),",");
					while($language)
					{
						ShowTextArea($_GET["key"], $language, false, true);
						$language = strtok (",");
					}	
				}
				else
				{
					//show the default text (read only)
					ShowTextArea($_GET["key"], $translator->getDefaultLanguage(), true);
					
					//show an edit box for each of the languages that this editor can translate
					$language = strtok ($translator->getTranslatorLanguages(),",");
					while($language)
					{
						ShowTextArea($_GET["key"], $language);
						$language = strtok (",");
					}	
				}
				
			?>
			<p>
			<INPUT TYPE="SUBMIT" VALUE="Submit Details" NAME="submit">
			</form>
			<?
		}
	}
	
	
	function ShowTextArea($key, $language, $blnReadOnly=false, $blnEditor=false)
	{
		global $translator;		
				
		//if the text requires an update - highlight it	
		if ($translator->isTranslated($key, $language) && !$translator->isUpToDate($key, $language))
			$strUpdateText = '<font color="red">(requires updating)</font>';
		
		//output the heading
		$languageName = $translator->getLanguageName($language);	
		echo "<br><br><b>$languageName: $strUpdateText</b><br><br>";								
	
		//output the text area
		if ($blnReadOnly)
			$strReadOnly = "READONLY";
		$strText = $translator->getLocalisedText($language, $key);
		echo "<TEXTAREA $strReadOnly NAME=\"".$language."_text\" rows=\"15\" cols=\"80\">$strText</TEXTAREA>";
		
		if ($blnEditor)
		{
			echo "<BR><INPUT TYPE=\"CHECKBOX\" VALUE=\"CHECKED\" NAME=\"".$language."_update_text\">Update $languageName text</INPUT>";		
			echo '<BR><INPUT TYPE="CHECKBOX" VALUE="CHECKED" NAME="'.$language.'_update_date">Update the timestamp for this text</INPUT>';		
		}
	}
	
?>

</body>
</html>

