<?
	# $Id: watch-list.php,v 1.2.2.7 2002-05-22 04:30:29 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited

    require($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
    require($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
    require($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");

    require($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");

	$Debug = 0;
	$origin = $_GET["origin"];

	$Origin = $HTTP_SERVER_VARS["HTTP_REFERER"];
	$Redirect = 1;

	if ($UserID == '') {
		# OI!  you aren't logged in!
		# just what are you doing here?
		header("Location: $Origin");  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}

	if (IsSet($_GET["remove"])) {
		$remove = AddSlashes($_GET["remove"]);
		if ($Debug) echo "I'm removing $remove\n<BR>";
		$sql = "delete from watch_list_element
			     where watch_list_id = $WatchListID
				   and element_id    = $remove";

		$result = pg_exec($db, $sql);
		if (!$result) {
			echo pg_errormessage();
			$Redirect = 0;
		}
	}

	if (IsSet($_GET["add"])) {
		$add = AddSlashes($_GET["add"]);
		if ($Debug) echo "I'm adding $add\n<BR>";
		$sql = "insert into watch_list_element (watch_list_id, element_id) values
								($WatchListID, $add)";
		$result = pg_exec($db, $sql);
		if (!$result) {
			echo pg_errormessage();
			$Redirect = 0;
		}
		
	}

#	echo "when done, I will return to " . $HTTP_SERVER_VARS["HTTP_REFERER"];
	if ($Redirect) {
		if ($origin) {
			if ($Debug) echo "origin supplied is $origin\n<BR>";
			$Origin = str_replace(" ", "&", $origin);
		}

		if ($Debug) echo "redirecting to $Origin\n<BR>";

		header("Location: $Origin");  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}

#	phpinfo();

?>
