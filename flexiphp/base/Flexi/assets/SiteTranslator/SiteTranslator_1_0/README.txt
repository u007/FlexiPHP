SiteTranslator Installation
===========================

The following README describes a two stage installation, the first 
step details how to install SiteTranslator with the demonstration data
using the LocalisedTextBaseDemo class. The resulting pages containing localised
text will not be editable, however they will verify that the SiteTranslator
classes and editing environment  have been correctly installed.

1. Place the contents of this package into a directory on your server.

2. Unzip the contents of flags.zip

3. Configure SiteTranslatorConfig.php, most of the required data
   for demonstration of SiteTRanslator is already set. You must
   configure the following:
   
 i.  Point $translationDir to the absoluterlocation of the 'translation' 
     directory on your server.
    
 ii. Point $flagHomeSmall and $flagHomeLarge to the absolute directory
     of the flag images on your server.
     
4. Fire up your browser and navigate to demo/index.php. You should be 
   able to view this page in English, German and Russian. You can also try
   logging in as an editor in order to view the interactive interface
   used to translate these pages.
   
   
Once you have got this far you will probably want to place the text in a database
so that you can perform translations.

Create a database, or use an exisiting one on your site. The file demo/demo.sql
provides a schema and some data so that you can run the same demonstration as
above but this time using a database.

1. Import the contents of demo/demo.sql into your database

2. Configure SiteTranslatorConfig.php to use LocalisedTextBaseMySQL.php, to do this
   change the 'require' at the start of the file to use this class. Secondly
   change the constructor of SiteTranslatorConfig to create an instance of 
   LocalisedTextBaseMySQL.
   
3. Configure the database parameters of SiteTranslatorConfig, these are all
   the variables starting with $mysql*.
   
4. Fire up your browser and navigate to demo/index.php. You should be 
   able to view this page in English, German and Russian. You can now login
   as an editor or translator and actually start changing the localised text.
   
That is just about it.

Any questions? 

Colin E.
webmaster@juggligndb.com
   
   
 


