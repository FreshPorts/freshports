<?
	# $Id: watch-list.php,v 1.2.2.8 2002-12-06 21:23:09 dan Exp $
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
	
	if (IsSet($_GET["wlid"])) {
		$WatchListID= AddSlashes($_GET["wlid"]);
	} else {
		die("Watch List ID not supplied when modifying watch list");
	}

	if (IsSet($_GET["remove"])) {
		$ElementID= AddSlashes($_GET["remove"]);
		if ($Debug) echo "I'm removing $ElementID\n<BR>";
		#
		# The subselect ensures the user can only delete things from their
		# own watch list
		#
		$sql = "DELETE FROM watch_list_element
		         WHERE watch_list_element.element_id     = $ElementID
		            AND watch_list.id                    = $WatchListID
		            AND watch_list.user_id               = $UserID
		            AND watch_list_element.watch_list_id = watch_list.id";

		$result = pg_exec($db, $sql);
		if (!$result) {
			echo pg_errormessage();
			$Redirect = 0;
		}
	}

	if (IsSet($_GET["add"])) {
		$ElementID = AddSlashes($_GET["add"]);
		if ($Debug) echo "I'm adding $add\n<BR>";
		$sql = "insert into watch_list_element (watch_list_id, element_id)
		        values (
		             (SELECT id
		               FROM watch_list
		              WHERE user_id = $UserID
		                AND id      = $WatchListID), $ElementID)";
        
		$result = pg_exec($db, $sql);
		if (!$result) {
			echo pg_errormessage() . $sql;
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
