<?
	# $Id: logout.php,v 1.1.2.14 2006-11-28 20:51:01 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	freshports_CookieClear();

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
?>

<html>

<head>
<title></title>
</head

<body>
</body>
</html>
