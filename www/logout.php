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

		$sql = "select * from user_logout($1)";
		#echo $sql;
		$result = pg_query_params($db, $sql, array($_COOKIE[USER_COOKIE_NAME]));
	}

	header("Location: /");  /* Redirect browser to PHP web site */
	exit;  /* Make sure that code below does not get executed when we redirect. */
