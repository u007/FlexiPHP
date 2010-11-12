<script language="javascript" type="text/javascript" src="<?=FlexiConfig::$sBaseURL?>/assets/plugins/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript" src="<?=FlexiConfig::$sBaseURL?>/assets/plugins/tinymce/js/xconfig.js"></script>
<script language="javascript" type="text/javascript">
tinyMCE.init({
	theme                            : 'advanced',
	mode                             : 'textareas',
	/* elements                         : 'ta', */
	editor_selector 								 : "mceEditor",
	language                         : 'en',
	document_base_url                : '<?=FlexiConfig::$sBaseURL?>',
	relative_urls                    : true,
	remove_script_host               : true,
	convert_urls                     : true,
	forced_root_block                : 'p',
	force_p_newlines                 : true,
	valid_elements                   : mce_valid_elements,
	extended_valid_elements          : mce_extended_valid_elements,
	invalid_elements                 : mce_invalid_elements,
	popup_css_add                    : '/assets/plugins/tinymce/style/popup_add.css',
	accessibility_warnings : false,
	theme_advanced_toolbar_location  : 'top',
	theme_advanced_statusbar_location: 'bottom',
	theme_advanced_toolbar_align     : 'ltr',
	theme_advanced_font_sizes        : '80%,90%,100%,120%,140%,160%,180%,220%,260%,320%,400%,500%,700%',
	content_css                      : '/assets/plugins/tinymce/style/content.css',
	formats : {
		alignleft   : {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes : 'justifyleft'},
		alignright  : {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes : 'justifyright'},
		alignfull   : {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes : 'justifyfull'},
	},
	apply_source_formatting          : true,
	remove_linebreaks                : false,
	convert_fonts_to_spans           : true,
	plugins                          : 'save,advlist,clearfloat,style,fullscreen,advimage,paste,advlink,media,contextmenu,table',
	theme_advanced_buttons1          : 'undo,redo,|,bold,forecolor,backcolor,strikethrough,formatselect,fontsizeselect,pastetext,pasteword,code,|,fullscreen,help',
	theme_advanced_buttons2          : 'image,media,link,unlink,anchor,|,justifyleft,justifycenter,justifyright,clearfloat,|,bullist,numlist,|,blockquote,outdent,indent,|,table,hr,|,styleprops,removeformat',
	theme_advanced_buttons3          : '',
	theme_advanced_buttons4          : '',
	theme_advanced_resize_horizontal :  false,
	external_link_list_url           : '/assets/plugins/tinymce/inc/tinymce.linklist.php',
	theme_advanced_blockformats      : 'p,h2,h3,h4,h5,h6,div,blockquote,code,pre',
	theme_advanced_styles            : '',
	theme_advanced_disable           : '',
	theme_advanced_resizing          : true,
	fullscreen_settings : {
		theme_advanced_buttons1_add_before : 'save'
	},
	plugin_insertdate_dateFormat     : '%d-%m-%Y',
	plugin_insertdate_timeFormat     : '%H:%M:%S',
	entity_encoding                  : 'named',
	file_browser_callback            : 'modx_fb',
	onchange_callback                : false,
	
})
</script>

<script language="javascript" type="text/javascript">
function modx_fb (field_name, url, type, win) {
    if (type == "media") {type = win.document.getElementById("media_type").value;}
	var cmsURL = "/manager/media/browser/mcpuk/browser.php?Connector=/manager/media/browser/mcpuk/connectors/php/connector.php&ServerPath=/&editor=tinymce&editorpath=<?=FlexiConfig::$sBaseURL?>/assets/plugins/tinymce/";
	switch (type) {
		case "image":
			type = "images";
			break;
		case "media":
		case "qt":
		case "wmp":
		case "rmp":
			type = "media";
			break;
		case "shockwave":
		case "flash":
			type = "flash";
			break;
		case "file":
			type = "files";
			break;
		default:
			return false;
	}
	if (cmsURL.indexOf("?") < 0) {
	    //add the type as the only query parameter
	    cmsURL = cmsURL + "?type=" + type;
	}
	else {
	    //add the type as an additional query parameter
	    // (PHP session ID is now included if there is one at all)
	    cmsURL = cmsURL + "&type=" + type;
	}

	var windowManager = tinyMCE.activeEditor.windowManager.open({
	    file : cmsURL,
	    width : screen.width * 0.7,  // Your dimensions may differ - toy around with them!
	    height : screen.height * 0.7,
	    resizable : "yes",
	    inline : 0,  // This parameter only has an effect if you use the inlinepopups plugin!
	    close_previous : "no"
	}, {
	    window : win,
	    input : field_name
	});
	if (window.focus) {windowManager.focus()}
	return false;
}
</script>
