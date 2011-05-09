<?php
extract($vars);

$sFormItem = $this->render("tab-formitem");
?>
<form id="<?=$sViewDBFormPrefix?>frmObject" name="frmObject" class="uniForm" method="POST" action="<?=$sSaveURL?>" enctype="multipart/form-data">
  <?=$sFormItem?>
</form>

<script type="text/javascript">
var <?=$sViewDBFormPrefix?>fieldCnt = 0;
var <?=$sViewDBFormPrefix?>sNewFormHTML = <?=json_encode($sFormItem)?>;

jQuery(document).ready(function() {
  //<?=$sViewDBFormPrefix?>sNewFormHTML = jQuery("#<?=$sViewDBFormPrefix?>frmObject").html();
})

function <?=$sViewDBFormPrefix?>onAfterInitForm() {
  var target="#<?=$sViewDBFormPrefix?>frmObject";
  jQuery(target+" textarea[name=fieldlistlabel]").blur(function() {
    var sValue = jQuery(target+" textarea[name=fieldlistlabel]").val();
    if (sValue!=null) {
      sValue = strReplace("[^a-zA-Z0-9]", "_", sValue);
      sValue = sValue.toLowerCase();
    }
    jQuery(target+" input[name=fieldlistvalue]").val(
      sValue
    );
  });
  initializeTinyMCE("#<?=$sViewDBFormPrefix?>frmObject");
  <?=$this->render("onload.tab-form") ?>
}

function <?=$sViewDBFormPrefix?>resetForm() {
  //document.frmObject.reset();
  jQuery("#<?=$sViewDBFormPrefix?>frmObject").html(<?=$sViewDBFormPrefix?>sNewFormHTML);
  jQuery("#<?=$sViewDBFormPrefix?>frmObject input[name=txtFormType]").val("insert");
  <?=$sViewDBFormPrefix?>onAfterInitForm();
}



jQuery(document).ready(function() {
  var target = "#<?=$sViewDBFormPrefix?>frmObject";
  
  <?=$sViewDBFormPrefix?>onAfterInitForm();
  
  jQuery("#<?=$sViewDBFormPrefix?>frmObject").ajaxForm(
  {
    beforeSubmit: function() {
    },
    dataType:  'json',
    iframe: true,
    success: function(data) {
      if (!data.status) appendNotice(data.msg, "error");
      else {
        appendNotice(data.msg, "success");
        <?=$sViewDBFormPrefix?>doLoadList();
        
        if (jQuery("#<?=$sViewDBFormPrefix?>frmObject input[name=txtFormType]").val()=="insert") {
    	  <?=$sViewDBFormPrefix?>resetForm();
        switchTab("<?=$sViewDBFormPrefix?>tabs", 0);
        } else {
          
        }
      }
    }
  });
});


function <?=$sViewDBFormPrefix?>doLoadObject(cond) {
  var processname = "<?=$sViewDBFormPrefix?>loadobject";
  if (_processing[processname]) return;

  <? if(1==2) { ?>console.log(<?=json_encode($vars)?>); <? } ?>
  jQuery("#<?=$sViewDBFormPrefix?>divFrmObject").html("Loading...");
  switchTab("<?=$sViewDBFormPrefix?>tabs", 1);
  doAjaxCall('<?=$sLoadURL ?>', cond,"GET",
    function(sReturn) {
      <?=$sViewDBFormPrefix?>showObjectForm(eval("("+sReturn+")"));
      _processing[processname] = false;
    });
}

function <?=$sViewDBFormPrefix?>showObjectForm(result) {
  if(! result.status) appendNotice(result.msg, "error");
  else {
    var data = result["return"];
    //console.log(jQuery("#<?=$sViewDBFormPrefix?>divFrmObject"));
    jQuery("#<?=$sViewDBFormPrefix?>divFrmObject").html("<div class=\"ctrlHolderTitle\">Edit Record</div>");
    jQuery("#<?=$sViewDBFormPrefix?>frmObject input[name=txtFormType]").val("update");
    for(var c in data) {
      var sHTML = "";
      var oField = data[c];
      if (oField.label+""!="") {
        sHTML += "<div class=\"ctrlHolder\">\n" +
          oField.label + "\n" + oField.input + "\n</div>";
      } else {
        sHTML = oField.input + "\n";
      }
      jQuery("#<?=$sViewDBFormPrefix?>divFrmObject").append(sHTML);
    }
  }
  <?=$sViewDBFormPrefix?>onAfterInitForm();
  switchTab("<?=$sViewDBFormPrefix?>tabs", 1);
  <?=$this->render("onshowobject.tab-form") ?>
}
</script>