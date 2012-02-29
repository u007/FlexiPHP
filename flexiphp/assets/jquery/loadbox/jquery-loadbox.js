(function($)
{
	$.fn.loadbox = function(params)
	{
		var defaults = { loadingclass: 'loading', initialWidth: 25, initialHeight: 25, color: "black" };
    
    var options;
    
    if (typeof(params)=='string') {
      options = $.extend(defaults, {url: params} );
    } else {
      options = $.extend(defaults, params);
    }
		
    if (! options.url) {
      throw new Error("url parameter not set");
    }
    
		return this.each(function()
		{
			var obj = $(this);
      var cssclass = options.loadingclass;
      cssclass += !options.color ? "": " " + options.color;
      
      $(obj).html("<div class='" + cssclass + "' " +
        " style='width: " + options.initialWidth + "px; height: " + options.initialHeight + "px'></div>");
      $(obj).load(options.url);
		});
	}
}(jQuery));