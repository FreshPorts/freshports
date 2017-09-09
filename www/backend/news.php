<?php
	#
	# $Id: news.php,v 1.2 2006-12-17 12:06:22 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/newsfeed.php');

	$format = basename($_SERVER['PHP_SELF'], '.php');

	if (IsSet($_REQUEST['branch'])) {
		$BranchName = htmlspecialchars($_REQUEST['branch']);
	} else {
		$BranchName = BRANCH_HEAD;
	}

	echo newsfeed($db, strtoupper($format), NO_WATCH_LIST_ID, $BranchName);
