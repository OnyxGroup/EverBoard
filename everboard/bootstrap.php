<?php

define("EVERNOTE_LIBS", dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib");

// add ourselves to include path
ini_set("include_path", ini_get("include_path") . ":" . EVERNOTE_LIBS);

require_once("Evernote/autoload.php");
require_once("OAuth/SimpleRequest.php");
require_once $GLOBALS['THRIFT_ROOT'].'/packages/UserStore/UserStore_constants.php';


?>