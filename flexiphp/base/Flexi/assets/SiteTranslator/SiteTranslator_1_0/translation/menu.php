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
	<head>
	<style type="text/css"> 
		<!--  
		a.small { font-size: 8pt;text-decoration: none; } 
		td { font-size: 8pt;background-color: #ffffff; }
		 --> 
	</style>
	</head>	
<body>
<?	

	echo '<a target="_top" href="logout.php">Log out</a><p>';

	if (!$translator->isLoggedIn())
	{
		echo "<h2>Access denied</h2>";
	}
	else
	{
		
		$translationStatus = $translator->getTranslationStatus($_GET["sort"]);	
		$listTranslated = $translationStatus[0];
		$listNotTranslated = $translationStatus[1];
		$listNotUpToDate = $translationStatus[2];
		
		if ($translator->isEditor())
		{
			echo '<a target="edit" href="addnew.php">Add new item</a><p>';
		}
		
		echo '<p>Order by <a href="menu.php?sort=name">name</a>, <a href="menu.php?sort=words">word count</a>'.
			' or <a href="menu.php?sort=date">date</a>';
		
		echo '<p><table cellpadding="2" cellspacing="1" bgcolor="#333333">';
			outputHeading("Text which requires updates");
			outputKeyList($listNotUpToDate);
			
			outputHeading("Text which is not translated");
			outputKeyList($listNotTranslated);
			
			outputHeading("Text which is translated");
			outputKeyList($listTranslated);
		echo '</table>';
	}
			

	function outputKeyList($keyList)
	{
		global $translator;
		
		while (list ($key, $val) = each ($keyList))
		{
			echo '<tr>';
				echo '<td>';		
					echo formatDate($val[2]);			
				echo '</td>';
				echo '<td align="middle">';		
					echo $val[1];			
				echo '</td>';
				echo '<td>';		
					echo $translator->showFlags($val[0]);			
				echo '</td>';
				echo '<td>';		
					echo '<a class="small" target="edit" href="translate.php?key='.$key.'">'.$key.'</a><br>';						
				echo '</td>';
			echo '</tr>';
		}
	}	
	
	function outputHeading($strHeading)
	{
		echo '<tr>';
			echo '<td colspan="4">';		
				echo "<h3>$strHeading</h3>";			
			echo '</td>';
		echo '</tr>';
		echo '<tr>';
			echo '<td>';		
				echo "<b>date</b>";			
			echo '</td>';
			echo '<td>';		
				echo "<b>words</b>";			
			echo '</td>';
			echo '<td>';		
				echo "<b>languages</b>";			
			echo '</td>';
			echo '<td>';		
				echo "<b>text key</b>";					
			echo '</td>';
		echo '</tr>';
	}
	
	function formatDate($strDate)
	{
		$time = strtotime ($strDate);
		$strReturn = date("d/m/y",$time);
		return $strReturn;
	}
?>


</body>
</html>