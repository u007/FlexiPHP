(function($)
{
	$.fn.blinkcolor = function(options)
	{
		var defaults = { delay:500, color: 'rgb(255, 0, 0)', altcolor: 'rgb(255, 150, 150)' };
		var options = $.extend(defaults, options);
		
		return this.each(function()
		{
			var obj = $(this);
			setInterval(function()
			{
				if($(obj).css("color") == options.color )
				{
					$(obj).css('color',options.altcolor);
				}
				else
				{
					$(obj).css('color',options.color);
				}
			}, options.delay);
		});
	},
  $.fn.comparecolor = function(color1, color2) {
    varR = parseInt(this.substring(1,3), 16);
    varG = parseInt(this.substring(3,5), 16);
    varB = parseInt(this.substring(5,7), 16);
    return "rgb(" + varR + ", " + varG + ", " +  varB + ")";
  }
}(jQuery))