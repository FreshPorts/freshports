<?php
	#
	# $Id: index.php,v 1.1.2.3 2004-12-07 00:31:25 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit_record.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/latest_commits.php');

	# JUST IN CASE THIS ISN'T SET ALREADY
	if (!IsSet($MaxNumberOfPortsLong)) {
		$MaxNumberOfPortsLong = 100;
	}
	$NumCommits = $MaxNumberOfPortsLong;

	if (IsSet($_REQUEST["numcommits"])) {
		$NumCommits = intval(AddSlashes($_REQUEST["numcommits"]));

		$NumCommits = min($MaxNumberOfPortsLong, max(10, $NumCommits));
	}

	$LatestCommits = new LatestCommits($db);
	$LatestCommits->SetMaxNumberOfPorts($NumCommits);
	$LatestCommits->SetDaysMarkedAsNew ($DaysMarkedAsNew);
	$LatestCommits->CreateHTML();

	echo $LatestCommits->HTML;
	$Statistics->Save();
?>
