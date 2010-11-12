<?php

?>
<script type="text/javascript">
  function onTypeChange() {
    //console.log("change: " + jQuery("#txtFieldType").val());
    if (jQuery("#ftxtDBLengthOri").val() == "") {
      switch(jQuery("#ftxtFieldType").val()) {
        case "varchar":
          jQuery("#ftxtDBLength").val("255");
          break;
        case "text":
        case "int":
        case "tiny":
        case "double":
        case "datetime":
        case "date":
        case "time":
          jQuery("#ftxtDBLength").val("");
          break;
      }
    }
  }
</script>
<table>
  <tr>
    <td style="vertical-align: top;">Field</td>
    <td style="vertical-align: top;"><input type="text" id="ftxtFieldName" name="txtFieldName" value="" size="35"/></td>
  </tr>
  
  <tr>
    <td style="vertical-align: top;">DB Type</td>
    <td style="vertical-align: top;">
      <select id="ftxtFieldType" name="txtFieldType" onChange="onTypeChange();">
        <option value="varchar">Varchar</option>
        <option value="text">Text</option>
        <option value="int">Integer</option>
        <option value="tiny">Tiny Number (0-127)</option>
        
        <option value="double">Double</option>
        
        <option value="datetime">Datetime</option>
        <option value="date">Date</option>
        
        <option value="timestamp">Timestamp</option>
      </select>
    </td>
  </tr>

  <tr>
    <td style="vertical-align: top;">DB Length</td>
    <td style="vertical-align: top;">
      <input type="hidden" id="ftxtDBLengthOri" name="txtDBLengthOri" value="" />
      <input type="text" maxlength="50" id="ftxtDBLength" name="ftxtDBLength" value="" size="35"/>
    </td>
  </tr>

  <tr>
    <td style="vertical-align: top;">Default Value</td>
    <td style="vertical-align: top;">
      <input type="hidden" id="ftxtDBDefaultOri" name="ftxtDBDefaultOri" value="" />
      <input type="text" maxlength="255" id="ftxtDBDefault" name="ftxtDBDefault" value="" size="35"/>
    </td>
  </tr>

  <tr>
    <td style="vertical-align: top;">Configuration</td>
    <td style="vertical-align: top;">
      <div>
        <input id="ftxtPrimary" name="ftxtPrimary" type="checkbox" value="1"/><label for="txtPrimary">Is Primary</label>
      </div>
      <div>
        <input id="ftxtAutonumber" name="ftxtAutonumber" type="checkbox" value="1"/><label for="txtAutonumber">Auto number</label>
      </div>
    </td>
  </tr>

  <tr>
    <td></td>
    <td><input type="button" value="Add" id="fbtnAdd" name="btnAdd" onClick="addField()"/></td>
  </tr>


</table>
<script type="text/javascript">
jQuery(document).ready(function() {
  onTypeChange();
});
</script>