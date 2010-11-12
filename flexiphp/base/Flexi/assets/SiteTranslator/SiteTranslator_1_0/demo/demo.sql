# $Author: colineberhardt $
# $Date: 2004/11/12 16:30:19 $
# $Revision: 1.1 $
# $Name:  $
# 
# Database dump for demo

CREATE TABLE text_de (
   textKey varchar(50) NOT NULL,
   textValue text NOT NULL,
   lastUpdate datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
   PRIMARY KEY (textKey)
);

INSERT INTO text_de VALUES( 'country_algeria', 'Algerien', '2004-11-11 17:27:44');
INSERT INTO text_de VALUES( 'country_australia', 'Australien', '2004-11-11 17:27:50');
INSERT INTO text_de VALUES( 'country_austria', '÷sterreich', '2004-11-11 17:27:55');
INSERT INTO text_de VALUES( 'country_belgium', 'Belgien', '2004-11-11 17:28:08');
INSERT INTO text_de VALUES( 'country_brazil', 'Brasilien', '2004-11-11 17:28:14');
INSERT INTO text_de VALUES( 'country_canada', 'Kanada', '2004-11-11 17:28:20');
INSERT INTO text_de VALUES( 'country_chile', 'Chile', '2004-11-11 17:28:26');
INSERT INTO text_de VALUES( 'country_colombia', 'Kolumbien', '2004-11-11 17:28:31');
INSERT INTO text_de VALUES( 'country_czech_republic', 'Tschechische Republik', '2004-11-11 17:28:38');
INSERT INTO text_de VALUES( 'country_denmark', 'D‰nemark', '2004-11-11 17:28:45');
INSERT INTO text_de VALUES( 'country_ecuador', 'Equador', '2004-11-11 17:28:51');
INSERT INTO text_de VALUES( 'country_ethiopia', 'ƒthiopien', '2004-11-11 17:29:01');
INSERT INTO text_de VALUES( 'country_germany', 'Deutschland', '2004-11-11 17:29:10');
INSERT INTO text_de VALUES( 'country_hungary', 'Ungarn', '2004-11-11 17:29:19');
INSERT INTO text_de VALUES( 'country_spain', 'Spanien', '2004-11-11 17:29:26');
INSERT INTO text_de VALUES( 'country_sweden', 'Schweden', '2004-11-11 17:29:33');
INSERT INTO text_de VALUES( 'country_united_states', '\"Vereinigte Staaten', '2004-11-11 17:29:39');
INSERT INTO text_de VALUES( 'country_united_kingdom', 'Vereintes Kˆnigreich (UK)', '2004-11-11 17:29:45');
INSERT INTO text_de VALUES( 'article', '<h2>Was ist eine Fackel?</h2>
<p>Es ist eine einfache Keule, die anstelle des ¸blichen Bauches einen
(normalerweise Asbestfreien) unbrennbaren Docht, der an einer Metallstange
festgeschraubt ist. Die Fackel wird zum Brennenden gebracht, indem man
die Spitze in Brennfl¸ssigkeit taucht und dann anz¸ndet. Die
Brennfl¸ssigkeit sollte dann (im Prinzip) verbrennen ohne den
Docht zu besch‰digen.
<p>Dieser Artikel handelt vom Gebrauch von Fackeln, was sie sind,
wie man sie (sicher) benutzt, wie man farbige Flammen macht, und so weiter.', '2004-11-11 17:30:10');
INSERT INTO text_de VALUES( 'country_france', 'Frankreich', '2004-11-11 17:31:55');


CREATE TABLE text_en (
   textKey varchar(50) NOT NULL,
   textValue text NOT NULL,
   lastUpdate datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
   PRIMARY KEY (textKey)
);


INSERT INTO text_en VALUES( 'country_algeria', 'Algeria', '2004-11-11 17:22:01');
INSERT INTO text_en VALUES( 'country_argentina', 'Argentina', '2004-11-11 17:22:25');
INSERT INTO text_en VALUES( 'country_australia', 'Australia', '2004-11-11 17:22:35');
INSERT INTO text_en VALUES( 'country_austria', 'Austria', '2004-11-11 17:22:41');
INSERT INTO text_en VALUES( 'country_belgium', 'Belgium', '2004-11-11 17:22:46');
INSERT INTO text_en VALUES( 'country_brazil', 'country_brazil', '2004-11-11 17:22:51');
INSERT INTO text_en VALUES( 'country_canada', 'Canada', '2004-11-11 17:22:57');
INSERT INTO text_en VALUES( 'country_chile', 'Chile', '2004-11-11 17:23:02');
INSERT INTO text_en VALUES( 'country_china', 'China', '2004-11-11 17:23:13');
INSERT INTO text_en VALUES( 'country_colombia', 'Colombia', '2004-11-11 17:24:01');
INSERT INTO text_en VALUES( 'country_czech_republic', 'Czech Republic', '2004-11-11 17:24:10');
INSERT INTO text_en VALUES( 'country_denmark', 'Denmark', '2004-11-11 17:24:16');
INSERT INTO text_en VALUES( 'country_ecuador', 'Ecuador', '2004-11-11 17:24:20');
INSERT INTO text_en VALUES( 'country_ethiopia', 'Ethiopia', '2004-11-11 17:24:25');
INSERT INTO text_en VALUES( 'country_finland', 'Finland', '2004-11-11 17:24:29');
INSERT INTO text_en VALUES( 'country_france', 'France', '2004-11-11 17:24:34');
INSERT INTO text_en VALUES( 'country_germany', 'Germany', '2004-11-11 17:24:38');
INSERT INTO text_en VALUES( 'country_greece', 'Greece', '2004-11-11 17:24:43');
INSERT INTO text_en VALUES( 'country_hungary', 'Hungary', '2004-11-11 17:24:49');
INSERT INTO text_en VALUES( 'country_ireland', 'Ireland', '2004-11-11 17:24:53');
INSERT INTO text_en VALUES( 'country_israel', 'Israel', '2004-11-11 17:24:59');
INSERT INTO text_en VALUES( 'country_italy', 'Italy', '2004-11-11 17:25:04');
INSERT INTO text_en VALUES( 'country_japan', 'Japan', '2004-11-11 17:25:17');
INSERT INTO text_en VALUES( 'country_korea', 'Korea', '2004-11-11 17:25:22');
INSERT INTO text_en VALUES( 'country_mexico', 'Mexico', '2004-11-11 17:25:26');
INSERT INTO text_en VALUES( 'country_netherlands', 'Netherlands', '2004-11-11 17:25:30');
INSERT INTO text_en VALUES( 'country_new_zealand', 'New Zealand', '2004-11-11 17:25:37');
INSERT INTO text_en VALUES( 'country_norway', 'Norway', '2004-11-11 17:25:42');
INSERT INTO text_en VALUES( 'country_peru', 'Peru', '2004-11-11 17:25:46');
INSERT INTO text_en VALUES( 'country_poland', 'Poland', '2004-11-11 17:25:53');
INSERT INTO text_en VALUES( 'country_portugal', 'Portugal', '2004-11-11 17:25:58');
INSERT INTO text_en VALUES( 'country_russia', 'Russia', '2004-11-11 17:26:03');
INSERT INTO text_en VALUES( 'country_singapore', 'Singapore', '2004-11-11 17:26:08');
INSERT INTO text_en VALUES( 'country_slovenia', 'Slovenia', '2004-11-11 17:26:13');
INSERT INTO text_en VALUES( 'country_south_africa', 'South Africa', '2004-11-11 17:26:18');
INSERT INTO text_en VALUES( 'country_spain', 'Spain', '2004-11-11 17:26:23');
INSERT INTO text_en VALUES( 'country_sweden', 'Sweden', '2004-11-11 17:26:28');
INSERT INTO text_en VALUES( 'country_switzerland', 'Switzerland', '2004-11-11 17:26:34');
INSERT INTO text_en VALUES( 'country_turkey', 'Turkey', '2004-11-11 17:26:40');
INSERT INTO text_en VALUES( 'country_united_kingdom', 'United Kingdom', '2004-11-11 17:26:46');
INSERT INTO text_en VALUES( 'country_united_states', 'United States', '2004-11-11 17:26:52');
INSERT INTO text_en VALUES( 'country_uruguay', 'Uruguay', '2004-11-11 17:26:57');
INSERT INTO text_en VALUES( 'country_venezuela', 'Venezuela', '2004-11-11 17:27:02');
INSERT INTO text_en VALUES( 'title', 'SiteTranslator Demo', '2004-11-11 17:27:08');
INSERT INTO text_en VALUES( 'article', '<h2>What Is A Fire Torch?</h2>
<p>It is simply a club which, instead of the usual bell end, has a
(usually non-asbestos) flame proof wick screwed or bolted on to a
metal sheath. The torch is lit by dipping the wick in fuel and the
fuel then burns without damaging the wick (in principle).							
<p>This article is on the use of torches: what they are, how to
use them (safely), what to use for fuel, how to make colored flames,
etc.', '2004-11-11 17:27:30');


CREATE TABLE text_ru (
   textKey varchar(50) NOT NULL,
   textValue text NOT NULL,
   lastUpdate datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
   PRIMARY KEY (textKey)
);


INSERT INTO text_ru VALUES( 'country_argentina', '·“«≈Œ‘…Œ¡', '2004-11-11 17:30:21');
INSERT INTO text_ru VALUES( 'country_australia', '·“«≈Œ‘…Œ¡', '2004-11-11 17:30:43');
INSERT INTO text_ru VALUES( 'country_canada', 'Î¡Œ¡ƒ¡', '2004-11-11 17:30:50');
INSERT INTO text_ru VALUES( 'country_chile', '˛…Ã…', '2004-11-11 17:31:04');
INSERT INTO text_ru VALUES( 'country_denmark', '˛≈€”À¡— “≈”–’¬Ã…À¡', '2004-11-11 17:31:29');
INSERT INTO text_ru VALUES( 'country_ecuador', '¸À◊¡ƒœ“', '2004-11-11 17:31:41');
INSERT INTO text_ru VALUES( 'country_france', 'Ê“¡Œ√…—', '2004-11-11 17:31:55');
INSERT INTO text_ru VALUES( 'country_germany', '', '2004-11-11 17:32:09');
INSERT INTO text_ru VALUES( 'country_greece', 'Á“≈√…—', '2004-11-11 17:32:18');
INSERT INTO text_ru VALUES( 'country_italy', 'È‘¡Ã…—', '2004-11-11 17:32:31');
INSERT INTO text_ru VALUES( 'country_japan', 'Ò–œŒ…—', '2004-11-11 17:32:39');
INSERT INTO text_ru VALUES( 'country_norway', 'Óœ“◊≈«…—', '2004-11-11 17:32:58');
INSERT INTO text_ru VALUES( 'country_peru', '≈“’', '2004-11-11 17:33:12');
INSERT INTO text_ru VALUES( 'country_portugal', 'œ“‘’«¡Ã…—', '2004-11-11 17:33:30');
INSERT INTO text_ru VALUES( 'country_sweden', '˚◊≈√…—', '2004-11-11 17:33:50');
INSERT INTO text_ru VALUES( 'country_turkey', 'Ù’“√…—', '2004-11-11 17:34:01');
INSERT INTO text_ru VALUES( 'country_united_kingdom', '˜≈Ã…Àœ¬“…‘¡Œ…—', '2004-11-11 17:34:08');
INSERT INTO text_ru VALUES( 'country_united_states', 'Ûœ≈ƒ…Œ≈ŒŒŸ≈ ˚‘¡‘Ÿ', '2004-11-11 17:34:15');

