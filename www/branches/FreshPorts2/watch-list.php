<?
	# $Id: watch-list.php,v 1.2.2.9 2002-12-08 03:22:45 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");

	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");

	$Debug = 1;
	$origin = $_GET["origin"];

	$Origin   = $HTTP_SERVER_VARS["HTTP_REFERER"];
	$Redirect = 1;
#phpinfo();

function RemoveElementFromWatchLists($db, $UserID, $ElementID, $WatchListsIDs) {
	if ($Debug) echo "I'm removing $ElementID\n<BR>";
	#
	# The subselect ensures the user can only delete things from their
	# own watch list
	#
	while (list($key, $WatchListID) = each($WatchListsIDs)) {
		$sql = "DELETE FROM watch_list_element
		         WHERE watch_list_element.element_id     = $ElementID
		            AND watch_list.id                    = $WatchListID
		            AND watch_list.user_id               = $UserID
		            AND watch_list_element.watch_list_id = watch_list.id";

		if ($Debug) echo "<pre>$sql</pre>";
		$result = pg_exec($db, $sql);
		if (!result) {
			break;
		}
	}

	return $result;
}

function AddElementToWatchLists($db, $UserId, $ElementID, $WatchListsIDs) {
	#
	# The subselect ensures the user can only delete things from their
	# own watch list
	#
$Debug = 1;
	if ($Debug) echo "I'm adding $ElementID\n<BR>";
	while (list($key, $WatchListID) = each($WatchListsIDs)) {
		$sql = "insert into watch_list_element (watch_list_id, element_id)
		        values (
		             (SELECT id
		               FROM watch_list
		              WHERE user_id = $UserID
		                AND id      = $WatchListID), $ElementID)";
        
		if ($Debug) echo "<pre>$sql</pre>";
		$result = pg_exec($db, $sql);
		if (!result) {
			break;
		}
	}

	return $result;
}

	if ($UserID == '') {
		# OI!  you aren't logged in!
		# just what are you doing here?
		header("Location: $Origin");  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}
	if (IsSet($_GET["wlid"])) {
		$WatchListID = AddSlashes($_GET["wlid"]);
	} else {
phpinfo();
		die("Watch List ID not supplied when modifying watch list");
	}

	if (IsSet($_GET["ask"])) {
		require_once($_SERVER['DOCUMENT_ROOT'] . "/include/watch-lists.php");

		freshports_Start("Watch list maintenance",
						"freshports - new ports, applications",
						"FreeBSD, index, applications, ports");
		?>
<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><td VALIGN=TOP>
<TABLE border="0">
<TR>
	<? freshports_PageBannerText("Watch list maintenance"); ?>
</TR>
<TR><TD valign="top" width="100%">
<?php
	if ($ErrorMessage) {
		freshports_ErrorMessage("Let\'s try that again!", $ErrorMessage);
	}

	$PostURL = $_SERVER["PHP_SELF"];
	if (IsSet($_GET["remove"])) {
		$ButtonName = "Remove";
		$Action     = "remove";
		$Object     = AddSlashes($_GET["remove"]);
	} else {
		if (IsSet($_GET["add"])) {
			$ButtonName = "Add";
			$Action     = "add";
			$Object     = AddSlashes($_GET["add"]);
		} else {
			die("I don't know whether you are removing or adding, so I'll just stop here shall I?");
		}
	}

?>
		<form action="<?php echo $PostURL; ?>" method="GET" NAME=f>
		<?php	echo freshports_WatchListDDLB($db, $UserID, '', 10, TRUE, TRUE); ?>
		<br><br>
		<INPUT id=submit style="WIDTH: 85px; HEIGHT: 24px" type=submit size=29 
		   value="<?php echo $ButtonName; ?>" name=submit><br>
		<INPUT TYPE="hidden" NAME="<?php echo $Action?>" VALUE="<?php echo $Object?>">
<?php
		if ($WatchListID) {
			echo '		<INPUT TYPE="hidden" NAME="wlid" VALUE="' . $WatchListID . '">';
		}
?>
		</form>
<p>
Port details goes there
</p>
</TD>
  <TD VALIGN="top" WIDTH="*" ALIGN="center">
    <?
       include($_SERVER['DOCUMENT_ROOT'] . "/include/side-bars.php");
    ?>
 </TD>
</TABLE>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include($_SERVER['DOCUMENT_ROOT'] . "/include/footer.php") ?>
</TD></TR>
</TABLE>

</BODY>
</HTML>

		<?php
#		die("This is where I ask you what watch lists you want");
		$Redirect = 0;
	} else {

	if (IsSet($_GET["remove"])) {
		$ElementID     = AddSlashes($_GET["remove"]);
		$WatchListsIDs = AddSlashes($_POST["watch_list_id"]);
		if (!RemoveElementFromWatchLists($db, $UserID, $ElementID, $_GET["watch_list_id"])) {
			echo pg_errormessage();
			$Redirect = 0;
		}
	}

	if (IsSet($_GET["add"])) {
		$ElementID     = AddSlashes($_GET["add"]);
		$WatchListsIDs = AddSlashes($_POST["watch_list_id"]);
		if (!AddElementToWatchLists($db, $UserID, $ElementID, $_GET["watch_list_id"])) {
			echo pg_errormessage();
			$Redirect = 0;
		}
	}
	} // end if Ask

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
