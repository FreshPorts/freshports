<?
	# $Id: logout.php,v 1.1.2.6 2002-05-18 18:33:27 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");

	freshports_CookieClear();

	$origin = $_GET["origin"];

	if ($origin == "/index.php") {                   
		$origin = "/";                                 
	}

	header("Location: http://" . $_SERVER["HTTP_HOST"] . "/$origin");  /* Redirect browser to PHP web site */
	exit;  /* Make sure that code below does not get executed when we redirect. */
?>

<html>

<head>
<title></title>
</head

<body>
</body>
</html>
