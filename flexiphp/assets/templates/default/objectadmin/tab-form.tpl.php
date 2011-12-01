<?php
 extract($vars);
 
 $sURL = $this->url(null, "save", $vars["#module"], "", true);


 $aOptions = array(
   "string"     => "String",
   "char"       => "Char",
   "email"     => "eMail",
   "select-tinyint"     => "Combo(TinyInt)",
   "select-smallint"     => "Combo(SmallInt)",
   "select-int"     => "Combo(Int)",
   "select-mediumint"     => "Combo(MediumInt)",
   "select-bigint"     => "Combo(BigInt)",
   "select-text"     => "Combo(Text)",
   "select-enum"     => "Combo(Enum)",
   "select-char"     => "Combo(Char)",
   "html"       => "HTML",
   "html-tiny"       => "HTML(Tiny)",
   "html-medium"       => "HTML(Medium)",
   "html-long"       => "HTML(Big)",
   "text"       => "Text",
   "text-tiny"   => "TinyText",
   "text-medium" => "MediumText",
   "text-long"   => "LongText",
   "json"       => "JSON",
   "json-tiny"       => "JSON(Tiny)",
   "json-medium"       => "JSON(Medium)",
   "json-long"       => "JSON(Big)",
   "int"        => "Int",
   "tinyint"    => "Int(Tiny)",
   "smallint"   => "Int(Small)",
   "mediumint"  => "Int(Medium)",
   "bigint"     => "Int(Big)",
   "money"      => "Money",
   "decimal"    => "Decimal",
   "double"     => "Double",
   "float"      => "Float",
   "date"       => "Date",
   "datetime"   => "Datetime",
   "timestamp"  => "TimeStamp",
   "timestamp-int"  => "TimeStamp(Int)",
   "file-varchar"   => "File",
   "file-text"  => "File(Text)",
   "image-varchar"   => "Image",
   "image-text"  => "Image(Text)",
   "multiimage-text" => "Multi-Image(Text)",
   "blob"       => "Blob",
   "bob-tiny"   => "Blob(Tiny)",
   "blob-medium"=> "Blob(Medium)",
   "blog-long"  => "Blob(Long)"
 );

 $sOptions = "";
 foreach($aOptions as $sKey => $sLabel) {
   $sOptions .= "<option value='" .  $sKey . "'>" . $sLabel . "</option>\\\n\t";
 }
 
?>
<style>
#divFields {
  overflow-x: scroll;
  position: relative;
}

#tableFields {
  margin-bottom: 10px;
}



</style>
<form id="frmObject" name="frmObject" class="uniForm" method="POST" action="<?=$sURL?>">
  <input type="hidden" name="fieldcount" id="fieldcount" value="0" />
  <input type="hidden" name="txtActualName" value=""/>
  <input type="hidden" name="txtPosition" value=""/>
  <div class="ctrlHolder">
    <label for="txtName">Name (if name change from update, new object will be created)</label>
    <input type="text" id="txtName" name="txtName" value="<?=$oObject["sName"]?>" size="35" class="textInput">
  </div>

  <div class="ctrlHolder">
    <label for="txtTableName">Table</label>
    <input type="text" id="txtTableName" name="txtTableName" value="<?=$oObject["sTableName"]?>" size="35" class="textInput">
  </div>

  <div class="ctrlHolder">
    <label for="txtTableName">Version</label>
    <div id="txtDisplayVersion"><?=$oObject["iVersion"]?></div>
  </div>

  <div class="ctrlHolder">
    <span>Fields [ <a href="javascript:" onClick="newObjectField()">+Add</a> ]</span>
    <div id="divFields" style="width: 880px; height: 500px;">
      <table id="tableFields" style="width: 1800px" class="tableGrid style1" >
        <thead>
          <th width="25"></th>
          <th width="25"></th>
          <th>Field Name</th>
          <th>Label</th>
          <th >Type</th>
          <th>Auto</th>
          <th>Precision</th>
          <th>Can Null</th>
          <th>Options</th>
          <th>Default (Use '')</th>
          <th>Is Primary</th>
          <th>Is Unique</th>
          <th>Insert</th>
          <th>Update</th>
          <th>Insert input</th>
          <th>Update input</th>
          <th>Allow HTML</th>
          <th>Allowed TAG(,)</th>
          <th>List</th>
          <th>Form Size</th>
          <th>Link Name</th>
        </thead>
        <tbody>
          <tr id="tableFieldsNewRow">
            <td colspan="19" style="text-align: left;">[ <a href="javascript:" onClick="newObjectField()">+</a> ]<td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="buttonHolder">
    <button type="submit" class="primaryAction">Save</button>
    <button type="button" class="primaryAction" onClick="resetForm()">Cancel</button>
  </div>

