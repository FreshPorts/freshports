<?
	# $Id: missing.php,v 1.1.2.2 2001-12-29 19:03:06 dan Exp $
	#
	# Copyright (c) 2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");

	require("./include/getvalues.php");

	require("../classes/elements.php");
	require("../classes/ports.php");


function freshports_Parse404URI($REQUEST_URI, $db) {
	#
	# we have a pending 404
	# if we can parse it, then do so and return 1;
	# otherwise, return 0.

	require("missing-port.php");

	if (freshports_Parse404CategoryPort($REQUEST_URI, $db)) {
#		echo "freshports_Parse404Port found something";
		return 1;
	}

	return 0;
}


if (!freshports_Parse404URI($REQUEST_URI, $db)) {
	#
	# this is a true 404
}

?>

<? include("./include/footer.php") ?>
</body>
</html>
