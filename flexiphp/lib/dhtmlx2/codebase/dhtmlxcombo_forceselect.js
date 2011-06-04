dhtmlXCombo.prototype._confirmSelection = function(data,status){
if(arguments.length==0){
var z=this.getOptionByLabel(this.DOMelem_input.value);
//data = z?z.value:this.DOMelem_input.value;
data = z?z.value:null;
status = (z==null);
if (data==this.getActualValue()) return;
};
this.DOMelem_hidden_input.value=data;
this.DOMelem_hidden_input2.value = (status?"true":"false");
this.callEvent("onChange",[]);
this._activeMode=false;
}
