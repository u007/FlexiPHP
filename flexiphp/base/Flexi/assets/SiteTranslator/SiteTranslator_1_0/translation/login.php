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
	
	$blnShowForm = true;
	
	if ($_POST["submit"])
	{
		if ($translator->login($_POST["username"], $_POST["password"]))
		{
			$blnShowForm = false;	
			
			//redirect to the specified page.
			if ($_POST["redir"])
				header("Location: ".$_POST["redir"]);
			else
				header("Location: index.php");
		}
		else
			$strAdditionalInfo = "Login failed - go away.";
	}
	
	if ($blnShowForm)
	{
		?>
		<HTML>
		<BODY>
			<? echo $strAdditionalInfo ?>
			<H3>Web Site Translator Login:</H3>
		
			<TABLE>
			<FORM ACTION="login.php" METHOD=post>
				<INPUT NAME="redir" VALUE="<? echo $_GET["redir"]; ?>" TYPE="HIDDEN">
				<TR>
					<TD> Name :</TD>
					<TD><INPUT NAME="username" STYLE="HEIGHT: 22px; WIDTH: 250px" TYPE="text"></TD>
				</TR>
				<TR>
					<TD> Password :</TD>
					<TD><INPUT NAME="password" STYLE="HEIGHT: 22px; WIDTH: 250px" TYPE="password"></TD>
				</TR>
				<TR align=right>
					<TD COLSPAN=2><INPUT TYPE="SUBMIT" VALUE="Login" NAME="submit"></TD>
				</TR>
			</FORM>
			</TABLE>
		</BODY>
		</HTML>
		<?
	}	
?>

