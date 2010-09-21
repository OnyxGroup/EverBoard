<?php
class WebNotebook
{

	protected $authToken;
	protected $noteStore;
	protected $fileManager;
	public $notebook;
	public $notes = array();

	public function __construct($notebook, $authToken, $noteStore, FileManager &$fileManager)
	{
		$this->fileManager = $fileManager;
		$this->noteStore = $noteStore;
		$this->notebook = $notebook;
		$this->sanitizeNotebook();
		$filter = new edam_notestore_NoteFilter();
		$filter->notebookGuid = $this->notebook->guid;
		$this->authToken = $authToken;
		$noteList = $this->noteStore->findNotes($this->authToken, $filter, 0, MAX_NOTES);
		$notes = $noteList->notes;
		foreach ($notes as $note) {
			$this->sanitizeNote($note);
			$this->notes[$note->guid] = $note;
		}
	}

	protected function sanitizeNotebook()
	{
		if ($this->notebook->serviceCreated) {
			$this->notebook->sanitizedServiceCreated = $this->notebook->serviceCreated/1000;
		}
		if ($this->notebook->serviceUpdated) {
			$this->notebook->sanitizedServiceUpdated = $this->notebook->serviceUpdated/1000;
		}
	}

	/*This function will convert some of the Note data into more PHP friendly data*/
	protected function sanitizeNote(&$note)
	{
		if ($note->contentHash) {
			$note->sanitizedContentHash = bin2hex($note->contentHash);
		}

		//the php date() function takes seconds, evernote doesn't "really" store the millis, so lets just get rid of them
		$note->sanitizedCreated = $note->created/1000;
		$note->sanitizedUpdated = $note->updated/1000;
		if ($note->deleted) {
			$note->sanitizedDeleted = $note->deleted/1000;
		}
		if ($note->attributes->subjectDate) {
			$note->attributes->subjectDate = $note->attributes->subjectDate/1000;
		}
		//loop throught the resources
		if (isset($note->resources)) {
			foreach ($note->resources as $key=>$resource) {
				if ($note->resources[$key]->data->bodyHash) {
					$note->resources[$key]->data->sanitizedBodyHash = bin2hex($note->resources[$key]->data->bodyHash);
				}
				if (isset($note->resources[$key]->recognition->bodyHash) && $note->resources[$key]->recognition->bodyHash) {
					$note->resources[$key]->recognition->sanitizedBodyHash = bin2hex($note->resources[$key]->recognition->bodyHash);
				}
				if ($note->resources[$key]->attributes->timestamp) {
					$note->resources[$key]->attributes->sanitizedTimestamp = $note->resources[$key]->attributes->timestamp/1000;
				}
				if (isset($note->hasImages)) {
					$note->hasImages =  $note->hasImages || self::isImageMime($resource->mime);
				}
				else {
					$note->hasImages = self::isImageMime($resource->mime);
				}
			}
		}
	}

	public function getNoteGuids()
	{
		return array_keys($this->notes);
	}

	public function getResourceGuids($noteGuid)
	{
		$resources = $this->notes[$noteGuid]->resources;
		$resourceGuids = array();
		if (is_array($resources)) {
			foreach ($resources as $resource) {
				$resourceGuids[] = $resource->guid;
			}
		}
		return $resourceGuids;
	}

	public function getNotebookInfoJson()
	{
		//Before we encode, let's make double sure our notebook has the sanitized values
		$this->sanitizeNotebook();
		return json_encode($this->notebook);
	}

	public function getNoteTagsJson($noteGuid)
	{
		if (!isset($this->notes[$noteGuid])) {
			return false;
		}
		if (is_array($this->notes[$noteGuid]->tagNames)) {
			return json_encode($this->notes[$noteGuid]->tagNames);
		}
	}

