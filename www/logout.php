<?php

	# $Id: logout.php,v 1.3 2011-10-02 18:58:36 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');


        syslog(LOG_ERROR, 'you clicked logout');

	freshports_CookieClear();

        syslog(LOG_ERROR, 'logging out');
        if (IsSet($_COOKIE["visitor"])) {
                $visitor = $_COOKIE["visitor"];

                $sql = "UPDATE users SET cookie = 'nocookie' WHERE cookie = '" . pg_escape_string($_COOKIE["visitor"]) . "'";
#                echo $sql;
		syslog(LOG_ERROR, $sql);
		$result = pg_exec($db, $sql);
		if (!$result) {
		  syslog(LOG_ERROR, "$sql -> " . pg_errormessage());
                }
        }

	if (IsSet($_GET['origin'])) {
		$origin = $_GET['origin'];
	} else {
		$origin = '';
	}

	if ($origin == '/index.php') {                   
		$origin = '';                                 
	}

	header("Location: /$origin");  /* Redirect browser to PHP web site */
	exit;  /* Make sure that code below does not get executed when we redirect. */
