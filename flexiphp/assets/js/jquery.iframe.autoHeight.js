/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


jQuery.fn.extend({
  autoHeight:function(a){
    if(this[0]===window){
      var iHeight = jQuery(document).height();
      var iWinHeight = jQuery(window).height();
      console.log(iHeight + "/" + iWinHeight);
      jQuery(window).height(iHeight);
      console.log(window.parentNode);
      console.log(jQuery(window, parent.document));
      jQuery(window, parent.document).css("height", iHeight)
      //setInterval(function(){ jQuery(); },a||1000);
    }
  return this}
});