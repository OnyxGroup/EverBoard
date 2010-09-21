<?php

/**
 * Copyright (c) 2006- Facebook
 * Distributed under the Thrift Software License
 *
 * See accompanying file LICENSE or visit the Thrift site at:
 * http://developers.facebook.com/thrift/
 *
 * @package thrift
 * @author Mark Slee <mcslee@facebook.com>
 */

/**
 * Include this file if you wish to use autoload with your PHP generated Thrift
 * code. The generated code will *not* include any defined Thrift classes by
 * default, except for the service interfaces. The generated code will populate
 * values into $GLOBALS['THRIFT_AUTOLOAD'] which can be used by the autoload
 * method below. If you have your own autoload system already in place, rename your
 * __autoload function to something else and then do:
 * $GLOBALS['AUTOLOAD_HOOKS'][] = 'my_autoload_func';
 *
 * Generate this code using the -phpa Thrift generator flag.
 */

/**
 * This parses a given filename for classnames and populates
 * $GLOBALS['THRIFT_AUTOLOAD'] with key => value pairs
 * where key is lower-case'd classname and value is full path to containing file.
 *
 * @param String $filename Full path to the filename to parse
 */
function scrapeClasses($filename) {
	$fh = fopen($filename, "r");
	while ($line = fgets($fh)) {
		$matches = array();
		if ( preg_match("/^\s*class\s+([^\s]+)/", $line, $matches)) {
			if (count($matches) > 1)
				$GLOBALS['THRIFT_AUTOLOAD'][strtolower($matches[1])] = $filename;
		}
	}
}

function findFiles($dir, $pattern, &$finds) {
	if (! is_dir($dir))
		return;
	if (empty($pattern))
		$pattern = "/^[^\.][^\.]?$/";
	$files = scandir($dir);
	if (!empty($files)) {
		foreach ($files as $f) {
			if ($f == "." || $f == "..")
				continue;
			if ( is_file($dir . DIRECTORY_SEPARATOR . $f) && preg_match($pattern, $f)) {
				$finds[] = $dir . DIRECTORY_SEPARATOR . $f;
			} else if ( is_dir($dir . DIRECTORY_SEPARATOR . $f) && substr($f, 0, 1) != ".") {
				findFiles($dir . DIRECTORY_SEPARATOR . $f, $pattern, $finds);
			}
		}
	}
}

// require Thrift core
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "Thrift.php");
if (! isset($GLOBALS['THRIFT_ROOT']))
	$GLOBALS['THRIFT_ROOT'] = dirname(__FILE__);


// stuff for managing autoloading of classes
$GLOBALS['THRIFT_AUTOLOAD'] = array();
$GLOBALS['AUTOLOAD_HOOKS'] = array();
$THRIFT_AUTOLOAD =& $GLOBALS['THRIFT_AUTOLOAD'];


// only populate if not done so already
if (empty($GLOBALS['THRIFT_AUTOLOAD'])) {
	//$allLibs = glob( dirname(__FILE__) . "/**/*.php");	// oh poor winblows users can't use glob recursively
	$allLibs = array();
	findFiles( dirname(__FILE__), "/\.php$/i", $allLibs);
	if (!empty($allLibs)) {
		foreach ($allLibs as $libFile) {
			scrapeClasses($libFile);
		}
	}
}


// main autoloading
if (!function_exists('__autoload')) {
  function __autoload($class) {
    global $THRIFT_AUTOLOAD;
    $classl = strtolower($class);
    if (isset($THRIFT_AUTOLOAD[$classl])) {
      //include_once $GLOBALS['THRIFT_ROOT'].'/packages/'.$THRIFT_AUTOLOAD[$classl];
      require_once($THRIFT_AUTOLOAD[$classl]);
    } else if (!empty($GLOBALS['AUTOLOAD_HOOKS'])) {
      foreach ($GLOBALS['AUTOLOAD_HOOKS'] as $hook) {
        $hook($class);
      }
    }
  }
}


