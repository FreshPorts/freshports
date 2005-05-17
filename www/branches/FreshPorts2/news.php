<?php
	#
	# $Id: news.php,v 1.1.2.19 2005-05-17 22:47:34 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#
	
	DEFINE('MAX_PORTS', 20);

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	DEFINE('NEWSCACHE', $_SERVER['DOCUMENT_ROOT'] . '/../caching/cache/news.rss');

	header('Content-type: application/rss+xml');

	if (file_exists(NEWSCACHE) && is_readable(NEWSCACHE)) {
		readfile(NEWSCACHE);
	}

	$Statistics->Save();
?>
