<?php
	#
	# $Id: commit-flag.php,v 1.1.2.1 2006-12-06 16:23:51 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit_flag.php');

	$Debug = 0;

	if ($_POST["Origin"]) {
		$Origin = AddSlashes($_POST["Origin"]);
	} else {
		$Origin = $HTTP_SERVER_VARS["HTTP_REFERER"];
	}
	$Redirect = 1;
#phpinfo();

function RemoveElementFromWatchLists($db, $UserID, $ElementID, $WatchListsIDs) {
	$Debug = 0;

	if ($Debug) echo "I'm removing $ElementID\n<BR>";
	$WatchListElement = new WatchListElement($db);
	while (list($key, $WatchListID) = each($WatchListsIDs)) {
		$result = $WatchListElement->Delete($UserID, $WatchListID, $ElementID);
		if ($result == -1) {
			break;
		}
	}

	return $result;
}

function AddElementToWatchLists($db, $UserID, $ElementID, $WatchListsIDs) {
	if ($Debug) echo "I'm adding $ElementID\n<BR>";
	$WatchListElement = new WatchListElement($db);
	while (list($key, $WatchListID) = each($WatchListsIDs)) {
		$result = $WatchListElement->Add($UserID, $WatchListID, $ElementID);
		if ($result == -1) {
			break;
		}
	}

	return $result;
}

	if ($User->id == '') {
		# OI!  you aren't logged in!
		# just what are you doing here?
		header("Location: $Origin");  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}

	if (IsSet($_REQUEST['message_id'])) {
		$message_id = AddSlashes($_REQUEST['message_id']);
	} else {
		$message_id = '';
	}

	if (IsSet($_REQUEST['action'])) {
		$Action = $_REQUEST['action'];
	} else {
		$Action = '';
	}

	switch ($Action) {
		case 'add':
			if ($message_id == '') {
				die('The target for addition was not supplied');
			}
	
			pg_exec($db, 'BEGIN');
			$Error = '';
			$CommitFlag = new CommitFlag($db);
			if ($CommitFlag->Add($User->id, $message_id) == 1) {
				pg_exec($db, 'COMMIT');
			} else {
				pg_exec($db, 'ROLLBACK');
				die(pg_last_error());
			}
			break;
			
		case 'remove':
			if ($message_id == '') {
				die('The target for removal was not supplied');
			}

			pg_exec($db, 'BEGIN');
			$CommitFlag = new CommitFlag($db);
			if ($CommitFlag->Delete($User->id, $message_id) >= 0) {
				pg_exec('COMMIT');
			} else {
				pg_exec('ROLLBACK');
				die(pg_last_error());
			}
			break;
				
		default:
			die("I don't know what I was supposed to do there!");
	}

#	echo 'when done, I will return to ' . $HTTP_SERVER_VARS['HTTP_REFERER'];
	if ($Redirect) {
		if ($Origin) {
			if ($Debug) echo "Origin supplied is $Origin\n<BR>";
			$Origin = str_replace(' ', '&', $Origin);
		}

		if ($Debug) echo "redirecting to $Origin\n<BR>";

		header("Location: $Origin");  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}

#	phpinfo();

?>