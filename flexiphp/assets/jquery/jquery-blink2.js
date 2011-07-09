(function($)
{
	$.fn.blink2 = function(options)
	{
		var defaults = { delay:500 };
		var options = $.extend(defaults, options);
		
		return this.each(function()
		{
			var obj = $(this);
			setInterval(function()
			{
				if($(obj).css("opacity") == "1")
				{
					$(obj).css('-moz-opacity','0.5');
          $(obj).css('opacity', '.50');
          $(obj).css('filter', 'alpha(opacity=50)');
				}
				else
				{
					$(obj).css('-moz-opacity','1');
          $(obj).css('opacity', '1');
          $(obj).css('filter', 'alpha(opacity=100)');
				}
			}, options.delay);
		});
	}
}(jQuery))