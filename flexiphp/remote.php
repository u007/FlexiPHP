<?php
/* 
 * Handling remote call
 */

function remoteErrorHandler($errno, $errstr, $errfile, $errline) {
  switch ($errno) {
    case E_USER_ERROR:
      echo json_encode(array("status" => false, "return" => $errno,
        "msg" => "[UserErr][" . $errno . "] " . $errstr . ": " . $errline . " in file " . $errfile));
      exit(1);
      break;

    case E_USER_WARNING:
      echo json_encode(array("status" => false, "return" => $errno,
        "msg" => "[Warn][" . $errno . "] " . $errstr . ": " . $errline . " in file " . $errfile));
      exit(1);
      break;
    case E_USER_NOTICE:
      echo json_encode(array("status" => false, "return" => $errno,
        "msg" => "[Notice][" . $errno . "] " . $errstr . ": " . $errline . " in file " . $errfile));
      exit(1);
      break;

    default:
      echo json_encode(array("status" => false, "return" => $errno,
        "msg" => "[" . $errno . "] " . $errstr . ": " . $errline . " in file " . $errfile));
      exit(1);
      break;
  }

  /* Don't execute PHP internal error handler */
  return true;
}
//set_error_handler("remoteErrorHandler");

ini_set('default_socket_timeout',    120);
$oRemoteServer = FlexiRemoteServer::getRemoteServer();
$oRemoteServer->run();
exit();