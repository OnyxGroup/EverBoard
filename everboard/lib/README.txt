REQUIREMENTS:
=============

PEAR Requirements:
	HTTP_Request      1.4.3


DEVELOPER NOTES:
================
Files in this directory are required libraries for accessing Evernote service
using PHP.  Files are organized in a manner similar to how PEAR files are layed
out. It is sufficient for PHP developer to simply add path to this directory to
their include_path ini variable. This can be done by editing php.ini file, or
calling ini_set("include_path", "<full path to this directory>");

A convinient way to get access to all dependent classes is to employ
autoload.php - see example in bootstrap.php.  It is sufficient to include
bootstrap.php at the beginning of every script (see index.php for example).

Files:
	Evernote/Thrift.php	- Thrift core library. Required for making calls to Evernote service.
	Evernote/autoload.php - Autoloader for all Evernote classes.
	Evernote/transport/* - Thrift transport libraries (from Thrift's source distribution)
	Evernote/protocol/* - Thrift protocol libraries (from Thrift's source distribution)
	Evernote/packages/* - Evernote specific classes. These are used to make calss to Evernote service and deal with Evernote data.
	HTTP/* - HTTP related classes (these are currently used by OAuth_SimpleRequest.php)
	OAuth/* - OAuth related classes.
	OAuth/SimpleRequest.php - class that allows OAuth Consumers to establish authenticated connection to Evernote service.
