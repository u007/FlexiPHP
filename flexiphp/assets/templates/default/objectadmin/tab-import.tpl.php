<?php

extract($vars);

?>
<? if (count($aList) > 0 ) { ?>

<table id="importtableList" class="tableGrid" style="width: 500px">
  <thead>
    <th>Table</th>
    <th>Exists</th>
    <th></th>
  </thead>
  <tbody>
    
  </tbody>
</table>

<? } else { ?>
No objects found...
<?
}
?>
<script type="text/javascript">

function doImportLoadList() {
  var processname = "importobjectlist";
  if (_processing[processname]) return;
  _processing[processname] = true;
  doAjaxCall('<?= $this->url(null, "AjaxTableList", $vars["#module"], "", true) ?>', [],"GET",
    function(sReturn) {
      showImportObjectList(eval("("+sReturn+")"));
      _processing[processname] = false;
    });
}

function showImportObjectList(result) {
  jQuery("#importtableList tbody tr").remove();
  if (!result.status) appendNotice(result.msg, "error");
  else {
    var data = result["return"];
    var target = jQuery("#importtableList tbody");
    if (data.length > 0) {
      for(var c=0;c < data.length; c++) {
        //console.log("adding: " + data[c]["sName"]);
        target.append("<tr>\n\
        <td>" + data[c]["name"] + "</td>\n\
        <td>" + "" + "</td>\n\
        <td>\n\
          [ <a href='javascript:' onClick='doImportObject(\"" + data[c]["name"] + "\")'>Import</a> ]\n\
          [ <a href='javascript:' onClick='doDeleteTable(\"" + data[c]["name"] + "\")'>Delete</a> ]\n\
        </td>\n\
        </tr>");
      }
    } else {
      target.append("<tr><td colspan='3'>No objects found</td></tr>");
    }
  }
}

function doImportObject(sName) {
  var processname = "importobjectsync";
  if (_processing[processname]) return;
  _processing[processname] = true;

  doAjaxCall('<?= $this->url(null, "AjaxImport", $vars["#module"], "", true) ?>', {
      "name" : sName
    },"GET",
    function(sReturn) {
      var data = eval("("+sReturn+")");
      if (data.status) {
        doLoadList();
      }
      doLoadInfoLog();
      appendNotice(data.msg, data.status? "success":"error");
      _processing[processname] = false;
    });
}

function doDeleteTable(sName) {
  var processname = "tabledelete";
  return; //todo
  
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
  doImportLoadList();
})

</script>