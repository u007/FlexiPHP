<?php
extract($vars);

$bCanDelete = empty($bCanDelete)? false: true;
$bCanEdit   = empty($bCanEdit)? false: true;

?>
<form id="<?=$sViewDBFormPrefix?>frmList" method="GET" action="<?=$sOpURL?>" class="frmList">
  <input type="hidden" name="txtType" value=""/>
  <? foreach($aParam as $sKey => $sValue) { ?>
  <input type="hidden" name="<?=$sKey?>" value="<?=$sValue?>" />
  <? } ?>
  <?=$this->render("tab-listsearch"); ?>
  <? if($bCanDelete) { ?>
  <input type="button" name="btnDelete" value="Delete" onClick="<?=$sViewDBFormPrefix?>doDeleteObjects()"/>
  <? } ?>
  <div id="<?=$sViewDBFormPrefix?>tableListPageTop" class="divPaging"></div>
  <table id="<?=$sViewDBFormPrefix?>tableList" class="tableGrid" style="" cellspacing="0" cellpadding="0">
    <thead>
      <? if($bCanDelete) { ?>
      <th><input type="checkbox" name="chkAll" value="1" onClick="toogleCheckAll(this, '#<?=$sViewDBFormPrefix?>tableList', 'checkPrimary')"/></th>
      <? } ?>
      <? foreach($aFieldHeader as $sField => $sHTML) { ?>
      <th><?=$sHTML?></th>
      <? } ?>
    </thead>
    <tbody>

    </tbody>
  </table>
  <div id="<?=$sViewDBFormPrefix?>tableListPage">
    <ul class="paging"></ul>
  </div>
</form>

<script type="text/javascript">
var <?=$sViewDBFormPrefix?>iTotal = -1;
var <?=$sViewDBFormPrefix?>iStart = -1;
var <?=$sViewDBFormPrefix?>iLimit = -1;
var <?=$sViewDBFormPrefix?>iPage = -1;
var <?=$sViewDBFormPrefix?>iPageCount = -1;

jQuery(document).ready(function() {

  jQuery("#<?=$sViewDBFormPrefix?>frmList").ajaxForm(
  {
    beforeSubmit: function() {
      var processname = "<?=$sViewDBFormPrefix?>objectlist";
      _processing[processname] = false;
      jQuery("#<?=$sViewDBFormPrefix?>title").html("loading...");
    },
    dataType:  'json',
    success: function(data) {
      var processname = "<?=$sViewDBFormPrefix?>objectlist";
      var listformtype = jQuery("#<?=$sViewDBFormPrefix?>frmList input[name=txtType]").val();
      if (!data.status) appendNotice(data.msg, "error");
      else {
        if (data.msg) appendNotice(data.msg, "success");
        switch(listformtype) {
          case "list":
        <?=$sViewDBFormPrefix?>showObjectList(data);
        switchTab("<?=$sViewDBFormPrefix?>tabs", 0);
            break;
          case "del":
            <?=$sViewDBFormPrefix?>Page(<?=$sViewDBFormPrefix?>iPage, 1);
            break;
        }//switch
      }
      _processing[processname] = false;
      jQuery("#<?=$sViewDBFormPrefix?>title").html("");
    }
  });
});

function <?=$sViewDBFormPrefix?>doDeleteObjects() {
  var bHasCheck = false;
  //var aCheck =
  var cnt = jQuery("#<?=$sViewDBFormPrefix?>frmList input[name*=checkPrimary]:checked").length;
  if (cnt < 1) {
    appendNotice("Please tick a record to delete");
    return;
  }
  if (confirm("Confirm delete " + cnt + " record(s)?")) {
    jQuery("#<?=$sViewDBFormPrefix?>frmList input[name=txtType]").val("del");
    jQuery("#<?=$sViewDBFormPrefix?>frmList").submit();
  }
}

function <?=$sViewDBFormPrefix?>doLoadList(pageno) {
  var processname = "<?=$sViewDBFormPrefix?>objectlist";
  if (_processing[processname]) return;
  _processing[processname] = true;

  var start = <?=$sViewDBFormPrefix?>iLimit*(pageno-1);
  start = !start ? 0: start;
  //console.log(start);
  jQuery("#<?=$sViewDBFormPrefix?>frmList input[name=txtType]").val("list");
  jQuery("#<?=$sViewDBFormPrefix?>frmList input[name=start]").val(start);
  jQuery("#<?=$sViewDBFormPrefix?>frmList").submit();
  return;

  doAjaxCall('<?=$sListURL ?>', aParam,"GET",
    function(sReturn) {
      <?=$sViewDBFormPrefix?>showObjectList(eval("("+sReturn+")"));
      jQuery("#<?=$sViewDBFormPrefix?>title").html("");
      _processing[processname] = false;
    });
}

