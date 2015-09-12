<?
//////////////////////////////////////////////////////////////////////
//
// phpPolls - A voting booth for PHP3 (configuration module)
//
// Copyright (c) 1999 Till Gerken (tig@skv.org)
//
//////////////////////////////////////////////////////////////////////

$poll_scriptVersion = "1.0.3";			// current version

$poll_baseURL = "/phpPolls";	// base path of script
						// NO slash at the end!

$poll_mySQL_host = "localhost";			// hostname of MySQL database
$poll_mySQL_user = "phppolls";			// MySQL username
$poll_mySQL_pwd = "proflex957";			// MySQL password

$poll_dbName = "fpphppolls";			// database to store the tables in

$poll_descTableName = "vbooth_desc";		// name of table that keeps poll descriptions
$poll_dataTableName = "vbooth_data";		// name of table that keeps all poll data
$poll_IPTableName = "vbooth_ip";		// name of table that keeps IP locking info
$poll_logTableName = "vbooth_log";		// name of table that keeps logging info

$poll_maxOptions = 10;				// maximal number of options allowed

$poll_logging = 0;				// do you want phpPolls to log every vote?
$poll_IPLocking = 0;				// do you want phpPolls to additionally use IP locking?
$poll_IPLockTimeout = 600;			// number of seconds for one IP to be locked

$poll_resultBarHeight = 12;			// height in pixels of percentage bar in result table
$poll_resultBarScale = 1;			// scale of result bar (in multiples of 100 pixels)
$poll_resultBarFile = "phpPollBar.gif";		// name of the image that contains the bar

$poll_setCookies = 1;				// 1 - sets a cookie not allowing a user to vote
						//     twice for the same poll
						// 0 - does not set a cookie nor does it check for one

$poll_warnCheaters = 0;				// 1 - if a cookie is present and a user tries
						//     to vote for this poll, a warning is issued
						// 0 - silently ignores the vote

$poll_usePersistentConnects = 0;		// 1 - uses persistent connects to the database
						//     (always leaves a link open)
						// 0 - uses open/close sequences for database
						//     accesses

$poll_cookiePrefix = "phpPoll";			// prefix for cookie names

//////////////////////////////////////////////////////////////////////

?>
