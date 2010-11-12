<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<? if(count($vars["list"]) > 0) : ?>
<table class="tableDataList">
  <thead>
    <th width="120">User Name</th>
    <th width="120">Actual Name</th>
    <th>Groups</th>
  </thead>
<? foreach($vars["list"] as $oRow) : ?>
  <tr>
    <td><a href="<?=$this->url(array("rid"=>$oRow->id), "form")?>"><?=$oRow->username?></a></td>
    <td></td>
    <td></td>
  </tr>
<? endforeach; ?>
</table>
<? endif; ?>