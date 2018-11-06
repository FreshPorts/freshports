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
	if ($BranchName === 'quarterly') {
		# from https://secure.php.net/manual/en/function.date.php
		# n Numeric representation of a month, without leading zeros 1 through 12
		$BranchName = date('Y') . 'Q' . (floor((date('n') - 1) / 3) + 1);
	}
	
	echo $BranchName . '<br>';

	$Flavor = '';
	if (IsSet($_REQUEST['flavor'])) {
		$Flavor = htmlspecialchars($_REQUEST['flavor']);
	}

	if (IsSet($_REQUEST['flavour'])) {
		$Flavor = htmlspecialchars($_REQUEST['flavour']);
	}

	$OrderBy = '';
	$Where   = '';
	if (!empty($Flavor)) {
	  switch($Flavor) {
	    case 'new':
	    case 'broken':
	    case 'vulnerable':
	    	break;

	    default:
	      syslog(LOG_ERR, "Invalid value for Flavor: '$Flavor'");
	      $Flavor = null;
	  }
	}

	echo newsfeed($db, strtoupper($format), NO_WATCH_LIST_ID, $BranchName, $Flavor); #$OrderBy, $Where);
