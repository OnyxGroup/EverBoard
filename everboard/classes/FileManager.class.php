<?php

class FileManager
{

	protected $cacheDir;

	public function __construct($cacheDir)
	{
		$this->cacheDir = $cacheDir;
		if (!is_dir($this->cacheDir)) {
			mkdir($this->cacheDir);
			if (!is_dir($this->cacheDir)) {
				throw new Exception('The Cache Directory could not be created at: '.$this->cacheDir);
			}
		}
	}

	public function getCacheDir()
	{
		return $this->cacheDir;
	}

	public function getSubDirs($directory)
	{
		if (!is_dir($directory)) {
			return array();
		}
		$dirs = scandir($directory);

		foreach ($dirs as $key=>$dir) {
			if ($dir == '.' || $dir == '..' || !is_dir($this->trailingslashit($directory).$dir)) {
				unset($dirs[$key]);
			}
		}
		return $dirs;
	}

	public function resourceNeedsUpdate($notebookGuid, $noteGuid, $resourceHash, $resourceGuid, $mimeType)
	{
		if ($notebookGuid && $noteGuid && $resourceHash) {
			return !file_exists($this->getResourcePath($notebookGuid, $noteGuid, $resourceHash, $resourceGuid, $mimeType));
		}
		else {
			throw new Exception('Check cache failed, bad parameters');
		}
		return true;
	}

	public function contentNeedsUpdate($notebookGuid, $noteGuid, $noteContentHash)
	{
		if ($notebookGuid && $noteGuid && $noteContentHash) {
			return !file_exists($this->getContentPath($notebookGuid, $noteGuid, $noteContentHash));
		}
		else {
			throw new Exception('Check cache failed, bad parameters');
		}
		return true;
	}

	public function writeNotebookInfo($notebookGuid, $info)
	{
		$notebookDir = $this->getNotebookDir($notebookGuid);
		if (!is_dir($notebookDir)) {
			if (!mkdir($notebookDir, 0777, true)) {
				throw new Exception('Could not create directory: '.$notebookDir);
			}
		}
		return file_put_contents($this->getNotebookInfoPath($notebookGuid), $info);
	}

	public function thumbNeedsUpdate($notebookGuid, $noteGuid, $resourceHash, $resourceGuid, $mimeType)
	{
		if ($notebookGuid && $noteGuid && $resourceHash) {
			return !file_exists($this->getThumbPath($notebookGuid, $noteGuid, $resourceHash, $resourceGuid, $mimeType));
		}
		else {
			throw new Exception('Check cache failed, bad parameters');
		}
		return true;
	}

	public function writeNoteInfo($notebookGuid, $noteGuid, $info)
	{
		$noteDir = $this->getNoteDir($notebookGuid, $noteGuid);
		if (!is_dir($noteDir)) {
			if (!mkdir($noteDir, 0777, true)) {
				throw new Exception('Could not create directory: '.$noteDir);
			}
		}
		return file_put_contents($this->getNoteInfoPath($notebookGuid, $noteGuid), $info);
	}

	public function writeNoteTags($notebookGuid, $noteGuid, $tags)
	{
		$noteTagsDir = $this->getNoteTagDir($notebookGuid, $noteGuid);
		debugIt($noteTagsDir, false);
		if (!is_dir($noteTagsDir)) {
			if (!mkdir($noteTagsDir, 0777, true)) {
				throw new Exception('Could not create directory: '.$noteTagsDir);
			}
		}
		return file_put_contents($this->getTagsPath($notebookGuid, $noteGuid), $tags);
	}

	public function writeNoteContent($notebookGuid, $noteGuid, $noteContentHash, $content)
	{
		if ($this->contentNeedsUpdate($notebookGuid, $noteGuid, $noteContentHash) && $content) {
			$noteContentDir = $this->getNoteContentDir($notebookGuid, $noteGuid, $noteContentHash);
			if (!is_dir($noteContentDir)) {
				if (!mkdir($noteContentDir, 0777, true)) {
					throw new Exception('Could not create directory: '.$noteContentDir);
				}
			}
			return file_put_contents($this->getContentPath($notebookGuid, $noteGuid, $noteContentHash), html_entity_decode(htmlentities($content, ENT_COMPAT, 'utf-8')));
		}
		return true;
	}

