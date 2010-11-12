<?
$aList = $vars["aList"];
?>
<div>
	<div>
	<? if (count($aList) > 0) :?>
	
		<form id="formList" action="" method="GET">
			<?=$this->renderFormHiddenRaw("mod", "FlexiRepoList")?>
			<?=$this->renderFormHiddenRaw("method", "op")?>
			<?=$this->renderFormHiddenRaw("op", "")?>
      <?=$this->renderFormHiddenRaw("a", FlexiConfig::$aQueryString["a"])?>
			<table class="tableDataList" style="width: 500px; ">
				<thead>
					<th><?=$this->renderCheckAllBox("checkList[]")?></th>
					<th width="24%">Key</th>
					<th width="42%">Value</th>
					<th width="42%">Label</th>
				</thead>
				<tbody>
				<? foreach($aList as $aRow): 
					
				?>
				<tr>
					<td><?=$this->renderCheckBox($aRow["id"], "checkList[]")?></td>
					<td><a href="javascript:loadForm(<?=$aRow["id"]?>)"><?=$aRow["listkey"]?></a></td>
					<td><a href="javascript:loadForm(<?=$aRow["id"]?>)"><?=$aRow["listvalue"]?></a></td>
					<td><?=$aRow["listlabel"]?></td>
				</tr>
				<? endforeach; ?>
				</tbody>
			</table>
		</form>

<script type="text/javascript">
function loadForm(tid) {
  jQuery("#divProfileForm").html("Loading...");
  <?=FlexiFormUtil::tinyMCERemoveControl($aForm);?>

  jQuery("#divProfileForm").load("<?=$this->url(null, "form", $vars["#module"], "", true)?>&rid=" + tid,
    function(response, status, xhr) {
      <?=FlexiFormUtil::tinyMCEInitControl($aForm);?>
    });
  tabs.setActiveTab(1);
  tabs.items.itemAt(1).setTitle("<span>Edit Setting</span>");
}
function cancelForm() {
  jQuery("#divProfileForm").html("Loading...");
  <?=FlexiFormUtil::tinyMCERemoveControl($aForm);?>
  jQuery("#divProfileForm").load("<?=$this->url(null, "form", $vars["#module"], "", true)?>", function(response, status, xhr) {
    <?=FlexiFormUtil::tinyMCEInitControl($aForm);?>
  });
  tabs.items.itemAt(1).setTitle("<span>New Setting</span>");
  tabs.setActiveTab(0);
}
</script>

	<? else: ?>
		No records found
	<? endif; ?>
		<? //var_dump($vars["aList"]) ?>
	</div>
</div>
