<?
	# $Id: watch-list.php,v 1.2.2.10 2002-12-08 16:47:59 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");

	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");

	$Debug = 0;

	if ($_POST["Origin"]) {
		$Origin = AddSlashes($_POST["Origin"]);
	} else {
		$Origin = $HTTP_SERVER_VARS["HTTP_REFERER"];
	}
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
		if (!$result) {
			break;
		}
	}

	return $result;
}

function AddElementToWatchLists($db, $UserID, $ElementID, $WatchListsIDs) {
	#
	# The subselect ensures the user can only delete things from their
	# own watch list
	#
	$Debug = 0;

	#
	# make sure we don't report the duplicate entry error when adding...
	#
	$PreviousReportingLevel = error_reporting(E_ALL ^ E_WARNING);
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
		if (!$result) {
			# If this isn't s aduplicate key error, then break
			if (stristr(pg_last_error(), "Cannot insert a duplicate key") == '') {
				break;
			} else {
				$result = 1;
			}
		}
	}
	error_reporting($PreviousReportingLevel);

	return $result;
}

	if ($UserID == '') {
		# OI!  you aren't logged in!
		# just what are you doing here?
		header("Location: $Origin");  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}

	if (IsSet($_GET["ask"])) {
		require_once($_SERVER['DOCUMENT_ROOT'] . "/include/watch-lists.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/../classes/ports.php");

		freshports_Start("Watch list maintenance",
						"freshports - new ports, applications",
						"FreeBSD, index, applications, ports");
		?>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><td VALIGN=TOP>
<TABLE border="0" width="100%">
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
		$Verb       = 'removed';
		$FromTo     = 'from';
		$Object     = AddSlashes($_GET["remove"]);
	} else {
		if (IsSet($_GET["add"])) {
			$ButtonName = "Add";
			$Action     = "add";
			$Verb       = 'added';
			$FromTo      = 'to';
			$Object     = AddSlashes($_GET["add"]);
		} else {
			die("I don't know whether you are removing or adding, so I'll just stop here shall I?");
		}
	}
	$ShowCategories			= 1;
	GLOBAL $ShowDepends;
	$ShowDepends				= 1;
	$DaysMarkedAsNew			= $DaysMarkedAsNew = $GlobalHideLastChange = $ShowChangesLink = $ShowDescriptionLink = $ShowDownloadPortLink = $ShowHomepageLink = $ShowLastChange = $ShowMaintainedBy = $ShowPortCreationDate = $ShowPackageLink = $ShowShortDescription = 1;
	$HideDescription			= 1;
	$ShowEverything			= 1;
	$ShowShortDescription	= "Y";
	$ShowMaintainedBy			= "Y";
	$GlobalHideLastChange	= "Y";
	$ShowDescriptionLink		= "N";
	$port = new Port($db);
	$port->FetchByID($Object);
	echo freshports_PortDetails($port, $db, $DaysMarkedAsNew, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription, 1, '', 1, "N", 0);
?>
Please select the watch lists <?php echo $FromTo; ?> which this port will be <?php echo $Verb; ?>:
<blockquote>
		<form action="<?php echo $PostURL; ?>" method="POST" NAME=f>
		<?php	echo freshports_WatchListDDLB($db, $UserID, '', 10, TRUE, TRUE); ?>
		<br><br>
		<INPUT id=submit style="WIDTH: 85px; HEIGHT: 24px" type=submit size=29 
		   value="<?php echo $ButtonName; ?>" name=submit><br>
		<INPUT TYPE="hidden" NAME="Origin" VALUE="<?php echo $Origin?>">
		<INPUT TYPE="hidden" NAME="<?php echo $Action?>" VALUE="<?php echo $Object?>">
<?php
		if ($WatchListID) {
			echo '		<INPUT TYPE="hidden" NAME="wlid" VALUE="' . $WatchListID . '">';
		}
?>
		</form>
</blockquote>
</TD>
</tr>
</table>
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

		$Redirect = 0;
	} else {

	if (IsSet($_REQUEST["remove"])) {
		$ElementID = AddSlashes($_REQUEST["remove"]);
		if (!RemoveElementFromWatchLists($db, $UserID, $ElementID, $_REQUEST["watch_list_id"])) {
			die("adding element failed : Please try again, and if the problem persists, please contact the webmaster.");
			$Redirect = 0;
		}
	}

	if (IsSet($_REQUEST["add"])) {
		$ElementID     = AddSlashes($_GET["add"]);
#		echo 'getting stuff from post<br>';
		$ElementID     = AddSlashes($_POST["add"]);
		$WatchListsIDs = AddSlashes($_POST["watch_list_id"]);
		/*
		echo '$WatchListsIDs[0]=\'' . $WatchListsIDs . '\'<br>';
		echo 'count($WatchListsIDs)=\'' . count($WatchListsIDs) . '\'<br>';
		while (list($key, $WatchListID) = each($_REQUEST["watch_list_id"])) {
			echo "$key = $WatchListID<br>";
		}
		echo '$UserID=\'' . $UserID .'\'<br>';
		while (list($key, $WatchListID) = each($WatchListsIDs)) {
			echo "$key = $WatchListID<br>";
		}
		*/
		if (!AddElementToWatchLists($db, $UserID, $ElementID, $_REQUEST["watch_list_id"])) {
			die("adding element failed : please contact postmaster");
			$Redirect = 0;
		}
	}
	} // end if Ask

#	echo "when done, I will return to " . $HTTP_SERVER_VARS["HTTP_REFERER"];
	if ($Redirect) {
		if ($Origin) {
			if ($Debug) echo "Origin supplied is $Origin\n<BR>";
			$Origin = str_replace(" ", "&", $Origin);
		}

		if ($Debug) echo "redirecting to $Origin\n<BR>";

		header("Location: $Origin");  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}

#	phpinfo();

?>
