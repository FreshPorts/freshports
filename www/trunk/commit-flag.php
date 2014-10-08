<?php
	#
	# $Id: commit-flag.php,v 1.3 2013-01-29 16:02:57 dan Exp $
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
		$Origin = pg_escape_string($_POST["Origin"]);
	} else {
		$Origin = $_SERVER["HTTP_REFERER"];
	}
	$Redirect = 1;
#phpinfo();

	if ($User->id == '') {
		# OI!  you aren't logged in!
		# just what are you doing here?
		header("Location: $Origin");  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}

	if (IsSet($_REQUEST['message_id'])) {
		$message_id = pg_escape_string($_REQUEST['message_id']);
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