<?php
	#
	# $Id: news.php,v 1.1.2.23 2005-07-15 01:55:23 dan Exp $
	#
	# Copyright (c) 1998-2005 DVL Software Limited
	#

	$headers = apache_request_headers();
/*
	if(isset($headers['If-Modified-Since'])) {
		syslog(LOG_NOTICE, 'If-Modified-Since=' . $headers['If-Modified-Since']);
	}

	if(isset($headers['ETag'])) {
		syslog(LOG_NOTICE, 'ETag=' . $headers['ETag']);
	}
*/
	DEFINE('MAX_PORTS', 20);

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	DEFINE('NEWSCACHE', $_SERVER['DOCUMENT_ROOT'] . '/../caching/cache/news.rss');

	header('Content-type: text/xml');

	if (file_exists(NEWSCACHE) && is_readable(NEWSCACHE)) {
		readfile(NEWSCACHE);
	}

	$Statistics->Save();
?>
