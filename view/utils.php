<?php
/**
 * utils.php
 *
 * @copyright 2010, Onyx Creative Group - (onyxcreates.com)
 * @author Adrian Mummey - http://mummey.org
 * @version $Id$
**/

function isImageMime($string)
{
	return in_array(strtolower($string), array('image/jpeg', 'images/gif', 'image/png', 'image/jpg'));
}

function getCurrentNotebook($notebooks)
{
  if(!is_array($notebooks) || !count($notebooks)){
    return false;
  }

	if (isset($_REQUEST['notebookGuid'])) {
		$requestNotebookGuid = cleanGuid($_REQUEST['notebookGuid']);
		if (isset($notebooks[$requestNotebookGuid])) {
			return $notebooks[$requestNotebookGuid];
		}
	}

	foreach ($notebooks as $notebookGuid=>$the_notebook) {
		if ($the_notebook->notebook->defaultNotebook) {
			$notebook =  $notebooks[$notebookGuid];
		}
	}
	if (!$notebook) {
		$notebook = current($notebooks);
	}

	return $notebook;

}


function cleanText($str)
{
	$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $str);
	$clean = strtolower(trim($clean, '-'));
	$clean = preg_replace("/[\/_|+ -]+/", '-', $clean);

	return $clean;
}

function cleanGuid($str)
{
	$clean = preg_replace("/[^abcdef0-9-]/", '', $str);
	$clean = strtolower(trim($clean, '-'));
	//$clean = preg_replace("/[\/_|+ -]+/", '-', $clean);
	return $clean;
}

function isHexColor($str)
{
	return preg_match('/^#[a-f0-9]{6}$/i', $str);
}

function trigger404()
{
	header("HTTP/1.1 404 Not Found");
	print '';
	die();
}

function buildAjaxUrl($notebookGuid, $noteGuid)
{
	return EVERBOARD_VIEW_URL.'idea.php?notebookGuid='.$notebookGuid.'&noteGuid='.$noteGuid;
}

function buildNotebookUrl($notebookGuid)
{
	return EVERBOARD_VIEW_URL.'index.php?notebookGuid='.$notebookGuid;
}

function cleanContent($str)
{
	return strip_tags($str);
}
?>