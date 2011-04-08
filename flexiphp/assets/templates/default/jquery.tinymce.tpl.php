<?php

$sExternalLink = !empty($vars["tinymce_link"]) ? $vars["tinymce_link"]: FlexiConfig::$sTinyMCEExternalLink;
$sExternalImage = !empty($vars["tinymce_img"]) ? $vars["tinymce_img"]: FlexiConfig::$sTinyMCEExternalImage;
$sExternalMedia = !empty($vars["tinymce_media"]) ? $vars["tinymce_media"]: FlexiConfig::$sTinyMCEExternalMedia;

$sContentCSS = !empty($vars["tinymce_contentcss"]) ? $vars["tinymce_contentcss"]: FlexiConfig::$sTinyMCEContentCSS;

?>
<script type="text/javascript" src="<?=FlexiConfig::$sFlexiBaseURL ?>assets/jquery/tiny_mce/jquery.tinymce.js"></script>
<script type="text/javascript">
  var oTinyMCESetting = {
    // Location of TinyMCE script
    script_url : '<?=FlexiConfig::$sFlexiBaseURL ?>assets/jquery/tiny_mce/tiny_mce.js',
    document_base_url       : '<?=FlexiConfig::$sBaseURLDir?>',
    relative_urls           : true,
    // General options
    theme : "advanced",
    plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",

    // Theme options: save,
    theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
    theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
    theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
    theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "left",
    theme_advanced_statusbar_location : "bottom",
    theme_advanced_resizing : true,

    // Example content CSS (should be your site CSS)
    <? if (!empty($sContentCSS)) { ?>
    content_css : "<?=$sContentCSS?>",
    <? } ?>
    // Drop lists for link/image/media/template dialogs
    template_external_list_url : "lists/template_list.js",
    //external_link_list_url : "lists/link_list.js",
    external_image_list_url : "<?=$sExternalImage?>",
    media_external_list_url : "<?=$sExternalMedia?>",

    external_link_list_url  : "<?=$sExternalLink?>",
    // Replace values for the template plugin
    template_replace_values : {
      username : "Some User",
      staffid : "991234"
    }
  }

  var oTinyMCEBasicSetting = cloneObject(oTinyMCESetting);
  oTinyMCEBasicSetting.theme_advanced_buttons1 = "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect";
  oTinyMCEBasicSetting.theme_advanced_buttons2 = "bullist,numlist,|,outdent,undo,redo,|,link,unlink,anchor,preview,forecolor,fullscreen";
  oTinyMCEBasicSetting.theme_advanced_buttons3 = "";
  oTinyMCEBasicSetting.theme_advanced_buttons4 = "";

  var oTinyMCEAdvanceSetting = cloneObject(oTinyMCEBasicSetting);
  oTinyMCEAdvanceSetting.theme_advanced_buttons2 += ",|,cut,copy,paste,pastetext,pasteword,|,search,replace,image,cleanup,help,code,|,insertdate,inserttime";
  oTinyMCEAdvanceSetting.theme_advanced_buttons3 = "backcolor,tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,iespell,media,advhr,|,print,|,ltr,rtl";
  oTinyMCEAdvanceSetting.theme_advanced_buttons4 = "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak";
  
	jQuery().ready(function() {
		initializeTinyMCE();
	});

  function initializeTinyMCE(target) {

    if (target){
      jQuery(target + ' textarea.mceEditor').tinymce(oTinyMCESetting);
      jQuery(target + ' textarea.basicMCEEditor').tinymce(oTinyMCEBasicSetting);
      jQuery(target + ' textarea.advanceMCEEditor').tinymce(oTinyMCEAdvanceSetting);
    } else {
      jQuery('textarea.mceEditor').tinymce(oTinyMCESetting);
      jQuery('textarea.basicMCEEditor').tinymce(oTinyMCEBasicSetting);
      jQuery('textarea.advanceMCEEditor').tinymce(oTinyMCEAdvanceSetting);
    }
    //full
    
  }
</script>