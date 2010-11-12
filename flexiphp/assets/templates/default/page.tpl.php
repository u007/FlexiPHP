<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=8" >
	<title><?=$vars["title"]?></title>
	<link href="<?=FlexiConfig::$sBaseURL?>/flexiphp/assets/templates/<?=FlexiConfig::$sTemplateDir?>/css/style.css" media="screen" rel="stylesheet" type="text/css" >
	<?=isset($vars["header"]) ? $vars["header"] : ""; ?>
</head>

<body>
	<?=$vars["notice"]; ?>
	<?=isset($vars["body"]) ? $vars["body"] : ""; ?> 
	<div class="footer">
	<?=isset($vars["footer"]) ? $vars["footer"] : ""; ?> 
	</div>
</body>

</html>