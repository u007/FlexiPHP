<?php

?>
<div style="font-size: 10px;">Order by DESC Time (Last 80 lines)</div>
<div id="divSQLLog" style="border:1px solid #B0BEC7; font-size: 11px; padding: 5px;">

</div>

<script type="text/javascript">
function doLoadSQLLog() {
  var processname = "sqllog";
  if (_processing[processname]) return;
  _processing[processname] = true;
  doAjaxCall('<?= $this->url(null, "AjaxLog", $vars["#module"], "", true) ?>', {type:"sql","sep" : ";.\n"},"GET",
    function(sReturn) {
      showSQLLog(eval("("+sReturn+")"));
      _processing[processname] = false;
    });
}

function showSQLLog(result) {
  if (!result.status) appendNotice(result.msg, "error");
  else {
    var data = result["return"];
    jQuery("#divSQLLog").html(data);
  }
}

jQuery(document).ready(function() {
  doLoadSQLLog();
})
</script>
