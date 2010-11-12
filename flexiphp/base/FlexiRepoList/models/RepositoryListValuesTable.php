<?php

class RepositoryListValuesTable extends Doctrine_Record
{
	
	public function setTableDefinition()
	{
		$this->hasColumn("id", "integer", 8, 
			array("default" => null, "primary" => true, "autoincrement" => true) );
		
		$this->hasColumn("listkey", "string", 255);
		$this->hasColumn("weight", "integer", 8);
		
		$this->hasColumn("listvalue", "string", 255);
		$this->hasColumn("listlabel", "string", 1000);
		
		$this->hasColumn("created", "datetime");
		$this->hasColumn("userid", "integer", 8);
		
		$this->setTableName(FlexiConfig::$sDBPrefix . "listvalues");
	}
	
	public function validateOnInsert()
	{
		//ech
	}
	
	public function validateOnUpdate()
	{
		return false;
	}
}

/*
 * CREATE TABLE `novexint_train`.`modx_listvalues` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `listkey` varchar(255)  NOT NULL,
  `listvalue` varchar(255) ,
  `listlabel` TEXT ,
  `weight` int  DEFAULT 999,
  `created` datetime ,
  `userid` int(11) UNSIGNED,
  PRIMARY KEY (`id`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

*/
