<?
	# $Id: watch-list.php,v 1.2.2.11 2002-12-09 20:33:03 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");

	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");

	require_once($_SERVER['DOCUMENT_ROOT'] . "/../classes/watch_list_element.php");

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
	$WatchListElement = new WatchListElement($db);
	while (list($key, $WatchListID) = each($WatchListsIDs)) {
		$result = $WatchListElement->Delete($UserID, $WatchListID, $ElementID);
		if ($result == -1) {
			break;
		}
	}

	return $result;
}

function AddElementToWatchLists($db, $UserID, $ElementID, $WatchListsIDs) {
	$Debug = 0;

	if ($Debug) echo "I'm adding $ElementID\n<BR>";
	$WatchListElement = new WatchListElement($db);
	while (list($key, $WatchListID) = each($WatchListsIDs)) {
		$result = $WatchListElement->Add($UserID, $WatchListID, $ElementID);
		if ($result == -1) {
			break;
		}
	}

	return $result;
}

	if ($User->id == '') {
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
		<?php
		if ($Action == 'add') {
			echo freshports_WatchListDDLB($db, $User->id, '', 10, TRUE, TRUE);
		} else {
			echo freshports_WatchListDDLB($db, $User->id, '', 10, TRUE, TRUE, $Object);
		}				
		?>
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

<?php
		if ($Action == 'remove') {
			echo 'NOTE: Only the watch lists which contain this port are shown above';
		}
?>
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
			if (IsSet($_REQUEST["watch_list_id"])) {
				if (RemoveElementFromWatchLists($db, $User->id, $ElementID, $_REQUEST["watch_list_id"]) == -1) {
					die("removing element failed : Please try again, and if the problem persists, please contact the webmaster: " . pg_last_error());
					$Redirect = 0;
				}
			} else {
				$WatchListElement = new WatchListElement($db);
				$WatchListElement->DeleteFromDefault($User->id, $ElementID);
			}
		}
	
		if (IsSet($_REQUEST["add"])) {
			$ElementID = AddSlashes($_REQUEST["add"]);
	
			if (IsSet($_REQUEST["watch_list_id"])) {
				if (AddElementToWatchLists($db, $User->id, $ElementID, $_REQUEST["watch_list_id"]) == -1) {
					die("adding element failed : Please try again, and if the problem persists, please contact the webmaster: " . pg_last_error());
					$Redirect = 0;
				}
			} else {
				$WatchListElement = new WatchListElement($db);
				$WatchListElement->AddToDefault($User->id, $ElementID);
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
