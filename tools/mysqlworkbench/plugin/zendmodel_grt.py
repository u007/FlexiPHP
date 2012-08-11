## EER Model Utility
# MySQL Workbench Plugin
# Written in MySQL Workbench 5.2.4

from wb import *
import os
import grt
import mforms
from mako.template import Template


ModuleInfo = DefineModule("zendmodel", author="James", version="1.0", description="Contains Plugin zendmodelplugin")

# This plugin takes the EER Model as an argument and will appear in the Plugins -> Utilities menu
@ModuleInfo.plugin("entstudio.zend.generator", caption="Generate Zend Models",
                   description="Generate Dbtables, model and mapper", input=[wbinputs.currentModel()], pluginMenu="Utilities")
@ModuleInfo.export(grt.INT, grt.classes.workbench_physical_Model)
def doGenerateZendModel(model):
	
	if grt.root.wb.docPath is None:
		return -1
	
	#export all models: grt.root.wb.doc.physicalModels
	sPath = os.path.dirname(grt.root.wb.docPath)
	
	sImportDir = sPath + "/templates/"
	print "Import Path: " + sImportDir
	
	for schema in grt.root.wb.doc.physicalModels[0].catalog.schemata:
		if os.path.exists(sPath + "/output/models/DbTable") is False:
			os.makedirs(sPath + "/output/models/DbTable")
		
		if os.path.exists(sPath + "/output/forms") is False:
			os.makedirs(sPath + "/output/forms")
		
		for table in schema.tables:
			
			aPrimary = []
			sPrimary = ""
			if table.primaryKey:
				for col in table.primaryKey.columns:
					sPrimary += "" if sPrimary == "" else ","
					sPrimary += "\"" + col.referencedColumn.name + "\""
					aPrimary.append(col.referencedColumn.name)
			sTableName = table.name
			sClassName = sTableName.title()
			sClassName = sClassName.replace(" ", "")
			
			print "Setting table: " + sTableName
			sField = ""
			for col in table.columns:
				sField += "" if sField == "" else ","
				sField += "\"" + col.name + "\""
				
			mytemplate = Template(filename=sImportDir+'dbtable.txt')
			sOutput = mytemplate.render(classname=sClassName, tablename=sTableName, primary=sPrimary, fields=sField)
			f = open(sPath + "/output/models/DbTable/" + sClassName + ".php", 'w')
			f.write(sOutput)
			f.close()
			
			mymodel = Template(filename=sImportDir+"model.txt")
			sOutput = mymodel.render(classname=sClassName, tablename=sTableName, primary=sPrimary, 
				aFields=table.columns, fields=sField, aPrimary=aPrimary)
			f = open(sPath + "/output/models/" + sClassName + ".php", 'w')
			f.write(sOutput)
			f.close()
			
			mymapper = Template(filename=sImportDir+"mapper.txt")
			sOutput = mymapper.render(classname=sClassName, tablename=sTableName, primary=sPrimary, 
				aFields=table.columns, fields=sField, aPrimary=aPrimary)
			f = open(sPath + "/output/models/" + sClassName + "Mapper.php", 'w')
			f.write(sOutput)
			f.close()
			
			myform = Template(filename=sImportDir+"form.txt")
			sOutput = myform.render(classname=sClassName, tablename=sTableName, primary=sPrimary, 
				aFields=table.columns, fields=sField, aPrimary=aPrimary)
			f = open(sPath + "/output/forms/" + sClassName + ".php", 'w')
			f.write(sOutput)
			f.close()
			
	return 0
