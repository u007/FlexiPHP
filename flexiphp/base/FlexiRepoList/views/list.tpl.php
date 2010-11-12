<?
$aList = $vars["aList"];
$aKeys = $vars["aKeys"];
$aOptions = array("" => "- select -");
foreach($aKeys as $oRow)
{
  $aOptions[$oRow->listkey] = $oRow->listkey;
}
?>
<div>
	<div style="margin-top: 10px">
	Navigation: 
	<?=$this->renderFlexiLink("", "Add", "form", null);?>
	</div>
	<div>
	<? if (count($aList) > 0) :?>
	<script type="text/javascript">
		function doFilterForm(oSelect)
		{
			$("#formList input[name=op]").val("list");
			$("#formList").submit();
		}
	</script>
		<form id="formList" action="" method="GET">
		<div style="width: 500px; display: block;">
			<div style="float:left; height: 25px; margin-bottom: 3px; overflow: hidden;">
				<img src="flexiphp/assets/templates/default/img/arrow-down-small.jpg"> Action:
				<?=$this->renderLink("javascript:doFormOperation('delete', '#formList')", "Delete")?>
			</div>
			<div style="float: right; height: 26px; padding-top: 4px; vertical-align: bottom;">
				<span>Filter: Key: </span>
				<?=$this->renderFormSelectRaw("filter_listkey", $vars["filter_listkey"], $aOptions, null, array("onChange" => "doFilterForm(this)"));?>
			</div>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
			<?=$this->renderFormHiddenRaw("mod", "FlexiRepoList")?>
			<?=$this->renderFormHiddenRaw("method", "op")?>
			<?=$this->renderFormHiddenRaw("op", "")?>
      <? if (FlexiConfig::$sFramework == "modx" || FlexiConfig::$sFramework == "modx2") { ?>
      <?=$this->renderFormHiddenRaw("a", FlexiConfig::$aQueryString["a"])?>
      <?=$this->renderFormHiddenRaw("id", FlexiConfig::$aQueryString["id"])?>
      <? } ?>
			<table class="tableDataList" style="width: 500px; ">
				<thead>
					<th><?=$this->renderCheckAllBox("checkList[]")?></th>
					<th width="24%">Key</th>
					<th width="42%">Value</th>
					<th width="42%">Label</th>
				</thead>
				<tbody>
				<? foreach($aList as $aRow): 
					$sLink = flexiURL("mod=FlexiRepoList&method=form&rid=" . $aRow["id"]);
				?>
				<tr>
					<td><?=$this->renderCheckBox($aRow["id"], "checkList[]")?></td>
					<td><a href="<?=$sLink?>"><?=$aRow["listkey"]?></a></td>
					<td><a href="<?=$sLink?>"><?=$aRow["listvalue"]?></a></td>
					<td><?=$aRow["listlabel"]?></td>
				</tr>
				<? endforeach; ?>
				</tbody>
			</table>
		</form>
		<div>
			<?=$this->renderPartial("pagination", 
				array("page" => $vars["page"], "max" => $vars["totalrecords"], "rowsperpage" => $vars["rowsperpage"], 
					"params" => $vars["params"], "url" => $vars["url"] ), 
				$vars["#modulepath"]); ?>
		</div>
	<? else: ?>
		No records found
	<? endif; ?>
		<? //var_dump($vars["aList"]) ?>
	</div>
</div>
