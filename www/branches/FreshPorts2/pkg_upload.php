<?
	# $Id: pkg_upload.php,v 1.5.2.14 2002-05-18 18:00:34 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");

	require("./include/getvalues.php");

	freshports_Start("Uploading pkg_info",
					"$FreshPortsName - new ports, applications",
					"FreeBSD, index, applications, ports");
$Debug=0;
#phpinfo();
function StagingAlreadyInUse($WatchListID, $dbh) {

	$Result = 1;	// yes, already in progress.

	$sql = "select WatchListStagingExists($WatchListID)";

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

	require_once "pkg_process.inc";

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

function DisplayStagingArea($WatchListID, $db) {

	echo '<TABLE ALIGN="center" BORDER="1" CELLSPACING="0" CELLPADDING="5" 
					bordercolor="#a2a2a2" BORDERCOLORDARK="#a2a2a2" BORDERCOLORLIGHT="#a2a2a2"><TR>';
?>

	<TR><TD COLSPAN="4"><BIG>The following information is in your Staging Area.  To save it to your Watch List, please click on the
			"Update watch list" button.</BIG> <SMALL><A HREF="/help.php">help</A></SMALL></TD></TR>

	<TR><TD COLSPAN="4">
			<FORM ACTION="<? echo $_SERVER["PHP_SELF"]; ?>" method="POST">
			<P ALIGN="center">
			<INPUT TYPE="submit" VALUE="Update watch list"  NAME="submit" SIZE="40">
			&nbsp;&nbsp;&nbsp;
 			<INPUT TYPE="submit" VALUE="Empty staging area" NAME="clear">
			</P>

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
	UploadDisplayStagingResultsMatches($WatchListID, $db);
	echo '</TD>';

	echo '<TD VALIGN="top">' . "\n";
	UploadDisplayStagingResultsMatchesNo($WatchListID, $db);
	echo '</TD>';

	echo '<TD VALIGN="top">' . "\n";
	UploadDisplayStagingResultsMatchesDuplicates($WatchListID, $db);
	echo '</TD>';

	echo '<TD VALIGN="top">' . "\n";
	UploadDisplayWatchListItemsNotInStagingArea($WatchListID, $db);
	echo '</TD>';
			echo '</FORM>';

	echo '</TABLE>';
}



?>

<TABLE width="<? echo $TableWidth ?>" border="0" ALIGN="center">
<TR><TD VALIGN=TOP>
<TABLE WIDTH="100%">
<TR>
	<? freshports_PageBannerText("Uploading pkg_info"); ?>
<TR><TD>
	<?
	# you can only be here if you are logged in!
	if (!$visitor) {
		?>
		<P>
		You must <A HREF="login.php?origin=<?echo $_SERVER["PHP_SELF"] ?>">login</A> before you can upload your package information.
		</P>
		<?
 	} else {
		global $gDBG;
		$gDBG  = false;

		$StagingInUse       = StagingAlreadyInUse($WatchListID, $db);
		$DisplayStagingArea = FALSE;
		$WatchListUpdated	= FALSE;

		#
		# is a file name supplied?
		#
		if ($StagingInUse) {
			$DisplayStagingArea = TRUE;
			if ($_POST["submit"]) {
				$ports = $_POST["ports"];
				# save these things to the watch list
				# and clear out part of the staging area.
#				echo ' you clicked on submit';
				if (MoveStagingToWatchList($WatchListID, $ports, $db)) {
#					$DisplayStagingArea = FALSE;
					$StagingInUse       = FALSE;
					$WatchListUpdated   = TRUE;
				}
			}
			if ($_POST["clear"]) {
#				echo " you pressed clear!";
				if (StagingAreaClear($WatchListID, $db)) {
					$StagingInUse		= FALSE;
					$DisplayStagingArea	= FALSE;
					DisplayError("Your staging area has been cleared.");
				}
			}
		} else {
			$DisplayStagingArea = FALSE;
			if (trim($pkg_info) != '') {
				$Destination = "/tmp/FreshPorts.tmp_pkg_output.$UserName";
				if (HandleFileUpload("pkg_info", $Destination)) {
					require_once "pkg_utils.inc";
					if (ProcessPackages($WatchListID, $Destination, $db)) {
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
			DisplayStagingArea($WatchListID, $db);
		} else {
			DisplayUploadForm($pkg_info);
		}
	}
	?>
</TD>
</TR>
</TABLE>
</TD>
  <TD valign="top" width="*">
    <?
		include("./include/side-bars.php");
    ?>
 </TD>
</TR>
</TABLE>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include("./include/footer.php") ?>
</TD></TR>
</TABLE>

</BODY>
</HTML>