function <?=$sViewDBFormPrefix?>showObjectList(result) {
  var aCols = <?=json_encode($aListFieldName)?>;
  jQuery("#<?=$sViewDBFormPrefix?>tableList tbody tr").remove();
  if (!result.status) appendNotice(result.msg, "error");
  else {
    var aReturn = result["return"];
    var data = aReturn["total"]? aReturn["data"]: aReturn;
    if (aReturn["total"]) {
      <?=$sViewDBFormPrefix?>iTotal = aReturn["total"];
      <?=$sViewDBFormPrefix?>iStart = aReturn["start"];
      <?=$sViewDBFormPrefix?>iLimit = aReturn["limit"];
      <?=$sViewDBFormPrefix?>iPage = Math.floor(<?=$sViewDBFormPrefix?>iStart/<?=$sViewDBFormPrefix?>iLimit)+1;
      <?=$sViewDBFormPrefix?>iPageCount = Math.floor(<?=$sViewDBFormPrefix?>iTotal/<?=$sViewDBFormPrefix?>iLimit)+1;
    }

    var target = jQuery("#<?=$sViewDBFormPrefix?>tableList tbody");
    target.html("");
    var oRow = [];
    if (data.length > 0) {
      for(var c=0;c < data.length; c++) {
        //console.log("adding: " + data[c]["sName"]);
        oRow = [];
        <? if($bCanDelete) { ?>
        oRow[0] = "<td><input type='checkbox' name='checkPrimary[]' value='" + jsonEncodeObject(data[c]._primary) + "'/></td>\n";
        <? } ?>
        for(var d=0; d<aCols.length; d++) {
          oRow[oRow.length] = "<td>" + data[c][aCols[d]] + "</td>";
        }
        target.append("<tr>\n" + oRow.join("\n") + "</tr>");
      }

      //todo add trigger
      //jQuery("#tableList input[name*=check]").;
    } else {
      target.append("<tr><td colspan='" + (aCols.length<?=$bCanDelete ? "+1":""?>) + "'>No records found</td></tr>");
    }

    var iPageIndexCount = 10;
    var iPage = <?=$sViewDBFormPrefix?>iPage;
    var iPageCount = <?=$sViewDBFormPrefix?>iPageCount;

    //regenerate page no.
    var divPage = jQuery("#<?=$sViewDBFormPrefix?>tableListPage ul");
    divPage.html("");
    divPage.append("<li class=\"" + (iPage > 1? "first": "first-off") +
      "\" onClick=\"<?=$sViewDBFormPrefix?>Page(1)\">««First</li>");
    divPage.append("<li class=\"" + (iPage > 1? "previous": "previous-off") +
      "\" onClick=\"<?=$sViewDBFormPrefix?>Page(" + (iPage-1 < 1? 1: iPage-1) + ")\">«Previous</li>");

    var d=0; var c=0;
    for(c = (iPage-iPageIndexCount < 1? 1: iPage-iPageIndexCount); c <= iPageCount && c <= Math.max(iPage,iPageIndexCount) + iPageIndexCount; c++,d++) {
      divPage.append("<li class=\"" + (iPage == c ? "active": "") +
      "\" onClick=\"<?=$sViewDBFormPrefix?>Page("+c+")\">" + c + "</li>");
  }
    if (c < iPageCount) {
      divPage.append("<li onClick=\"<?=$sViewDBFormPrefix?>Page("+iPageCount+")\">..." + iPageCount + "</li>");
    }

    divPage.append("<li class=\"" + (iPage < iPageCount ? "next": "next-off") +
      "\" onClick=\"<?=$sViewDBFormPrefix?>Page(" + (iPage+1 > iPageCount? iPageCount: iPage+1) + ")\">Next»</li>");
    divPage.append("<li class=\"" + (iPage < iPageCount ? "last": "last-off") +
      "\" onClick=\"<?=$sViewDBFormPrefix?>Page("+iPageCount+")\">Last»»</li>");

    divPage.append("<li class=\"reload\" onClick=\"<?=$sViewDBFormPrefix?>Page("+iPage+",1)\">Reload</li>");

    jQuery("#<?=$sViewDBFormPrefix?>tableListPageTop").html(jQuery("#<?=$sViewDBFormPrefix?>tableListPage").html());

    if (aReturn["total"]) {
      jQuery("#<?=$sViewDBFormPrefix?>frmList input[name=limit]").val(aReturn["limit"]);
      jQuery("#<?=$sViewDBFormPrefix?>frmList input[name=start]").val(aReturn["start"]);
  }
  }
  <?=$this->render("onloadlist.tab-form") ?>
  jQuery("#<?=$sViewDBFormPrefix?>title").html("");
}

function <?=$sViewDBFormPrefix?>Page(pageno, forceLoad) {
  jQuery("#<?=$sViewDBFormPrefix?>frmList input[name=txtType]").val("list");
  if (<?=$sViewDBFormPrefix?>iPage==pageno && !forceLoad) return;
  <?=$sViewDBFormPrefix?>doLoadList(pageno);
}

function <?=$sViewDBFormPrefix?>doDeleteObject(cond) {
  var processname = "<?=$sViewDBFormPrefix?>objectdelete";
  if (_processing[processname]) return;
  _processing[processname] = true;
  if (confirm("Confirm delete this record? (no undo)")) {
    doAjaxCall('<?=$sDelURL ?>', cond,"GET",
    function(sReturn) {
      var data = eval("("+sReturn+")");
      if (data.status) {
        <?=$this->render("ondelete.tab-form") ?>
        <?=$sViewDBFormPrefix?>doLoadList(); //reload list
      }
      appendNotice(data.msg, data.status? "success":"error");
      _processing[processname] = false;
    });
  } else {
    _processing[processname] = false;
  }
}

jQuery(document).ready(function() {
  <?=$sViewDBFormPrefix?>doLoadList();
    
  if (jQuery("#<?=$sViewDBFormPrefix?>divSearch").length > 0) {
    //has search
    jQuery("#<?=$sViewDBFormPrefix?>divSearch input[type=text]").change(function() {
      jQuery("#<?=$sViewDBFormPrefix?>frmList input[name=start]").val(0); //reset page for search change
    });
    jQuery("#<?=$sViewDBFormPrefix?>divSearch select").change(function() {
      jQuery("#<?=$sViewDBFormPrefix?>frmList input[name=start]").val(0); //reset page for search change
    });
  }
  <?=$this->render("onload.tab-list")?>
})

</script>