	public function writeResource($notebookGuid, $noteGuid, $resourceGuid, $resourceHash, $mimeType, $resource)
	{
		if ($this->resourceNeedsUpdate($notebookGuid, $noteGuid, $resourceHash, $resourceGuid, $mimeType) && $resource) {
			$resourceDir = $this->getResourceDir($notebookGuid, $noteGuid, $resourceGuid);
			if (!is_dir($resourceDir)) {
				if (!mkdir($resourceDir, 0777, true)) {
					throw new Exception('Could not create directory: '.$resourceDir);
				}
			}
			//Might need to check resource and mime Type
			$resourcePath = $this->getResourcePath($notebookGuid, $noteGuid, $resourceHash, $resourceGuid, $mimeType);
			if ($resourcePath) {
				return file_put_contents($resourcePath, $resource);
			}
		}
		return true;
	}

	public function writeThumb($notebookGuid, $noteGuid, $resourceGuid, $resourceHash, $mimeType, $thumb)
	{
		if ($this->thumbNeedsUpdate($notebookGuid, $noteGuid, $resourceHash, $resourceGuid, $mimeType) && $thumb) {
			$thumbDir = $this->getThumbDir($notebookGuid, $noteGuid, $resourceGuid);
			if (!is_dir($thumbDir)) {
				if (!mkdir($thumbDir, 0777, true)) {
					throw new Exception('Could not create directory: '.$thumbDir);
				}
			}
			//Might need to check resource and mime Type
			$thumbPath = $this->getThumbPath($notebookGuid, $noteGuid, $resourceHash, $resourceGuid, $mimeType);
			if ($thumbPath) {
				return file_put_contents($thumbPath, $thumb);
			}
		}
		return true;
	}

	public function cleanNotebooks($newGuids, $oldGuids)
	{
		$removeDirs = array_diff($oldGuids, $newGuids);
		debugIt($removeDirs);
		foreach ($removeDirs as $rmdir) {
			$this->deleteAll($this->getNotebookDir($rmdir));
		}
	}

	public function cleanNotes($notebookGuid, $newGuids, $oldGuids)
	{
		$removeDirs = array_diff($oldGuids, $newGuids);
		debugIt($removeDirs);
		foreach ($removeDirs as $rmdir) {
			$this->deleteAll($this->getNoteDir($notebookGuid, $rmdir));
		}
	}

	/*We use the hash as the folder name rather than GUID*/
	public function cleanNoteContents($notebookGuid, $noteGuid, $newHashes, $oldHashes)
	{
		$removeDirs = array_diff($oldHashes, $newHashes);
		debugIt($removeDirs);
		foreach ($removeDirs as $rmdir) {
			$this->deleteAll($this->getNoteContentDir($notebookGuid, $noteGuid, $rmdir));
		}
	}

	/*We use the hash as the folder name rather than GUID*/
	public function cleanResources($notebookGuid, $noteGuid, $newGuids, $oldGuids)
	{
		$removeDirs = array_diff($oldGuids, $newGuids);
		debugIt($removeDirs);
		foreach ($removeDirs as $rmdir) {
			$this->deleteAll($this->getResourceDir($notebookGuid, $noteGuid, $rmdir));
		}
	}

	public function getNotebookDir($notebookGuid, $relative = false)
	{
		$root = ($relative)?(''):($this->trailingslashit($this->getCacheDir()));
		return $root.$notebookGuid;
	}

	public function getNoteDir($notebookGuid, $noteGuid, $relative = false)
	{
		return $this->trailingslashit($this->getNotebookDir($notebookGuid, $relative)).$noteGuid;
	}

	public function getNoteContentDir($notebookGuid, $noteGuid, $noteContentHash, $relative = false)
	{
		return $this->trailingslashit($this->getNoteDir($notebookGuid, $noteGuid, $relative)).CONTENT_SUB_DIR.'/'.$noteContentHash;
	}

	public function getNoteTagDir($notebookGuid, $noteGuid, $relative = false)
	{
		return $this->trailingslashit($this->getNoteDir($notebookGuid, $noteGuid, $relative)).TAGS_SUB_DIR;
	}

	public function getResourceDir($notebookGuid, $noteGuid, $resourceGuid, $relative = false)
	{
		return $this->trailingslashit($this->getNoteDir($notebookGuid, $noteGuid, $relative)).RESOURCES_SUB_DIR.'/'.$resourceGuid;
	}

