<?
	# $Id: pkg_upload.php,v 1.5.2.24 2002-12-10 05:13:27 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");

	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/watch-lists.php");

	freshports_Start("Uploading pkg_info",
					"$FreshPortsName - new ports, applications",
					"FreeBSD, index, applications, ports");
$Debug=0;
#phpinfo();

function StagingAlreadyInUse($UserID, $dbh) {

	$Result = 1;	// yes, already in progress.

	$sql = "select WatchListStagingExists($UserID)";

	$result = pg_exec($dbh, $sql);
	if ($result && pg_numrows($result)) {
		$row = pg_fetch_array($result, 0);
		if (!$row[0]) {
			$Result = 0;
		}
	} else {
		echo pg_errormessage() . " sql = $sql";
	}

	return $Result;
}

	require_once($_SERVER['DOCUMENT_ROOT'] . "/pkg_process.inc");

function DisplayUploadForm($pkg_info) {
	?>

	<P>
	You can update your watch lists from the packages database on your computer.  Use the output
	from the <CODE CLASS="code">pkg_info</CODE> command as the input for this page.  FreshPorts
	will take this information, analyze it, and use that data to update your watch list.
	<SMALL><A HREF="/help.php">help</A></SMALL>
	</P>

	<P>Here are the steps you should perform:</P>

	<OL>

	<LI>
	<P>
	You should first issue this command on your FreeBSD computer:
	</P>

	<BLOCKQUOTE>
		<CODE CLASS="code">pkg_info -qoa > mypkg_info.txt</CODE>
	</BLOCKQUOTE>

	</LI>

	<LI>
	<P>
	Then click on the <B>Choose</B> button and select the file you created in the previous step.
	<P>
	</LI>

	<LI>
	Then click on <B>Upload</B>.
	</LI>

	</OL>


	<FORM ACTION="<? echo $_SERVER["PHP_SELF"]; ?>" METHOD="post" enctype="multipart/form-data">
		<TABLE>
			<TR><TD>The file name containing the output from step 1:</TD></TR>
			<TR><TD><INPUT TYPE="file"   NAME="pkg_info" SIZE="40" ></TD></TR>
			<TR><TD><INPUT TYPE="submit" NAME="upload"   SIZE="20" VALUE="Upload"></TD></TR>
		</TABLE>
	</FORM>

<?
#	<P>
#	If you prefer, you can download the <A HREF="/freshports.tgz">FreshPorts port</A> which will upload
#	the output for you.
#	</P>
}

function DisplayStagingArea($UserID, $WatchListID, $db) {

	echo '<TABLE ALIGN="center" BORDER="1" CELLSPACING="0" CELLPADDING="5" 
					bordercolor="#a2a2a2" BORDERCOLORDARK="#a2a2a2" BORDERCOLORLIGHT="#a2a2a2"><TR>';
?>

	<TR><TD COLSPAN="4"><BIG>The following information is in your Staging Area.  To save it to a Watch List, 
		please click on the
			"Update watch list" button.</BIG> <SMALL><A HREF="/help.php">help</A></SMALL></TD></TR>

	<TR><TD COLSPAN="4">
	<table width="100%" border="0"><tr><td>
			<FORM ACTION="<? echo $_SERVER["PHP_SELF"]; ?>" method="POST">
			<P ALIGN="center">
			<INPUT TYPE="submit" VALUE="Update watch list"  NAME="update_watch_list" SIZE="40">
			&nbsp;&nbsp;&nbsp;
 			<INPUT TYPE="submit" VALUE="Empty staging area" NAME="clear">
			</P>
			<td align="right">
			<?php echo freshports_WatchListDDLB($db, $UserID, $WatchListID); ?>
			</td>
			<td>
	<?php echo freshports_WatchListSelectGoButton() ?>
</tr></table>
</td>

	</TD></TR>
<?

	echo '<TD VALIGN="top"><B>Ports found from your uploaded data.</B><BR>Those marked with a W are already on your watch list.</TD>' . "\n";
	echo '<TD VALIGN="top"><B>Ports not found.</B><BR>These ports are installed on your system but could not be located within FreshPorts.  Perhaps they have
								been renamed or removed from the ports tree.  You could use the search link, locate the ports, and add them to your
								watch list manually.</TD>' . "\n";
	echo '<TD VALIGN="top"><B>Ports duplicated</B><BR>The following ports have been installed multiple times, most definitely with different versions on
										 your system.</TD>' . "\n";

	echo '<TD VALIGN="top"><B>Port from your watch lists</B><BR>These ports are on your watch list but do not appear in your pkg_info data.</TD>' . "\n";

	echo '</TR><TR>';


	echo '<TD VALIGN="top">' . "\n";
	UploadDisplayStagingResultsMatches($UserID, $WatchListID, $db);
	echo '</TD>';

	echo '<TD VALIGN="top">' . "\n";
	UploadDisplayStagingResultsMatchesNo($UserID, $db);
	echo '</TD>';

	echo '<TD VALIGN="top">' . "\n";
	UploadDisplayStagingResultsMatchesDuplicates($UserID, $WatchListID, $db);
	echo '</TD>';

	echo '<TD VALIGN="top">' . "\n";
	UploadDisplayWatchListItemsNotInStagingArea($WatchListID, $db);
	echo '</TD>';

	echo '</FORM>';

	echo '</TABLE>';
}

