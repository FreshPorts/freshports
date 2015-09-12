<?php
	#
	# $Id: ports-new.php,v 1.2 2006-12-17 12:06:22 dan Exp $
	#
	# Copyright (c) 1998-2005 DVL Software Limited
	#
	
	DEFINE('MAX_PORTS', 30);

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	DEFINE('NEWSCACHE', $_SERVER['DOCUMENT_ROOT'] . '/../dynamic/caching/cache/ports-new.rss');

	if (file_exists(NEWSCACHE) && is_readable(NEWSCACHE)) {
		readfile(NEWSCACHE);
	}

	$Statistics->Save();
?>