	public function getNoteInfoJson($noteGuid)
	{
		if (!isset($this->notes[$noteGuid])) {
			return false;
		}
		$tmpNote = $this->notes[$noteGuid];
		//Before we encode, let's make double sure our notes has the sanitized values
		$this->sanitizeNote($tmpNote);
		//We don't need to store the content here as we are putting it in an .eml file so that we can cache it
		$tmpNote->content = null;
		if (isset($tmpNote->resources)) {
			foreach ($tmpNote->resources as $key=>$resource) {
				//Also we don't need to encode the body data for the resources as we are caching it in a file
				$tmpNote->resources[$key]->data->body = null;
				//Going to set the recognition body to null as well to be sure we aren't storing too much data, you could remove this later
				//if you want this info
				$tmpNote->resources[$key]->recognition->body = null;
				//We will need to encode the hexidecimal info too, because json will write this as null if we dont'
				//$tmpNote->contentHash = bin2hex()
			}
		}

		return json_encode($tmpNote);
	}

	public function loadNoteContent($noteGuid)
	{
		//global $fileManager;
		$note = $this->notes[$noteGuid];
		if ($this->fileManager->contentNeedsUpdate($this->notebook->guid, $noteGuid, /*bin2hex($this->notes[$noteGuid]->contentHash)*/ $this->notes[$noteGuid]->sanitizedContentHash)) {
			debugIt('Content needs Load: '.$noteGuid, false);
			$this->notes[$noteGuid]->content = $this->noteStore->getNoteContent($this->authToken, $noteGuid);
		}
		debugIt('Content is cached: '.$noteGuid, false);
		/*Tags are a cheap request so we'll just take 'em without worrying about the cache*/
		$this->notes[$noteGuid]->tagNames = $this->noteStore->getNoteTagNames($this->authToken, $noteGuid);
		//We should sanitize the note whenever we alter it
		$this->sanitizeNote($this->notes[$noteGuid]);
	}

	public function loadNoteResources($noteGuid)
	{
		//global $fileManager;
		$note = $this->notes[$noteGuid];
		$this->notes[$noteGuid]->hasImages = false;
		try{
			if (isset($note->resources)) {
				foreach ($note->resources as $key=>$resource) {
					if ($this->fileManager->resourceNeedsUpdate($this->notebook->guid, $noteGuid, $this->notes[$noteGuid]->resources[$key]->data->sanitizedBodyHash, $this->notes[$noteGuid]->resources[$key]->guid, $this->notes[$noteGuid]->resources[$key]->mime)) {
						debugIt('Resource needs Load: '.$this->notes[$noteGuid]->resources[$key]->guid, false);

						//Here we aren't loading the recognition field or Alternate data field, if you want them check the api and modify the params
						$this->notes[$noteGuid]->resources[$key] = $this->noteStore->getResource($this->authToken, $resource->guid, true, false, true, false);
						//We should sanitize the note whenever we alter it
						$this->sanitizeNote($this->notes[$noteGuid]);
					}
					else {

					}
					debugIt('Resource is cached: '.$this->notes[$noteGuid]->resources[$key]->guid, false);
				}
			}

		} catch (edam_error_EDAMUserException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
			} else {
				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (edam_error_EDAMSystemException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
			} else {
				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (edam_error_EDAMNotFoundException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
			} else {
				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (Exception $e) {
			print $e->getMessage();
		}
	}

	/*This function works but is only for testing/demonstration purposes*/
	/*We don't want to use this because it loads everything, and may not be effecient*/
	public function loadNote($guid)
	{
		try{
			$this->notes[$guid] = $this->noteStore->getNote($this->authToken, $guid, true, true, true, false);
			$this->notes[$guid]->tagNames = $this->noteStore->getNoteTagNames($this->authToken, $guid);
			$this->sanitizeNote($this->notes[$guid]);
		} catch (edam_error_EDAMUserException $e)
		{
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
			} else {
				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (edam_error_EDAMSystemException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
			} else {
				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (edam_error_EDAMNotFoundException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
			} else {
				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (Exception $e) {
			print $e->getMessage();
		}

		debugIt($this->notes[$guid]);
		return $this->notes[$guid];
	}
	/*This function works but is only for testing/demonstration purposes*/
	public function loadAllNotes()
	{
		foreach ($this->notes as $guid=>$note) {
			$this->loadNote($guid);
		}
		return $this->notes;
	}

	public static function isImageMime($string)
	{
		return in_array(strtolower($string), array('image/jpeg', 'images/gif', 'image/png', 'image/jpg'));
	}

}