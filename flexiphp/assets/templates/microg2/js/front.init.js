
function doCheckAllCheckBox(sName, oInput)
{
	$(sName).attr('checked', oInput.checked);
}

function doFormOperation(sOperation, sForm)
{
	$(sForm + " input[name=op]").val(sOperation);
	//todo check if got checked
	
	$(sForm).submit();
}

function doToogleWidget(sTarget)
{
	if ($(sTarget + " .widget-topinfo").css("display") == "none")
	{
		$(sTarget + " .widget-head a img")[0].src="flexiphp/assets/templates/microg2/images/arrow-up.png";
		$(sTarget + " .widget-topinfo").show();
		$(sTarget + " .widget-toppanel").show();
		$(sTarget + " .widget-content").show();
	}
	else
	{
		$(sTarget + " .widget-head a img")[0].src="flexiphp/assets/templates/microg2/images/arrow-down.png";
		$(sTarget + " .widget-topinfo").hide();
		$(sTarget + " .widget-toppanel").hide();
		$(sTarget + " .widget-content").hide();
	}
}

function doCustomWidgetAddItem(sTarget, sItemCount)
{
	var iCount = parseInt($(sItemCount).val());
	var aItems = $(sTarget + " li");
	
	//console.log(sTarget + " li");
	//console.log(aItems);
	if (iCount >= aItems.length) { return; }
	
	iCount++;
	$(sItemCount).val(iCount);
	refreshCustomWidgetItem(sTarget, sItemCount);
}

function doCustomWidgetMinusItem(sTarget, sItemCount)
{
	//console.log($(sItemCount));
	var iCount = parseInt($(sItemCount).val());
	
	if (iCount <= 1) { return; }
	
	iCount--;
	$(sItemCount).val(iCount);
	refreshCustomWidgetItem(sTarget, sItemCount);
}

function refreshCustomWidgetItem(sTarget, sItemCount)
{
	var aItems = $(sTarget + " li");
	//console.log(sTarget + " li");
	//console.log(aItems);
	//console.log(sItemCount);
	var iCount = parseInt($(sItemCount).val());
	
	//console.log(iCount + "/" + aItems.length);
	for(var c=0; c < aItems.length; c++)
	{
		if (c+1 <= iCount)
		{
			aItems[c].style.display = "block";
		}
		else
		{
			aItems[c].style.display = "none";
		}
	}
}



function doWidgetAddItem(sTarget)
{
	var iCount = parseInt($("#widget" + sTarget + "ItemCount").val());
	var aItems = $("#" + sTarget + " .widget-content ul li");
	
	if (iCount >= aItems.length) { return; }
	
	iCount++;
	$("#widget" + sTarget + "ItemCount").val(iCount);
	refreshWidgetItem(sTarget);
}

function doWidgetMinusItem(sTarget)
{
	var iCount = parseInt($("#widget" + sTarget + "ItemCount").val());
	
	if (iCount <= 1) { return; }
	
	iCount--;
	$("#widget" + sTarget + "ItemCount").val(iCount);
	refreshWidgetItem(sTarget);
}

function refreshWidgetItem(sTarget)
{
	var aItems = $("#" + sTarget + " .widget-content ul li");
	var iCount = parseInt($("#widget" + sTarget + "ItemCount").val());
	
	for(var c=0; c < aItems.length; c++)
	{
		if (c+1 <= iCount)
		{
			aItems[c].style.display = "block";
		}
		else
		{
			aItems[c].style.display = "none";
		}
	}
}

