<?php
/**
 * config.php
 *
 * @copyright 2010, Onyx Creative Group - (onyxcreates.com)
 * @author Adrian Mummey - http://mummey.org
 * @version $Id$
**/

/**
 * Some of this information will be provided to you by Evernote when
 * you register for use of the API.
 *
 */
define("USERNAME", "");
define("PASSWORD", "");
 
// These are your API idents, you will need to request them from Evernote
// https://www.evernote.com/about/developer/api/
// You will need the key for "Client Application"
define("CONSUMER_KEY", "");
define("CONSUMER_SECRET", "");

//If you are using the sandbox mode you will need to change this to sandbox.evernote.com
define("SP_HOSTNAME","https://www.evernote.com");
define("REQUEST_TOKEN_URL", SP_HOSTNAME . "/oauth");
define("ACCESS_TOKEN_URL", SP_HOSTNAME . "/oauth");
define("AUTHORIZATION_URL_BASE", SP_HOSTNAME . "/OAuth.action");
define("NOTE_STORE_HOST", "www.evernote.com");
define("NOTE_STORE_PORT", "80");
define("NOTE_STORE_PROTO", "https");
define("NOTE_STORE_URL", "edam/note/");
define("USER_STORE_HOST", "www.evernote.com");
define("USER_STORE_PORT", "80");
define("USER_STORE_PROTO", "https");
define("USER_STORE_URL", "edam/user");

//Evernote requires a number for the maximum notes (per notebook) to grab
define("MAX_NOTES", 500);
define("DEBUG_IT", true);
define('TIME_LIMIT', 600);

if ( !defined('ABS_PATH') )
	define('ABS_PATH', dirname(__FILE__) . '/');

//This is the directory where all the files will be stored. This directory should be accessible to
//your webserver
define('CACHE_DIR', ABS_PATH.'cache');
define('CONTENT_SUB_DIR', 'content');
define('TAGS_SUB_DIR', 'tags');
define('RESOURCES_SUB_DIR', 'resources');
define('THUMB_SUB_DIR', 'thumb');

//When true, old cache files are deleted automatically,
//probably a good idea to leave this false while testing, 
//lest a bug wipes out some of your data
define('DELETE_OLD', true);

//Using the reader/writer paradigm, its better to resize the images beforehand on the server
define('RESIZE_IMAGES', true);
define('THUMB_WIDTH', 332);
//If you want your thumb to just be locked to a specific width or height, set the other dimension to zero
//For the Masonry we are using fixed width images
define('THUMB_HEIGHT', 0);

$valid_notebooks = array();
