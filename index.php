<?php

/*
 * This is an example of how flexiphp can be initialise via several lines of codes and config.
 * It is assuming that the parent framework start the session, therefore session will not
 * be handled within flexicontroller...
 * @author James
 * @version 1.0
 * @url http://www.mercstudio.com
 */

require_once(dirname(__FILE__) . "/config.inc.php");
$oFlexi->run($oFlexi->getRequest("mod"), $oFlexi->getRequest("method"));

