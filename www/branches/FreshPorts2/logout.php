<?
	# $Id: logout.php,v 1.1.2.3 2002-01-06 16:48:12 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");

	freshports_CookieClear();

	if ($origin == "/index.php") {                   
		$origin = "/";                                 
	}

	header("Location: $origin");  /* Redirect browser to PHP web site */
	exit;  /* Make sure that code below does not get executed when we redirect. */
?>

<html>

<head>
<title></title>
</head

<body>
</body>
</html>