</form>

<script type="text/javascript">
var fieldCnt = 0;

function resetForm() {
  document.frmObject.reset();
  doClearObjectFields();
}

function cleanSep(value, sep, replace) {
  if (!value) return value;
  var result = ""
  var aValue = value.split(sep)
  result = aValue.join(replace);
  return result;
}


function removeObjectField(cnt) {
  jQuery("#tableFields #tableFieldsRow" + cnt).hide();
  jQuery("#tableFields input[name=txtFieldStatus" + cnt+"]").val("0");
}

function newObjectField(data) {
  var sName = data ? data["sName"]: "";
  var sLabel = data? data["label"]: "";
  var sType = data? data["type"]: "";
  var sPrecision = data? data["precision"]: "";
  var mDefault = data? data["default"]: "null";
  var bCanNull = data? data["cannull"]: 1;
  var bAutoNumber = data? data["autonumber"]: 0;
  var bPrimary    = data? data["primary"]: 0;
  var bUnique     = data? data["unique"]: 0;
  //always use existing old name, only reset after sync
  var sOldName    = data? (!data["oldname"] ? data["sName"]: data["oldname"]): "";
  var sOldType    = data? (!data["oldtype"] ? data["type"]: data["oldtype"]): "";

  var bInsert       = data? data["caninsert"]: 1;
  var bUpdate       = data? data["canupdate"]: 1;
  var sInsertInput  = data? data["inputinsert"]: 0;
  var sUpdateInput  = data? data["inputupdate"]: 0;
  var bCanList      = data? data["canlist"]: 1;

  var bAllowHTML    = data? data["allowhtml"]: 0;
  var sAllowTag    = data? data["allowtag"]: "";
  var sFormSize    = data? data["formsize"]: "25";
  var sLinkName     = data? data["linkname"]: "";
  var sOption       = data? data["options"]: "";

  if (sOption == null) sOption = "";

  var cnt = fieldCnt;
  jQuery("#tableFieldsNewRow").before(
    "<tr id='tableFieldsRow" + cnt + "'>\n\
      <td>O<input type='hidden' name='txtCnt' value='" + cnt + "'/></td>\n\
      <td><a href='javascript:' onClick='removeObjectField(" + cnt + ")'>X</a>\n\
          <input type='hidden' name='txtFieldOldName"+cnt+"' value='" + sOldName + "'/>\n\
          <input type='hidden' name='txtFieldOldType"+cnt+"' value='" + sOldType + "'/>\n\
          <input type='hidden' name='txtFieldStatus"+cnt+"' value='1'/></td>\n\
      <td><input type='text' name='txtFieldName"+cnt+"' value='" + sName + "' size='15' maxsize='255'/></td>\n\
      <td><input type='text' name='txtFieldLabel"+cnt+"' value='"+ sLabel + "' size='15'  maxsize='255'/></td>\n\
      <td align='center'>\n\
        <select name='txtFieldType"+cnt+"' style='width: 130px'>\n\
          <?=$sOptions?>
        </select>\n\
      </td>\n\
      <td><input type='checkbox' name='txtFieldAutoNumber"+cnt+"' value='1' " + (bAutoNumber ? "checked='checked'":"") + " /></td>\n\
      <td><input type='text' name='txtFieldPrecision"+cnt+"' value='" + sPrecision + "' size='5' maxsize='20'/></td>\n\
      <td><input type='checkbox' name='txtFieldCanNull"+cnt+"' value='1' " + (bCanNull ? "checked='checked'":"") + " /></td>\n\
      <td><textarea name='txtFieldOptions"+cnt+"'  style='width: 150px; height: 50px;' >" + sOption + "</textarea></td>\n\
      <td><input type='text' name='txtFieldDefault"+cnt+"' value=\"" + cleanSep(mDefault,"\"","\\\"") + "\"  size='15' /></td>\n\
      <td><input type='checkbox' name='txtFieldPrimary"+cnt+"' value='1' " + (bPrimary ? "checked='checked'":"") + " /></td>\n\
      <td><input type='checkbox' name='txtFieldUnique"+cnt+"' value='1' " + (bUnique ? "checked='checked'":"") + " /></td>\n\
      <td><input type='checkbox' name='txtFieldCanInsert"+cnt+"' value='1' " + (bInsert ? "checked='checked'":"") + " /></td>\n\
      <td><input type='checkbox' name='txtFieldCanUpdate"+cnt+"' value='1' " + (bUpdate ? "checked='checked'":"") + " /></td>\n\
      <td><select name='txtFieldInputInsert"+cnt+"' style='width: 80px'>\n\
          <option value='edit'" + (sInsertInput=="edit" ? " selected":"") + ">Editable</option>\n\
          <option value='readonly'" + (sInsertInput=="readonly" ? " selected":"") + ">Display Only</option>\n\
          <option value='display'" + (sInsertInput=="display" ? " selected":"") + ">Show &amp; Hidden</option>\n\
          <option value='hidden'" + (sInsertInput=="hidden" ? " selected":"") + ">Hidden</option>\n\
          <option value='none'" + (sInsertInput=="none" ? " selected":"") + ">None</option>\n\
        </select>\n\
      </td>\n\
      <td><select name='txtFieldInputUpdate"+cnt+"' style='width: 80px'>\n\
          <option value='edit'" + (sUpdateInput=="edit" ? " selected":"") + ">Editable</option>\n\
          <option value='readonly'" + (sUpdateInput=="readonly" ? " selected":"") + ">Display Only</option>\n\
          <option value='display'" + (sUpdateInput=="display" ? " selected":"") + ">Show &amp; Hidden</option>\n\
          <option value='hidden'" + (sUpdateInput=="hidden" ? " selected":"") + ">Hidden</option>\n\
          <option value='none'" + (sUpdateInput=="none" ? " selected":"") + ">None</option>\n\
        </select>\n\
      </td>\n\
      <td><input type='checkbox' name='txtFieldAllowHTML"+cnt+"' value='1' " + (bAllowHTML ? "checked='checked'":"") + " /></td>\n\
      <td><input type='text' name='txtFieldAllowTag"+cnt+"' value=\"" + cleanSep(sAllowTag,"\"","\\\"") + "\"  size='15' /></td>\n\
      <td><input type='checkbox' name='txtFieldCanList"+cnt+"' value='1' " + (bCanList ? "checked='checked'":"") + " /></td>\n\
      <td><input type='text' name='txtFieldFormSize"+cnt+"' value=\"" + cleanSep(sFormSize,"\"","\\\"") + "\"  size='5' /></td>\n\
      <td><input type='text' name='txtFieldLinkName"+cnt+"' value=\"" + cleanSep(sLinkName,"\"","\\\"") + "\"  size='5' /></td>\n\
     </tr>"
  );
  jQuery("#frmObject select[name=txtFieldType"+cnt+"]").val(sType);
  
  fieldCnt++;
  jQuery("#frmObject input[name=fieldcount]").val(fieldCnt);
  
  var sPosition = jQuery("#frmObject input[name=txtPosition]").val();
  sPosition = !sPosition || sPosition.length==0 ? cnt+"": sPosition + "," + cnt;
  jQuery("#frmObject input[name=txtPosition]").val(sPosition);
}

