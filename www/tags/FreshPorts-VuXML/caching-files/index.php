<?php
	#
	# $Id: index.php,v 1.1.2.2 2004-01-06 13:45:53 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit_record.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/latest_commits.php');

	$LatestCommits = new LatestCommits($db);
	$LatestCommits->SetMaxNumberOfPorts($MaxNumberOfPorts);
	$LatestCommits->SetDaysMarkedAsNew ($DaysMarkedAsNew);
	$LatestCommits->CreateHTML();

	echo $LatestCommits->HTML;
	$Statistics->Save();
?>
