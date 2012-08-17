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
  
  public function connect($driverOptions= array (), array $options= array()) {
    if (! parent::connect($driverOptions)) {
      throw new Exception(__METHOD__ . ": Unable to connect");
    }
    return true;
  }
  
  /**
  * Parses parameter bindings in SQL prepared statements.
  *
  * @param string $sql A SQL prepared statement to parse bindings in.
  * @param array $bindings An array of parameter bindings to use for the replacements.
  * @return string The SQL with the binding placeholders replaced.
  */
  public function parseInsertOrUpdateBindings($sql, $bindings) {
    if (!empty($sql) && !empty($bindings)) {
      reset($bindings);
      $bound = array();
      while (list ($k, $param)= each($bindings)) {
        if (!is_array($param)) {
          $v= $param;
          $type= $this->getPDOType($param);
          $bindings[$k]= array(
              'value' => $v,
              'type' => $type
          );
        } else {
          $v= $param['value'];
          $type= $param['type'];
        }
        if (!$v) {
          switch ($type) {
            case PDO::PARAM_INT:
              $v= '0';
              break;
            case PDO::PARAM_BOOL:
              $v= '0';
              break;
            default:
              break;
          }
        }
        
        if (!is_int($k) || substr($k, 0, 1) === ':') {
            $pattern= '/' . $k . '\b/';
            if ($type > 0) {
              $v= $this->quote($v, $type);
              $v = str_replace("\\\\$", "\\$", $v);
            } else {
              $v= 'NULL';
            }
          $bound[$pattern] = $v;
        } else {
          $parse= create_function('$d,$v,$t', 'return $t > 0 ? $d->quote($v, $t) : \'NULL\';');
          $sql= preg_replace("/(\?)/e", '$parse($this,$bindings[$k][\'value\'],$type);', $sql, 1);
        }
      }
      
      if (!empty($bound)) {
         $sql= preg_replace(array_keys($bound), array_values($bound), $sql);
      }
      //echo "<br/>\nSQL: " . $sql;
    }
    return $sql;
  }

}