jQuery(document).ready(function() {
  doClearObjectFields();
  <?if (count($oObject["aChild"]["field"]) < 1) { ?>
      newObjectField({"sName": "id", "label": "Id", "type": "int", "cannull":0, 
        "precision": "11", "autonumber": 1, "primary": 1, "default": "",
        "caninsert": 1, "canupdate": 1, "inputinsert": "hidden", "inputupdate": "readonly",
        "canlist": true, "allowhtml": 0, "allowtag": "", "formsize": "25", "linkname": "rid",
        "options": ""});
      newObjectField();
  <? } ?>
  <? foreach($oObject["aChild"]["field"] as $sKey => $aValue) { ?>
    newObjectField($aValue);
  <? } ?>
  fieldCnt = jQuery("#tableFields tbody tr").length;

  jQuery("#frmObject").ajaxForm(
  {
    beforeSubmit: function() {
    },
    dataType:  'json',
    success: function(data) {
      if (!data.status) appendNotice(data.msg, "error");
      else {
        appendNotice(data.msg, "success");
        doLoadList();
        doLoadSQLLog();
        jQuery("#tabs").tabs( "select" , 0 );
      }
      doLoadInfoLog();
    }
  });

  jQuery("#tableFields tbody").sortable({
    update: function(event, ui) {
      updateObjectFieldPosition();
    }
  });
  //disable 1st cell from selection
	jQuery("#tableFields tbody tr td:first").disableSelection();

});

