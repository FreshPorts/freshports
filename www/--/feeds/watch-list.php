<?php
	#
	# Copyright (c) 1998-2026 Dan Langille
	#
	# A watch list, as RSS 2.0 (default) or Atom 1.0 (format=atom) - re #721
	#
	# id=<watch list token>; no login needed, the token is enough
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/watch-lists.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/feed.php');

	if (IN_MAINTENANCE_MODE) {
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Retry-After: 300');
		exit;
	}

	$Token = $_GET['id'] ?? '';
	if (!is_string($Token) || $Token === '') {
		# no feed asked for: the list of a user's feeds is on this page
		header('Location: /backend/watch-list.php');
		exit;
	}

	$WatchListID = freshports_WatchListVerifyToken($db, pg_escape_string($db, $Token));
	if ($WatchListID == '') {
		syslog(LOG_ERR, __FILE__ . '::' . __LINE__ .
			' watch list token requested by ' . $_SERVER['REMOTE_ADDR'] .
			' not found ' . "'$Token'");
		header('HTTP/1.1 404 NOT FOUND');
		exit;
	}

	$Type = (($_GET['format'] ?? '') === 'atom') ? 'atom' : 'rss';

	$SelfURL = 'https://' . $_SERVER['HTTP_HOST'] . '/--/feeds/watch-list.php?id=' . rawurlencode($Token) . ($Type == 'atom' ? '&format=atom' : '');

	freshports_Feed($db, $Type, $SelfURL, $WatchListID);
