<?php

extract($vars);

?>
<? if (count($aList) > 0 ) { ?>

<table id="tableList" class="tableGrid style1" style="width: 500px">
  <thead>
    <th>Name</th>
    <th>Table</th>
    <th></th>
  </thead>
  <tbody>
    
  </tbody>
</table>

<? } else { ?>
<table id="tableList" class="tableGrid style1" style="width: 500px">
  <thead>
    <th>Name</th>
    <th>Table</th>
    <th></th>
  </thead>
  <tbody>
    <tr>
      <td colspan="3">No objects found...</td>
    </tr>
  </tbody>
</table>
<?
}
?>
<script type="text/javascript">

function doLoadList() {
  var processname = "objectlist";
  if (_processing[processname]) return;
  _processing[processname] = true;
  doAjaxCall('<?= $this->url(null, "AjaxList", $vars["#module"], "", true) ?>', [],"GET",
    function(sReturn) {
      showObjectList(eval("("+sReturn+")"));
      _processing[processname] = false;
    });
}

function showObjectList(result) {
  jQuery("#tableList tbody tr").remove();
  if (!result.status) appendNotice(result.msg, "error");
  else {
    var data = result["return"];
    var target = jQuery("#tableList tbody");
    if (data.length > 0) {
      for(var c=0;c < data.length; c++) {
        //console.log("adding: " + data[c]["sName"]);
        target.append("<tr>\n\
        <td><a href='javascript:' onClick='doLoadObject(\"" + data[c]["sName"] + "\")'>" + data[c]["sName"] + "</a></td>\n\
        <td>" + data[c]["sTableName"] + "</td>\n\
        <td>\n\
          [ <a href='javascript:' onClick='doSyncObject(\"" + data[c]["sName"] + "\")'>Sync</a> ]\n\
          [ <a href='javascript:' onClick='doDeleteObject(\"" + data[c]["sName"] + "\")'>Delete</a> ]\n\
        </td>\n\
        </tr>");
      }
    } else {
      target.append("<tr><td colspan='3'>No objects found</td></tr>");
    }
  }
}

function doSyncObject(sName) {
  var processname = "objectsync";
  if (_processing[processname]) return;
  _processing[processname] = true;
  doAjaxCall('<?= $this->url(null, "AjaxSync", $vars["#module"], "", true) ?>', {
      "name" : sName
    },"GET",
    function(sReturn) {
      var data = eval("("+sReturn+")");
      if (data.status) {
        doLoadSQLLog();
      }
      appendNotice(data.msg, data.status? "success":"error");
      _processing[processname] = false;
    });
}

function doDeleteObject(sName) {
  var processname = "objectdelete";
  if (_processing[processname]) return;
  _processing[processname] = true;
  if (confirm("Confirm delete this object? (no undo)")) {
    doAjaxCall('<?= $this->url(null, "AjaxDelete", $vars["#module"], "", true) ?>', {
      "name" : sName
    },"GET",
    function(sReturn) {
      var data = eval("("+sReturn+")");
      if (data.status) {
        doLoadList(); //reload list
        doLoadSQLLog();
      }
      appendNotice(data.msg, data.status? "success":"error");
      _processing[processname] = false;
    });
  } else {
    _processing[processname] = false;
  }
}

jQuery(document).ready(function() {
  doLoadList();
})

</script>