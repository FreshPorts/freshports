<?
	# $Id: watch-list.php,v 1.2.2.5 2002-05-18 08:25:07 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited

    require("./include/common.php");
    require("./include/freshports.php");
    require("./include/databaselogin.php");

    require("./include/getvalues.php");

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

	if (IsSet($remove)) {
		$remove = AddSlashes($remove);
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

	if (IsSet($add)) {
		$add = AddSlashes($add);
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
