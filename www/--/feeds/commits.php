<?php
	#
	# Copyright (c) 1998-2026 Dan Langille
	#
	# The latest commits, as RSS 2.0 (default) or Atom 1.0 (format=atom) - re #721
	#
	# optional: branch=2026Q4, flavor=new|broken|vuln
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/feed.php');

	$Type = (($_GET['format'] ?? '') === 'atom') ? 'atom' : 'rss';

	$BranchName = BRANCH_HEAD;
	if (IsSet($_GET['branch']) && is_string($_GET['branch'])) {
		$BranchName = NormalizeBranch(htmlspecialchars(pg_escape_string($db, $_GET['branch'])));
	}

	$Flavor = $_GET['flavor'] ?? '';
	switch ($Flavor) {
		case '':
		case 'new':
		case 'broken':
		case 'vuln':
			break;

		default:
			$Flavor = '';
	}

	# this feed's own URL, built from what selects the feed, so it is the same for everyone sharing the cache
	$Query = array();
	if ($BranchName != BRANCH_HEAD) {
		$Query[] = 'branch=' . rawurlencode($BranchName);
	}
	if ($Flavor != '') {
		$Query[] = 'flavor=' . $Flavor;
	}
	if ($Type == 'atom') {
		$Query[] = 'format=atom';
	}
	$SelfURL = 'https://' . $_SERVER['HTTP_HOST'] . '/--/feeds/commits.php' . ($Query ? '?' . implode('&', $Query) : '');

	freshports_Feed($db, $Type, $SelfURL, NO_WATCH_LIST_ID, $BranchName, $Flavor);
