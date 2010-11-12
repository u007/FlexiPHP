<?


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=8" >
	<title><?=$vars["title"]?></title>
	<link href="<?=FlexiConfig::$sBaseURL?>/flexiphp/assets/templates/<?=FlexiConfig::$sTemplateDir?>/css/reset.css" media="all" rel="stylesheet" type="text/css" >
	<link href="<?=FlexiConfig::$sBaseURL?>/flexiphp/assets/templates/<?=FlexiConfig::$sTemplateDir?>/css/style.css" media="all" rel="stylesheet" type="text/css" >
	<link href="<?=FlexiConfig::$sBaseURL?>/flexiphp/assets/templates/<?=FlexiConfig::$sTemplateDir?>/css/print.css" media="print" rel="stylesheet" type="text/css" >
	<script type="text/javascript" src="<?=FlexiConfig::$sBaseURL?>flexiphp/assets/templates/<?=FlexiConfig::$sTemplateDir?>/js/jquery-1.4.2.min.js"></script>
	<script type="text/javascript" src="<?=FlexiConfig::$sBaseURL?>flexiphp/assets/templates/<?=FlexiConfig::$sTemplateDir?>/js/jquery-ui-1.8.1.custom.min.js"></script>
	<script type="text/javascript" src="<?=FlexiConfig::$sBaseURL?>flexiphp/assets/templates/<?=FlexiConfig::$sTemplateDir?>/js/jquery-ui-personalized-1.6rc2.min.js"></script>
	<script type="text/javascript" src="<?=FlexiConfig::$sBaseURL?>flexiphp/assets/templates/<?=FlexiConfig::$sTemplateDir?>/js/front.init.js"></script>
	
	<?=isset($vars["header"]) ? $vars["header"] : ""; ?>
</head>

<body>

<div class="mainContent">
	<div class="topheader">
		<div class="toplogo">
			<a class="homelink" href="<?=$this->url(null, "default", "default");?>"></a>
		</div>
		<div class="topbar">
			<div class="topleft">
				<p><strong>Welcome!</strong></p>
				<p>0001 Member name User Name</p>
				<p><a href="#">Log Out</a></p>
			</div>
			<div class="topright">
				<?=$this->render("topweather") ?>
			</div>
		</div>
		<div class="toppanel">
			<div style="float: left;">
				<a href="#"><img src="<?=FlexiConfig::$sBaseURL?>/flexiphp/assets/templates/<?=FlexiConfig::$sTemplateDir?>/images/but-addwidget.png"></a>
			</div>
			<div style="float: right;">
				<a href="#"><img src="<?=FlexiConfig::$sBaseURL?>/flexiphp/assets/templates/<?=FlexiConfig::$sTemplateDir?>/images/but-resetwidget.png"></a>
			</div>
		</div>
	</div>
	
	<div class="contentBody">
		<?=$vars["notice"]; ?>
		<?=isset($vars["body"]) ? $vars["body"] : ""; ?> 
	</div>
	
	<div class="contentFooter">
		<div class="footer">
			<?=isset($vars["footer"]) ? $vars["footer"] : ""; ?> 
		</div>
	</div>
	
</div>

</body>

</html>
