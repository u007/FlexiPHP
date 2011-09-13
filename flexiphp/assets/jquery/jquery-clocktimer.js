(function($)
{
  $.aClockTimer = [];
  
  $.fn.secondsToDHMS = function(seconds) {
    var iTime = seconds;
    var iDay = 60*60*24;
    var iHour = 60*60;
    var iMin = 60;
    
    aResult = { day: 0, hour: 0, minute: 0, second: 0 };
    if (iTime > iDay) {
      aResult.day = Math.round(Math.floor(iTime / iDay));
      iTime -= iDay * aResult.day;
    }
    
    if (iTime > iHour) {
      aResult.hour = Math.round(Math.floor(iTime / iHour));
      iTime -= iHour * aResult.hour;
    }
    
    if (iTime > iMin) {
      aResult.minute = Math.round(Math.floor(iTime / iMin));
      iTime -= iMin * aResult.minute;
    }
    
    aResult.second = Math.round(Math.floor(iTime));
    return aResult;
  };
  
  $.fn.hasClockTimer = function(timername) {
    for(var c=0; c < $.aClockTimer.length; c++) {
      if ($.aClockTimer[c].name == timername) {
        return true;
      }
    }
    return false;
  };
  //{duration: #milliseconds, onEnd: function, onUpdate: function, freq: milliseconds }
  $.fn.stopClocktimer = function(timername) {
    for(var c=0; c < $.aClockTimer.length; c++) {
      if ($.aClockTimer[c].name == timername) {
        $.aClockTimer.splice (c,1);
        break;
      }
    }
  };
  
  $.fn.stopAllClocktimer = function() {
    $.aClockTimer = [];
  };
  
	$.fn.clocktimer = function(options)
	{
    var iNow = (new Date().getTime());
		var defaults = {name: "timer" + (iNow) + "_" + Math.random(), freq: 1000, start: iNow, ended: false };
		var options = $.extend(defaults, options);
    var iIndex = $.aClockTimer.length;
    
    $.aClockTimer[iIndex] = { name: options.name, update: function() {
        if (! $().hasClockTimer(options.name)) return false;
        var iNow = (new Date().getTime());
        var iRanDuration = iNow-options.start;
        var iBalanceTime = (options.duration*1000) - iRanDuration;
        
        aTimer = $().secondsToDHMS(iBalanceTime/1000);
        if (options.onUpdate) {
          options.onUpdate(aTimer, iBalanceTime, iRanDuration, options.start, options.duration);
        }
        
        if (iBalanceTime >= 0) {
          setTimeout($.aClockTimer[iIndex].update, options.freq);
        } else {
          if (options.onEnd) {
            options.onEnd();
          }
          options.ended = true;
        }
      }//update
    };//aClocktimer
    
    setTimeout($.aClockTimer[iIndex].update, options.freq);
	}
  
}(jQuery));