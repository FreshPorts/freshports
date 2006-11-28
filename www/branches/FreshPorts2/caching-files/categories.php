<?php
	#
	# $Id: categories.php,v 1.1.2.5 2006-11-28 21:13:43 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	$sql = "select CategoryStatsUpdate()";

	$result = pg_exec($db, $sql);
	if (!$result) {
	   print pg_errormessage() . "<br>\n";
	   exit;
	}
?>
