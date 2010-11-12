<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<div style="margin-top: 10px">
	Navigation:
		<a href="<?=$this->url(null, "form")?>">Add User</a>
</div>
<form id="formList" action="" method="GET">

<?=$this->renderFormHidden("mod", $vars["#module"])?>
<?=$this->renderFormHidden("method", "op")?>
<?=$this->renderFormHidden("op", "")?>
<? if(count($vars["list"]) > 0) : ?>
<div style="width: 500px; display: block;">
  <div style="float:left; height: 25px; margin-bottom: 3px; overflow: hidden;">
    <img src="flexiphp/assets/templates/default/img/arrow-down-small.jpg"> Action:
    <?=$this->renderLink("javascript:doFormOperation('delete', '#formList')", "Delete")?>
  </div>
  <div style="float: right; height: 26px; padding-top: 4px; vertical-align: bottom;">
 
  </div>
  <div class="clear"></div>
</div>
<table class="tableDataList">
  <thead>
    <th><?=$this->renderCheckAllBox("checkList[]")?></th>
    <th width="120">User Name</th>
    <th width="120">Actual Name</th>
    <th>Groups</th>
  </thead>
<? foreach($vars["list"] as $oRow) :

  $aGroups = $oRow->Groups;
  $sURL = $this->url(array("rid"=>$oRow->id), "form");
  ?>
  <tr>
    <td><?=$this->renderCheckBox($oRow->id, "checkList[]")?></td>
    <td><a href="<?=$sURL?>"><?=$oRow->username?></a></td>
    <td><a href="<?=$sURL?>"><?=$oRow->Attributes->fullname ?></a></td>
    <td>
      <?
      $aGroupList = array();
      foreach($aGroups as $oGroup):
        $aGroupList[] = $oGroup->GroupName->name;
      endforeach;


      ?><?=implode(", ", $aGroupList); ?>
    </td>
  </tr>
<? endforeach; ?>
</table>
<? endif; ?>
</form>