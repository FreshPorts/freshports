<?php
	#
	# $Id: index.php,v 1.1.2.1 2003-11-20 14:33:05 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit_record.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/latest_commits.php');

	$LatestCommits = new LatestCommits($db, $MaxNumberOfPorts);

	echo $LatestCommits->HTML;
	$Statistics->Save();
?>
