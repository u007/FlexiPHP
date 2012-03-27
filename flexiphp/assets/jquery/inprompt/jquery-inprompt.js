(function($)
{
  $.fn.inPromptFunc = {};
  $.fn.inPromptOption = {};
	$.fn.inPrompt = function(msg, params)
	{
    if (!$.fn.inPromptCnt) $.fn.inPromptCnt = 1;
    else {
      $.fn.inPromptCnt++;
    }
		var defaults = { buttons: [], submit: null, ready: null };
    
    var options;
    options = $.extend(defaults, params);
    
    $.fn.inPromptOption = options;
    
		return this.each(function()
		{
			var obj = $(this);
      var html = "";
      
      html = "<div class='jqinprompt'>"; 
      html += "<div class='jqinnprompt_message'>" + 
        msg + "</div>";
      
      if (options.buttons.length > 0) {
        html += "<div class='jqinpromptbuttons'>";
        
        $.fn.inPromptFunc = {};//reset functions
        for(var c=0; c < options.buttons.length; c++) {
          var oButton = options.buttons[c];
          html += "<button type='button' ";
          
          html += " onClick='jQuery.fn.inPromptFunc.btn" + c + "(" + c + ");'";
          
          $.fn.inPromptFunc["btn"+c] = function(c) {
            var oButton = $.fn.inPromptOption["buttons"][c];
            options.submit(oButton.value);
            if (oButton.run) oButton.run();
            if (oButton.link) document.location.href= oButton.link;
          }
          
          html += ">" + oButton.title + "</button>";
        }
          
        html += "</div>";
      }
      
      html += "</div>";
      $(obj).html(html);
      if (options.ready) {
        options.ready(this);
      }
		});
	}
}(jQuery));