<?php

?>
<div style="font-size: 10px;">Order by DESC Time (Last 80 lines)</div>
<div id="divInfoLog" style="border:1px solid #B0BEC7; font-size: 11px; padding: 5px;">

</div>

<script type="text/javascript">
function doLoadInfoLog() {
  var processname = "infolog";
  if (_processing[processname]) return;
  _processing[processname] = true;
  doAjaxCall('<?= $this->url(null, "AjaxLog", $vars["#module"], "", true) ?>', {type:"info", sep: ".\n"},"GET",
    function(sReturn) {
      showInfoLog(eval("("+sReturn+")"));
      _processing[processname] = false;
    });
}

function showInfoLog(result) {
  if (!result.status) appendNotice(result.msg, "error");
  else {
    var data = result["return"];
    jQuery("#divInfoLog").html(data);
  }
}

jQuery(document).ready(function() {
  doLoadInfoLog();
})
</script>
