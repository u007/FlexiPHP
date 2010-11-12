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
	if (!$translator->isLoggedIn() || !$translator->isEditor())
	{
		echo "<h2>Access denied</h2>";
	}
	else
	{
	
		if ($_POST["submit"])
		{		
			if ($translator->addText($_POST["key"], $_POST["text"]))
			{
				echo "<h3>The database has been updated</h3>";
				echo "You will have to refresh the menu on the left hand side to see the changes";			
			}
			else
			{
				echo "<h3>The item already exists in the database</h3>";
				showForm($_POST["key"], $_POST["text"]);
			}
		}
		else
		{
			showForm();
		}
	}
	
	function showForm($key="", $text="")
	{
		?>
		<form method="post" action="<? echo $PHP_SELF ?>">
			Key: <INPUT VALUE="<? echo $key ?>" NAME="key" SIZE=20>
			<TEXTAREA NAME="text" rows="15" cols="80"><? echo $text ?></TEXTAREA><br>
			<INPUT TYPE="SUBMIT" VALUE="Submit Details" NAME="submit">
		</form>
		<?
	}
	
		
?>

</body>
</html>

