<?php 
/**
 * idea.php
 *
 * @copyright 2010, Onyx Creative Group - (onyxcreates.com)
 * @author Adrian Mummey - http://mummey.org
 * @version $Id$
**/

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'utils.php';

require_once( EVERBOARD_CODE_PATH.'config.php');
require_once( EVERBOARD_CODE_PATH.'classes/NotebookLoader.class.php');
require_once( EVERBOARD_CODE_PATH.'classes/FileManager.class.php');

if(isset($_REQUEST['notebookGuid'])){
  $notebookGuid = cleanGuid($_GET['notebookGuid']);
}
if(isset($_REQUEST['noteGuid'])){
  $noteGuid = cleanGuid($_GET['noteGuid']);
}

if(!isset($notebookGuid) || !isset($noteGuid)){
  trigger404();
}

$fileManager = new FileManager(CACHE_DIR);

try{
  $notebook = new NotebookLoader($notebookGuid, $fileManager, false);
  $notebook->loadSingleNote($noteGuid);
  $note = $notebook->notes[$noteGuid];
} catch(Exception $e){
  trigger404();
}

print $note->textContent;
?>
