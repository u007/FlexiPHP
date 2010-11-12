<?php

require_once("BaseModxUserextend.php");
require_once("ModxWebUsers.php");

class ModxUserextend extends BaseModxUserextend
{
	function setUp() {
    $this->hasOne('ModxWebUsers as User', array(
			'local' => 'internalKey',
			'foreign' => 'id'
			)
		);
  }
	
}

