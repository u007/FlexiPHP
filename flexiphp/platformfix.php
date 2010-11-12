<?php
//===BEGIN FIXING IIS PROBLEM===//
if (! isset($_SERVER['DOCUMENT_ROOT'])){
  if (isset($_SERVER['SCRIPT_FILENAME'])) {
    $_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0-strlen($_SERVER['PHP_SELF'])));
  }
}
if (! isset($_SERVER['DOCUMENT_ROOT'])){
  if (isset($_SERVER['PATH_TRANSLATED'])){
    $_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0-strlen($_SERVER['PHP_SELF'])));
  }
}

if (!isset($_SERVER['REQUEST_URI'])) {
  $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],1 );
  if (isset($_SERVER['QUERY_STRING'])) {
    $_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING'];
  }
}
//===END FIXING IIS PROBLEM===//

?>
