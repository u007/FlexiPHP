<?php
extract($vars);

$bCanDelete = empty($bCanDelete)? false: true;
$bCanEdit   = empty($bCanEdit)? false: true;
?>
<? if($bCanDelete) { ?>
<input type="button" class="button clickable" name="btnDelete" value="Delete" onClick="<?=$sViewDBFormPrefix?>doDeleteObjects()"/>
<? } ?>
<div id="<?=$sViewDBFormPrefix?>tableListPageTop" class="divPaging dataTables_paginate paging_full_numbers"></div>
<table id="<?=$sViewDBFormPrefix?>tableList" class="tableGrid style1 datatable" style="" cellspacing="0" cellpadding="0">
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
  <ul class="paging dataTables_paginate paging_full_numbers"></ul>
</div>

<script type="text/javascript">
function <?=$sViewDBFormPrefix?>RenderPaging() {
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
}
</script>