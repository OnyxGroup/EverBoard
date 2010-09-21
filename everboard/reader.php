<?php
/**
 * reader.php
 *
 * @copyright 2010, Onyx Creative Group - (onyxcreates.com)
 * @author Adrian Mummey - http://mummey.org
 * @version $Id$
**/

require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . "config.php");
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . "NotebookLoader.class.php");
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . "FileManager.class.php");

/*This is more of an example file showing how to load the files into PHP objects*/
$fileManager = new FileManager(CACHE_DIR);
$notebookGuids = array_intersect($fileManager->getSubDirs(CACHE_DIR), $valid_notebooks);

$notebooks = array();
foreach($notebookGuids as $notebookGuid){
    $notebooks[$notebookGuid] = new NotebookLoader($notebookGuid, $fileManager);
}
