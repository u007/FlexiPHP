<?php
/* 
 * Handling remote call
 */
ini_set('default_socket_timeout',    120); 
$oRemoteServer = FlexiRemoteServer::getRemoteServer();
$oRemoteServer->run();
die();