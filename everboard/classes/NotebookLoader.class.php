<?php
class NotebookLoader
{
	public $notebook;
	public $notes = array();
	protected $fileManager;

	public function __construct($notebookGuid, FileManager $fileManager, $loadAllNotes = true)
	{
		$this->fileManager = $fileManager;
		$this->notebook = $this->loadNotebookInfoFile($notebookGuid);
		$notebookDir = $this->fileManager->getNotebookDir($notebookGuid);
		$noteGuids = $this->fileManager->getSubDirs($notebookDir);
		if ($loadAllNotes) {
			foreach ($noteGuids as $noteGuid) {
				$this->loadSingleNote($noteGuid);
			}
		}
	}

	public function loadSingleNote($noteGuid)
	{
		$notebookGuid = $this->notebook->guid;
		$this->notes[$noteGuid] = $this->loadNoteInfoFile($notebookGuid, $noteGuid);
		$this->notes[$noteGuid]->tagNames = $this->loadNoteTagsFile($notebookGuid, $noteGuid);
		$this->notes[$noteGuid]->content = $this->loadNoteContentFile($notebookGuid, $noteGuid, $this->notes[$noteGuid]->sanitizedContentHash);
		$this->notes[$noteGuid]->textContent = $this->getTextOnlyNoteContent($this->notes[$noteGuid]->content);
		$this->notes[$noteGuid]->contentPath = $this->fileManager->getContentPath($notebookGuid, $noteGuid, $this->notes[$noteGuid]->sanitizedContentHash, true);

		if (is_array($this->notes[$noteGuid]->resources)) {
			foreach ($this->notes[$noteGuid]->resources as $key=>$resource) {
				//We'll store the resource Filename for later
				$this->notes[$noteGuid]->resources[$key]->localFilename = $this->fileManager->getResourcePath($notebookGuid, $noteGuid, $this->notes[$noteGuid]->resources[$key]->data->sanitizedBodyHash, $this->notes[$noteGuid]->resources[$key]->guid, $this->notes[$noteGuid]->resources[$key]->mime, true);
				$this->notes[$noteGuid]->resources[$key]->absFilename = $this->fileManager->getResourcePath($notebookGuid, $noteGuid, $this->notes[$noteGuid]->resources[$key]->data->sanitizedBodyHash, $this->notes[$noteGuid]->resources[$key]->guid, $this->notes[$noteGuid]->resources[$key]->mime);
				//We'll store the Thumb file name for later
				$this->notes[$noteGuid]->resources[$key]->localThumb = $this->fileManager->getThumbPath($notebookGuid, $noteGuid, $this->notes[$noteGuid]->resources[$key]->data->sanitizedBodyHash, $this->notes[$noteGuid]->resources[$key]->guid, $this->notes[$noteGuid]->resources[$key]->mime, true);
				$this->notes[$noteGuid]->resources[$key]->absThumb = $this->fileManager->getThumbPath($notebookGuid, $noteGuid, $this->notes[$noteGuid]->resources[$key]->data->sanitizedBodyHash, $this->notes[$noteGuid]->resources[$key]->guid, $this->notes[$noteGuid]->resources[$key]->mime);
			}
		}
	}


	protected function loadNotebookInfoFile($notebookGuid)
	{
		$notebookInfoPath = $this->fileManager->getNotebookInfoPath($notebookGuid);
		if (file_exists($notebookInfoPath)) {
			$notebookInfo = file_get_contents($notebookInfoPath);
			if ($notebookInfo == null) {
				throw new Exception('Malformed Notebook Info File');
			}
			$notebookInfo = json_decode($notebookInfo);
			if ($notebookInfo == null) {
				throw new Exception('Notebook Info file could not be parsed: '.$notebookInfoPath);
			}
			return $notebookInfo;
		}
		else {
			throw new Exception('Notebook Info File could not be located at: '.$notebookInfoPath);
		}
	}

	protected function loadNoteInfoFile($notebookGuid, $noteGuid)
	{
		$noteInfoPath = $this->fileManager->getNoteInfoPath($notebookGuid, $noteGuid);
		if (file_exists($noteInfoPath)) {
			$noteInfo = file_get_contents($noteInfoPath);
			if ($noteInfo == null) {
				throw new Exception('Note Info File is empty: '.$noteInfoPath);
			}
			$noteInfo = json_decode($noteInfo);
			if ($noteInfo == null) {
				throw new Exception('Note Info File could not be parsed: '.$noteInfoPath);
			}
			return $noteInfo;
		}
		else {
			throw new Exception('Notebook Info File could not be located at: '.$noteInfoPath);
		}
	}

	protected function loadNoteContentFile($notebookGuid, $noteGuid, $noteContentHash)
	{
		$noteContentPath = $this->fileManager->getContentPath($notebookGuid, $noteGuid, $noteContentHash);
		if (file_exists($noteContentPath)) {
			$noteContent = file_get_contents($noteContentPath);
			if ($noteContent == null) {
				throw new Exception('Note Content File is empty: '.$noteContentPath);
			}
			return $noteContent;
		}
		else {
			throw new Exception('Notebook Content File could not be located at: '.$noteContentPath);
		}
	}


	protected function loadNoteTagsFile($notebookGuid, $noteGuid)
	{
		$noteTagsPath = $this->fileManager->getTagsPath($notebookGuid, $noteGuid);
		if (file_exists($noteTagsPath)) {
			$noteTags = file_get_contents($noteTagsPath);
			$noteTags = json_decode($noteTags);
			if ($noteTags == null) {
				$noteTags = array();
			}
			return $noteTags;
		}
		else {
			return array();
		}
	}
	/*Returns an html friendly version of the note*/
	protected function getTextOnlyNoteContent($content)
	{
		//Removing all tags per the API
		$allowedTags = '<a><abbr><acronym><address><area><b><bdo><big><blockquote><br><caption><center><cite><code><col><colgroup><dd><del><dfn><div><dl><dt><em><font><h1><h2><h3><h4><h5><h6><hr><i><ins><kbd><li><ol><p pre><q><s><samp><small><span><strike><strong><sub><sup><table><tbody><td><tfoot><th><thead><title><tr><tt><u><ul><var><xmp>';
		return strip_tags($content, $allowedTags);
	}

}