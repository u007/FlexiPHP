<?php

class FlexiXPDO extends XPDO {

  function __construct($options = array()) {
    $options = array(
        xPDO::OPT_CACHE_PATH => FlexiConfig::$sBaseDir .'cache',
        xPDO::OPT_TABLE_PREFIX => '',
        xPDO::OPT_HYDRATE_FIELDS => true,
        xPDO::OPT_HYDRATE_RELATED_OBJECTS => true,
        xPDO::OPT_HYDRATE_ADHOC_FIELDS => true,
        xPDO::OPT_VALIDATE_ON_SAVE => true,
    );
    parent :: __construct(
        'mysql:host='. FlexiConfig::$sDBHost . ";dbname=" . FlexiConfig::$sDBName . ";charset=utf8",
        FlexiConfig::$sDBUser,
        FlexiConfig::$sDBPass,
        $options,
        array (
            PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
            PDO::ATTR_PERSISTENT => false,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        )
    );
    $this->setPackage('flexiphp', dirname(dirname(dirname(dirname(__FILE__)))) . "/models");
  }
  
  public function connect($driverOptions= array ()) {
    if (! parent::connect($driverOptions)) {
      throw new Exception(__METHOD__ . ": Unable to connect");
    }
    return true;
  }

}