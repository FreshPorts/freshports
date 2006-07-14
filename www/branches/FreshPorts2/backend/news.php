<?php
	#
	# $Id: news.php,v 1.1.2.1 2006-07-14 13:42:42 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/newsfeed.php'); 

	$format = basename($_SERVER['PHP_SELF'], '.php');

	echo newsfeed($db, $format);
?>