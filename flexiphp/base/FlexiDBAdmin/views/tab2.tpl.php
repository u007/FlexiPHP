<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<script type="text/javascript">
var aTable = null;

function resetTableForm() {
  aTable = new Array();
  aTable.fields = new Array();
}

function addField() {
  console.log("adding field");
  var oField = new Array();
  oField.fname = jQuery("#ftxtFieldName").val();
  oField.ftype = jQuery("#ftxtFieldType").val();
  oField.flen = jQuery("#ftxtDBLength").val();
  oField.fdefault = jQuery("#ftxtDBDefault").val() == null ? "" : jQuery("#ftxtDBDefault").val();
  oField.fprimary = jQuery("#ftxtPrimary:checked").val();
  oField.fautonumber = jQuery("#ftxtAutonumber:checked").val();
  
  aTable.fields[aTable.fields.length] = oField;

  console.log(jQuery("#tblFieldList > tbody:last"));
  jQuery("#tblFieldList > tbody:last").append("<tr>" + 
  "<td>" + oField.fname + "</td>" +
  "<td>" + oField.ftype + "</td>" +
  "<td>" + oField.flen + "</td>" +
  "<td>" + oField.fdefault + "</td>" +
  "<td>" + (oField.fprimary ? "Primary\n<br/>" : "") +
    (oField.fautonumber? "Autonumber\n<br/>" : "") +
  "</tr>");

  resetFieldsForm();

  jQuery("#divFields").tabs("option", "selected", 0);
}

function reloadFields() {
//  <th width="200">Field</th>
//        <th width="100">DB Type</th>
//        <th width="100">DB Length</th>
//        <th width="100">Default</th>
}

function resetFieldsForm() {
  jQuery("#ftxtFieldName").val("");
  jQuery("#ftxtFieldType").val("varchar");

  jQuery("#ftxtDBDefault").val("");
  jQuery("#ftxtPrimary").val()[0].checked = 0;
  jQuery("#ftxtAutonumber").val()[0].checked = 0;

  onTypeChange();
}


resetTableForm();
</script>
<h2 class="tab">New Table</h2>
<table>
  <tr>
    <td>Name</td>
    <td><input type="text" name="txtName" value="" size="40"/></td>
  </tr>
  <tr>
    <td>Description</td>
    <td><input type="text" name="txtDescription" value="" size="40"/></td>
  </tr>

  <tr>
    <td>Encoding</td>
    <td>
      <select name="txtCharset">
        <option value="utf-8">UTF-8</option>
        <option value="latin1">latin1</option>
      </select>
    </td>
  </tr>
</table>

<div id="divFields">
  <ul>
    <li><a href="#divFieldList"><span>Field List</span></a></li>
    <li><a href="#divFieldAdd"><span>Add Field</span></a></li>
  </ul>


  <div id="divFieldList">
    <table id="tblFieldList" cellpadding="2" cellspacing="0">
      <thead>
        <th width="200">Field</th>
        <th width="100">DB Type</th>
        <th width="100">DB Length</th>
        <th width="100">Default</th>
        <th width="120"></th>
      </thead>
      <tbody>

      </tbody>
    </table>
  </div>

  <div id="divFieldAdd">
      <?=$this->render("table.field") ?>
  </div>

</div>
<script type="text/javascript">
  jQuery("#divFields").tabs();
</script>

<div>
  <input type="submit" value="Save" name="btnSave" />
</div>
<!--end divFields-->