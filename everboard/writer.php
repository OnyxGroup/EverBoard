<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "bootstrap.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "config.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . "EvernoteConnect.class.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . "WebNotebook.class.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . "FileManager.class.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . "Image.class.php";

set_time_limit( TIME_LIMIT );
//Needed for the timestamp stuff
ini_set("precision", "20");

//print "Notes for " . $user->username . ":<br/>";
//File Manager takes care of any interactions with the file/cache system
$fileManager = new FileManager(CACHE_DIR);

//Connection will just connect to evernote and return the notebooks
$connection = new EvernoteConnect();
$notebooks = array();
if (!empty($connection->notebooks)) {

	foreach ($connection->notebooks as $notebook) {
	 	debugIt($notebook->guid.' '.$notebook->name, false);
		if (in_array($notebook->guid, $valid_notebooks)) {
			$notebooks[$notebook->guid] = new WebNotebook($notebook, $connection->authToken, $connection->noteStore, $fileManager);
		}
	}
}


//Get the notebooks guids from evernote
$newNotebookGuids = array_keys($notebooks);
//Scan the filesystem for notebook guids directories
$oldNotebookGuids = $fileManager->getSubDirs($fileManager->getCacheDir());
//remove any old notebooks from the file system
$fileManager->cleanNotebooks($newNotebookGuids, $oldNotebookGuids);
$thumbs = array();

//Main loop, let's go through each notebook and process it
foreach ($notebooks as $notebookGuid=>$notebook) {
	//The current Note guids
	$newGuids = $notebook->getNoteGuids();
	//Check filesystem for note guids
	$oldGuids = $fileManager->getSubDirs($fileManager->getNotebookDir($notebookGuid));
	//Remove any unused notes directories
	$fileManager->cleanNotes($notebookGuid, $newGuids, $oldGuids);

	//Write Notebook Info here
	$fileManager->writeNotebookInfo($notebookGuid, $notebook->getNotebookInfoJson());

	foreach ($notebook->notes as $noteGuid=>$note) {
		$fileManager->writeNoteInfo($notebookGuid, $noteGuid, $notebook->getNoteInfoJson($noteGuid));
		//Load any uncached note content from Evernote
		$notebook->loadNoteContent($noteGuid);

		//The current content hash (there will only be one)
		$newContentHashes = array(/*bin2hex($notebook->notes[$noteGuid]->contentHash)*/ $notebook->notes[$noteGuid]->sanitizedContentHash);
		//Load the old Content hashes from the filesystem
		$oldContentHashes = $fileManager->getSubDirs($fileManager->trailingslashit($fileManager->getNotebookDir($notebookGuid)).CONTENT_SUB_DIR);
		//Remove any unused note content guid directories
		$fileManager->cleanNoteContents($notebookGuid, $noteGuid, $newContentHashes, $oldContentHashes);

		//Write the note contents
		$fileManager->writeNoteContent($notebookGuid, $noteGuid, /*bin2hex($notebook->notes[$noteGuid]->contentHash)*/ $notebook->notes[$noteGuid]->sanitizedContentHash, $notebook->notes[$noteGuid]->content);
		//Write tags here
		$fileManager->writeNoteTags($notebookGuid, $noteGuid, $notebook->getNoteTagsJson($noteGuid));

		//Lets grab resource (images, files) from Evernote, but only if we need to. This can be an expensive request
		$notebook->loadNoteResources($noteGuid);
		//Get current Resource guids
		$newResourceGuids = $notebook->getResourceGuids($noteGuid);
		//Get old guids from file system
		$oldResourceGuids = $fileManager->getSubDirs($fileManager->trailingslashit($fileManager->getNoteDir($notebookGuid, $noteGuid)).RESOURCES_SUB_DIR);
		//Remove any unused resources
		$fileManager->cleanResources($notebookGuid, $noteGuid, $newResourceGuids, $oldResourceGuids);
		if (isset($notebook->notes[$noteGuid]->resources)) {
			foreach ($notebook->notes[$noteGuid]->resources as $resource) {
				//Write the resources to files
				$fileManager->writeResource($notebookGuid, $noteGuid, $resource->guid, /*bin2hex($resource->data->bodyHash)*/ $resource->data->sanitizedBodyHash, $resource->mime, $resource->data->body);
				//Check if we want to create a thumb
				//We will do the thumb processing later so we don't have to delay the connection too much.

				if (defined('RESIZE_IMAGES') && RESIZE_IMAGES) {
					if ($fileManager->thumbNeedsUpdate($notebookGuid, $noteGuid, $resource->data->sanitizedBodyHash, $resource->guid, $resource->mime) && $fileManager->isImageMime($resource->mime)) {

						$thumbs[] = array('path'=>$fileManager->getResourcePath($notebookGuid, $noteGuid, $resource->data->sanitizedBodyHash, $resource->guid, $resource->mime), 'mime'=>$resource->mime, 'notebookGuid'=>$notebookGuid, 'noteGuid'=>$noteGuid, 'resourceGuid'=>$resource->guid, 'sanitizedBodyHash'=>$resource->data->sanitizedBodyHash);

					}
				}
			}
		}
	}
}

//After we have synched NOW lets process the thumbs;
foreach ($thumbs as $thumb) {
	$image = new Image($thumb['path'], $thumb['mime']);
	$thumbData = $image->getProcessedImage(THUMB_WIDTH, THUMB_HEIGHT);
	if ($thumbData) {
		$fileManager->writeThumb($thumb['notebookGuid'], $thumb['noteGuid'], $thumb['resourceGuid'], $thumb['sanitizedBodyHash'], $thumb['mime'], $thumbData);
	}
}

function debugIt($data, $krumo=true)
{
	if (defined('DEBUG_IT') && DEBUG_IT) {
		if ($krumo) {
			if (function_exists('krumo')) {
				krumo($data);
			}
		}
		else {
			print '<pre>'.$data.'</pre>';
		}
	}
}