function doClearObjectFields() {
  jQuery("#tableFields tbody tr").slice(0,-1).remove();
  fieldCnt = jQuery("#tableFields tbody tr").length-1;
  //ignore add button row
  jQuery("#frmObject input[name=txtName]").val("");
  jQuery("#frmObject input[name=txtTableName]").val("");
  jQuery("#frmObject input[name=txtActualName]").val("");
  jQuery("#frmObject input[name=fieldcount]").val(fieldCnt);
  jQuery("#frmObject #txtDisplayVersion").html("1");
  updateObjectFieldPosition();
}


function doLoadObject(sName) {
  var processname = "loadobject";
  if (_processing[processname]) return;
  doAjaxCall('<?= $this->url(null, "AjaxLoad", $vars["#module"], "", true) ?>', {
    "name": sName
  },"GET",
    function(sReturn) {
      showObjectForm(eval("("+sReturn+")"));
      _processing[processname] = false;
    });
}

function showObjectForm(result) {
  if(! result.status) appendNotice(result.msg, "error");
  else {
    var data = result["return"];
    doClearObjectFields();
    jQuery("#frmObject input[name=txtName]").val(data.sName);
    jQuery("#frmObject input[name=txtTableName]").val(data.sTableName);
    jQuery("#frmObject input[name=txtActualName]").val(data.sName);
    jQuery("#frmObject #txtDisplayVersion").html(data.iVersion);
    
    for(var i in data.aChild.field) {
      newObjectField(data.aChild.field[i]);
    }
    updateObjectFieldPosition();
  }
  jQuery("#tabs").tabs("select", 1);
}

function updateObjectFieldPosition() {
  var aList = jQuery("#tableFields tbody tr");
  var aPosition = [];
  //last position is the add button, ignoring it
  for(var c=0; c < aList.length-1; c++) {
    aPosition[aPosition.length] = jQuery("td:first input", aList[c]).val();
  }
  if (aPosition.length < 1) {
    jQuery("#frmObject input[name=txtPosition]").val(""); return;
  }
  jQuery("#frmObject input[name=txtPosition]").val(aPosition.join(","));
}
</script>