function ChooseWatchLists($UserID, $db) {

	echo '<TABLE width="100%" ALIGN="center" BORDER="1" CELLSPACING="0" CELLPADDING="5" 
					bordercolor="#a2a2a2" BORDERCOLORDARK="#a2a2a2" BORDERCOLORLIGHT="#a2a2a2"><TR>';
?>

	<TR><TD colspan="3"><BIG>Your staging area contains your uploaded information.  Please choose a watch list, and click on Go.
		 <SMALL><A HREF="/help.php">help</A></SMALL></TD></TR>

	<TR><TD>
	<table width="100%" border="0"><tr><td>
			<FORM ACTION="<? echo $_SERVER["PHP_SELF"]; ?>" method="POST">
			<P ALIGN="center">
 			<INPUT TYPE="submit" VALUE="Empty staging area" NAME="clear">
 			</td><td align="right">
			<?php echo freshports_WatchListDDLB($db, $UserID); ?>
			</td>
			<td>
	<?php echo freshports_WatchListSelectGoButton() ?>

</td></tr></table>
	</TD></TR>
<?
	echo '</FORM>';

	echo '</TABLE>';
}

?>

<TABLE width="<? echo $TableWidth ?>" border="0" ALIGN="center">
<TR><TD VALIGN=TOP>
<TABLE WIDTH="100%" border="0">
<TR>
	<? freshports_PageBannerText("Uploading pkg_info"); ?>
<TR><TD>
<BIG>WARNING</BIG>: The system will clear out your staging areas from time to time.
</TD><TR>
<TR><TD>
	<?
	# you can only be here if you are logged in!
	$visitor = $_COOKIE["visitor"];
	if (!$visitor) {
		?>
		<P>
		You must <A HREF="login.php?origin=<?echo $_SERVER["PHP_SELF"] ?>">login</A> before you can upload your package information.
		</P>
		<?
 	} else {
		global $gDBG;
		$gDBG  = false;

		$StagingInUse       = StagingAlreadyInUse($User->id, $db);
		$DisplayStagingArea = FALSE;
		$WatchListUpdated	  = FALSE;

		#
		# is a file name supplied?
		#

		if ($StagingInUse) {
			$DisplayStagingArea = TRUE;
			if ($_POST["update_watch_list"]) {
				$ports = $_POST["ports"];
				# save these things to the watch list
				# and clear out part of the staging area.
				$WatchListID = AddSlashes($_POST["watch_list_id"]);
#				echo ' you clicked on update_watch_list';
				if (MoveStagingToWatchList($User->id, $WatchListID, $ports, $db)) {
#					$DisplayStagingArea = FALSE;
					$StagingInUse       = FALSE;
					$WatchListUpdated   = TRUE;
				}
			}
			if ($_POST["clear"]) {
#				echo " you pressed clear!";
				if (StagingAreaClear($User->id, $db)) {
					$StagingInUse			= FALSE;
					$DisplayStagingArea	= FALSE;
					DisplayError("Your staging area has been cleared.");
				}
			}
			
			if ($_POST["watch_list_select_x"] && $_POST["watch_list_select_y"]) {
				# they clicked on the GO button and we have to apply the 
				# watch staging area against the watch list.
				$WatchListID = AddSlashes($_POST["watch_list_id"]);
			}
		} else {
			$DisplayStagingArea = FALSE;
			if (trim($_FILES["pkg_info"]) != '') {
				$Destination = "/tmp/FreshPorts.tmp_pkg_output.$UserName";
				if (HandleFileUpload("pkg_info", $Destination)) {
					require_once($_SERVER['DOCUMENT_ROOT'] . "/pkg_utils.inc");
					if (ProcessPackages($User->id, $Destination, $db)) {
						$DisplayStagingArea = TRUE;
					}
				}
			}
		}

		#
		# either we display the staging area, or we display the upload form.
		#
		if ($DisplayStagingArea) {
			if ($WatchListUpdated) {
				DisplayError("<BIG>Your watch list has been updated. You may wish to empty your staging area now.</BIG>");
			}
			if ($WatchListID) {
				DisplayStagingArea($User->id, $WatchListID, $db);
			} else {
				ChooseWatchLists($User->id, $db);
			}
		} else {
			DisplayUploadForm($pkg_info);
		}
	}
	?>
</TD>
</TR>
</TABLE>
</TD>
  <TD VALIGN="top" WIDTH="*" ALIGN="center">
    <?
		require_once($_SERVER['DOCUMENT_ROOT'] . "/include/side-bars.php");
    ?>
 </TD>
</TR>
</TABLE>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? require_once($_SERVER['DOCUMENT_ROOT'] . "/include/footer.php") ?>
</TD></TR>
</TABLE>

</BODY>
</HTML>
