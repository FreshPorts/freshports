<?php
	#
	# Copyright (c) 1998-2026 Dan Langille
	#
	# The latest commits as an HTML fragment, for showing in another page.
	# The only /backend/ feed left: the others redirect to /--/feeds/ - re #724
	#
	# optional: branch=2026Q4, flavor=new|broken|vuln
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/feed.php');

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

	freshports_Feed($db, 'html', '', NO_WATCH_LIST_ID, $BranchName, $Flavor);
