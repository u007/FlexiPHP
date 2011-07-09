
/* slideShow ~ James - Version 1.0 - MIT/GPL 
 * Requires jQuery + jQuery UI
 */
(function($)
{
	$.fn.slideShow = function(options)
	{
    var defaults = { delay:4000, showdelay: 1000, "ease": "blind" };
		var options = $.extend(defaults, options);
    
    $("li", $(this)).hide(0);
    return this.each(function()
		{
			var obj = $(this);
      var aList = $("li", obj);
      var iIndex = 0;
      
      obj.css("list-style-type", "none");
      obj.css("position", "relative");
      aList.css("position", "absolute");
      aList.hide(0);
      if (aList.length <= 0) return;
      
      $(aList[0]).show(options.ease, [], options.showdelay);
     
      var doSlide = function() {
          iIndex = iIndex+1 < aList.length ? iIndex+1: 0;
          if (iIndex-1 >= 0) $(aList[iIndex-1]).fadeOut(options.showdelay);
            else $(aList[aList.length-1]).fadeOut(options.showdelay);
          $(aList[iIndex]).show(options.ease, [], options.showdelay, function() {
            setTimeout(doSlide, options.delay);
          });
      };
    
      setTimeout(doSlide, options.delay);
		});
	}
}(jQuery));