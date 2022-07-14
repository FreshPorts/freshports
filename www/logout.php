<?php

	# $Id: logout.php,v 1.3 2011-10-02 18:58:36 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');


	freshports_CookieClear();

	if (IsSet($_COOKIE[USER_COOKIE_NAME])) {
		$visitor = $_COOKIE[USER_COOKIE_NAME];

		$sql = "UPDATE users SET cookie = 'nocookie' WHERE cookie = '" . pg_escape_string($db, $_COOKIE[USER_COOKIE_NAME]) . "'";
		#echo $sql;
		$result = pg_exec($db, $sql);
	}

	header("Location: /");  /* Redirect browser to PHP web site */
	exit;  /* Make sure that code below does not get executed when we redirect. */
