jQuery.noConflict();
jQuery.handleError = function(target, error) {
  if (console) {
    if (console.log) {
      console.log(error);
    }
  }
}
jQuery.httpData = function(response, returntype) {
  //does nothing
  switch(returntype) {
    case "json":
      return eval("(" + response.responseText + ")");
      break;
    case "text":
    case "html":
      return response.responseText;
      break;
  }
}