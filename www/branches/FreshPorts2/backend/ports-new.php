<?php
	#
	# $Id: ports-new.php,v 1.1.2.1 2005-03-13 14:04:47 dan Exp $
	#
	# Copyright (c) 1998-2005 DVL Software Limited
	#
	
	DEFINE('MAX_PORTS', 30);

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	DEFINE('NEWSCACHE', $_SERVER['DOCUMENT_ROOT'] . '/../caching/cache/ports-new.rss');

	if (file_exists(NEWSCACHE) && is_readable(NEWSCACHE)) {
		readfile(NEWSCACHE);
	}

	$Statistics->Save();
?>