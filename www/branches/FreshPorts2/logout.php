<?
	# $Id: logout.php,v 1.1.2.12 2003-07-04 14:59:16 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');

	freshports_CookieClear();

	if (IsSet($_GET['origin'])) {
		$origin = $_GET['origin'];
	} else {
		$origin = '/';
	}

	if ($origin == '/index.php') {                   
		$origin = '/';                                 
	}

	header('Location: http://' . $_SERVER['HTTP_HOST'] . "/$origin");  /* Redirect browser to PHP web site */
	exit;  /* Make sure that code below does not get executed when we redirect. */
?>

<html>

<head>
<title></title>
</head

<body>
</body>
</html>