	public function getThumbDir($notebookGuid, $noteGuid, $resourceGuid, $relative = false)
	{
		return $this->trailingslashit($this->getNoteDir($notebookGuid, $noteGuid, $relative)).RESOURCES_SUB_DIR.'/'.$resourceGuid.'/'.THUMB_SUB_DIR;
	}

	/*Start Paths*/
	public function getNotebookInfoPath($notebookGuid, $relative = false)
	{
		return $this->trailingslashit($this->getNotebookDir($notebookGuid, $relative)).$notebookGuid.'.json';
	}

	public function getNoteInfoPath($notebookGuid, $noteGuid, $relative = false)
	{
		return $this->trailingslashit($this->getNoteDir($notebookGuid, $noteGuid, $relative)).$noteGuid.'.json';
	}

	public function getResourcePath($notebookGuid, $noteGuid, $resourceHash, $resourceGuid, $mimeType, $relative = false)
	{
		$extension = $this->getMimeExtension($mimeType);
		if ($extension == false) {
			return false;
		}
		return $this->trailingslashit($this->getResourceDir($notebookGuid, $noteGuid, $resourceGuid, $relative)).$resourceHash.'.'.$extension;
	}

	public function getContentPath($notebookGuid, $noteGuid, $noteContentHash, $relative = false)
	{
		$filename = 'content.enml';
		return $this->trailingslashit($this->getNoteContentDir($notebookGuid, $noteGuid, $noteContentHash, $relative)).$filename;
	}

	public function getTagsPath($notebookGuid, $noteGuid, $relative = false)
	{
		$filename = 'tags.json';
		return $this->trailingslashit($this->getNoteTagDir($notebookGuid, $noteGuid, $relative)).$filename;
	}

	public function getThumbPath($notebookGuid, $noteGuid, $resourceHash, $resourceGuid, $mimeType, $relative = false)
	{
		$thumbsize = '';
		if (defined('THUMB_WIDTH')) {
			$thumbsize .= THUMB_WIDTH.'w';
		}
		if (defined('THUMB_HEIGHT')) {
			if (THUMB_HEIGHT) {
				$thumbsize .= ($thumbsize)?('x'.THUMB_HEIGHT.'h'):(THUMB_HEIGHT.'h');
			}
		}
		$thumbsize = ($thumbsize)?('-'.$thumbsize):($thumbsize);

		$extension = $this->getMimeExtension($mimeType);
		if ($extension == false) {
			return false;
		}
		return $this->trailingslashit($this->getThumbDir($notebookGuid, $noteGuid, $resourceGuid, $relative)).$resourceHash.$thumbsize.'.'.$extension;
	}

	public function trailingslashit($string)
	{
		if ( '/' != substr($string, -1)) {
			$string .= '/';
		}
		return $string;
	}

	public function getMimeExtension($mimeType)
	{
		switch (strtolower($mimeType)) {
		case 'image/jpg':
		case 'image/jpeg':
			return 'jpg';
			break;
		case 'images/gif':
			return 'gif';
			break;
		case 'image/png':
			return 'png';
			break;
		case 'application/pdf':
			return 'pdf';
			break;
		default:
			return false;
		}
	}

	public function isImageMime($string)
	{
		return in_array(strtolower($string), array('image/jpeg', 'images/gif', 'image/png', 'image/jpg'));
	}

	protected function deleteAll($directory, $empty = false)
	{
		debugIt('Removing: '.$directory, false);
		/*********REMOVE THIS AFTER TESTING**/
		if (DELETE_OLD) {
			/************************************/
			if (substr($directory, -1) == "/") {
				$directory = substr($directory, 0, -1);
			}

			if (!file_exists($directory) || !is_dir($directory)) {
				return false;
			} elseif (!is_readable($directory)) {
				return false;
			} else {
				$directoryHandle = opendir($directory);

				while ($contents = readdir($directoryHandle)) {
					if ($contents != '.' && $contents != '..') {
						$path = $directory . "/" . $contents;

						if (is_dir($path)) {
							$this->deleteAll($path);
						} else {
							unlink($path);
						}
					}
				}

				closedir($directoryHandle);

				if ($empty == false) {
					if (!rmdir($directory)) {
						return false;
					}
				}

				return true;
			}
		}/* end if*/
	}
}