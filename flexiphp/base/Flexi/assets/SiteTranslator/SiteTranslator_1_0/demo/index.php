<?

/*
$Author: colineberhardt $
$Date: 2004/11/16 10:27:00 $
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


  //create an instance of SiteTranslator, the class keeps track of the user's language
  //preferences. Also it will handle language changes via the configurable GET variable
  require ("../SiteTranslator.php");
  $translator = new SiteTranslator();
?>
<html lang="<? echo $translator->getLanguage(); ?>">
<head>
  <meta http-equiv="content-type" content="text/html; charset=<? echo $translator->getCharSet(); ?>">
<title>
<?
  //Output the localised title for this page, supressing the translation
  //HTML tag, because the title cannot contain HTML!
  echo $translator->getLocalisedText("title", true);
?>
</title>
</head>
<body>

<h2>SiteTranslator Demonstration</h2>
<p style="background:#dddddd">The following is a simple (and ugly) demonstration of some of the features of
SiteTranslator. The localised text data is stored in an array within the class
LocalisedTextBaseDemo. This class does not support the editing functions found in
LocalisedTextBaseMySQL, however you can login as an editor or translator to view the interface. The usernames
and passwords are as follows:
<ul>
<li><b>German translator:</b>  trans_de -> de_pass
<li><b>Russian translator:</b> trans_ru -> ru_pass
<li><b>Website editor:</b> editor -> editor_pass
</ul>



<?

  //if the visitor to the page is logged in as a translator, provide a link to the logout page
  if ($translator->isLoggedIn())
    echo '<p><a href="../translation/logout.php">Log out</a>';
  else
    //otherwise provide a login link
  	echo 	'<p><a href="../translation/login.php?redir=http://'.$_SERVER["SERVER_NAME"].$_SERVER["PHP_SELF"].'">'.
  			'Log in</a> to see the translator/editor view of this site'.
  			'<br>';
  	
    
    
    
  //output the different languages which this site is available in. The flags provide hyperlinks
  //which allow the visitor to select their preferred language.
  echo "<p><b style=\"background:#dddddd\">Select the desired language by clicking on the following flags:</b><br>";
  echo $translator->showFlags("", false, $_SERVER["PHP_SELF"]);
  
  //output some text which can be localised
  echo "<p><b style=\"background:#dddddd\">A list of countries:</b><br>";
  echo $translator->getLocalisedText("country_argentina")."<br>";
  echo $translator->getLocalisedText("country_australia")."<br>";
  echo $translator->getLocalisedText("country_austria")."<br>";
  echo $translator->getLocalisedText("country_canada")."<br>";
  echo $translator->getLocalisedText("country_chile")."<br>";
  echo $translator->getLocalisedText("country_china" )."<br>";
  echo $translator->getLocalisedText("country_denmark")."<br>";
  echo $translator->getLocalisedText("country_ecuador")."<br>";
  echo $translator->getLocalisedText("country_france")."<br>";
  echo $translator->getLocalisedText("country_germany" )."<br>";
  echo $translator->getLocalisedText("country_greece")."<br>";
  echo $translator->getLocalisedText("country_italy")."<br>";
  echo $translator->getLocalisedText("country_japan")."<br>";
  echo $translator->getLocalisedText("country_korea")."<br>";
  echo $translator->getLocalisedText("country_mexico" )."<br>";  
  echo $translator->getLocalisedText("country_norway")."<br>";
  echo $translator->getLocalisedText("country_peru")."<br>";
  echo $translator->getLocalisedText("country_poland")."<br>";
  echo $translator->getLocalisedText("country_portugal" )."<br>";
  echo $translator->getLocalisedText("country_russia")."<br>";
  echo $translator->getLocalisedText("country_sweden")."<br>";
  echo $translator->getLocalisedText("country_switzerland")."<br>";
  echo $translator->getLocalisedText("country_turkey")."<br>";
  echo $translator->getLocalisedText("country_united_kingdom")."<br>";
  echo $translator->getLocalisedText("country_united_states" )."<br>";
  echo $translator->getLocalisedText("country_venezuela")."<br>";
  
  //output some more text which can be localised
  echo "<p><b style=\"background:#dddddd\">A larger block of text [note this has not yet been translated into Russian]:</b>";
  echo $translator->getLocalisedText("article");
  
?>
</body>
</html>

