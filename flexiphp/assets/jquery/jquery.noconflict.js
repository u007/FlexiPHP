jQuery.noConflict();
jQuery.handleError = function(target, error) {
  if (console) {
    if (console.log) {
      console.log(error);
    }
  }
}