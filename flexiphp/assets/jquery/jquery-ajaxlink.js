(function($)
{
  //target (load to)
	$.fn.ajaxlink = function(options)
	{
		var defaults = { urlattribute: "href" };
		var options = $.extend(defaults, options);
		
		return this.each(function()
		{
			var obj = $(this);
			var sLink = obj.attr(options.urlattribute);
      if (sLink=="") return;
      if (sLink.substring(0,11) != "javascript:") {
        obj.attr(options.urlattribute, "javascript:");
        obj.click(function() {
          $(options.target).load(sLink);
        });
      }
		});
	}
}(jQuery))