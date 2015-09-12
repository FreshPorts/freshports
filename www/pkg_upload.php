<?php
	#
	# $Id: pkg_upload.php,v 1.8 2006-12-17 12:06:13 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/watch-lists.php');

	freshports_Start('Uploading pkg_info',
					$FreshPortsName . ' - new ports, applications',
					'FreeBSD, index, applications, ports');
$Debug = 1;
if ($Debug) {
#phpinfo();
#exit;
}

function StagingAlreadyInUse($UserID, $dbh) {

        $UserID = pg_escape_string($UserID);

	$Result = 1;	// yes, already in progress.

	$sql = "select WatchListStagingExists($UserID)";

	$result = pg_exec($dbh, $sql);
	if ($result && pg_numrows($result)) {
		$row = pg_fetch_array($result, 0);
		if ($row[0] == 0) {
			$Result = 0;
		}
	} else {
		echo pg_errormessage() . " sql = $sql";
	}

	return $Result;
}

	require_once($_SERVER['DOCUMENT_ROOT'] . '/pkg_process.inc');

function DisplayUploadForm($db, $UserID) {
	?>

	<P>
	You can update your watch lists from the packages database on your computer.  Use the output
	from the <CODE CLASS="code">pkg info</CODE> command as the input for this page.  FreshPorts
	will take this information, analyze it, and use that data to update your watch list.
	<SMALL><A HREF="/help.php">help</A></SMALL>
	</P>

	<p>
	You can either save the output to a file and update the file, or you can
	copy/paste the results into a form.
	</p>

	<table border="1" cellpadding="5" cellspacing="0" width="100%">
	<tr>
	<td valign="top">
	<h2>Uploading a file</h2>

	<P>Here are the steps you should perform:</P>

	<OL>

	<LI>
	<P>
	You should first issue this command on your FreeBSD computer:
	</P>

	<BLOCKQUOTE>
		<CODE CLASS="code">pkg info -qoa > mypkg_info.txt</CODE>
	</BLOCKQUOTE>

	</LI>

	<LI>
	<P>
	Then click on the <B>Choose</B> button and select the file you created in the previous step.
	<P>
	</LI>

	<LI>
	Then click on either <b>Staging</b> or <B>Upload</B>.
	</LI>

	</OL>

	<hr>


	<FORM ACTION="<? echo $_SERVER["PHP_SELF"]; ?>" METHOD="post" enctype="multipart/form-data">
		<TABLE>
			<TR><TD>The file name containing the output from step 1:</TD></TR>
			<TR><TD><INPUT TYPE="file"   NAME="pkg_info" SIZE="40" ></TD></TR>
			<TR><TD><INPUT TYPE="submit" NAME="staging"  SIZE="20" VALUE="Staging"> &lt;= Click here to go to staging area</TD></TR>
			<tr><td><hr></td></tr>

			<tr><td>Use this Watch List: 
			<?php
echo freshports_WatchListDDLB($db, $UserID); 

?>
</td></tr>
			<tr><td><input type="radio" name="replaceappend" value="replace" checked>Replace list contents<br>
                    <input type="radio" name="replaceappend" value="append">Append to list (duplicates will be removed)</td></tr>
			<tr><td><input type="submit" name="upload" size="40" value="Upload"> &lt;= Click here here to avoid staging area</td></tr>
		</TABLE>
	</FORM>

	</td>
	<td valign="top">
	<h2>Copy/Paste</h2>

	<FORM ACTION="<? echo $_SERVER["PHP_SELF"]; ?>" METHOD="post" enctype="multipart/form-data">
		<TABLE>
			<TR><TD>Paste the output of <code>pkg_info -qoa</code> here:</TD></TR>
			<tr><td><textarea name="copypaste" rows="20" cols="30"></textarea></td></tr>
			<TR><TD><INPUT TYPE="submit" NAME="staging_copypaste" SIZE="20" VALUE="Staging"> &lt;= Click here to go to staging area</TD></TR>
			<tr><td><hr></td></tr>

			<tr><td>Use this Watch List: 
			<?php
echo freshports_WatchListDDLB($db, $UserID); 

?>
</td></tr>
			<tr><td><input type="radio" name="replaceappend" value="replace" checked>Replace list contents<br>
                    <input type="radio" name="replaceappend" value="append" >Append to list (duplicates will be removed)</td></tr>
			<tr><td><input type="submit" name="upload_copypaste" size="40" value="Upload"> &lt;= Click here here to avoid staging area</td></tr>
		</TABLE>
	</FORM>


	</td>
	</tr>
	</table>

<?
}

function DisplayStagingArea($UserID, $WatchListID, $db) {

	echo '<TABLE ALIGN="center" BORDER="1" CELLSPACING="0" CELLPADDING="5">';
?>

	<TR><TD COLSPAN="4"><BIG>The following information is in your Staging Area.  To save it to a Watch List, 
		please click on the
			"Update watch list" button.</BIG> <SMALL><A HREF="/help.php">help</A></SMALL></TD></TR>

	<TR><TD COLSPAN="4">
	<FORM ACTION="<? echo $_SERVER["PHP_SELF"]; ?>" method="POST">
	<table width="100%" border="0">
	<tr><td align="center">
			<INPUT TYPE="submit" VALUE="Update watch list"  NAME="update_watch_list" SIZE="40">
			&nbsp;&nbsp;&nbsp;
 			<INPUT TYPE="submit" VALUE="Empty staging area" NAME="clear">
	</td><td align="right">
			<?php echo freshports_WatchListDDLB($db, $UserID, $WatchListID); ?>
	</td><td>
	<?php echo freshports_WatchListSelectGoButton() ?>
	</tr></table>
	</form>

	</TD></TR>
	<tr>
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

	echo '</TABLE>';
}

function ChooseWatchLists($UserID, $db) {

	echo '<TABLE width="100%" ALIGN="center" BORDER="1" CELLSPACING="0" CELLPADDING="5"><TR>';
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

	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(); ?>
<TR>
	<? echo freshports_PageBannerText("Uploading pkg_info"); ?>
<TR><TD>
<BIG>WARNING</BIG>: The system will clear out your staging area from time to time.
</TD></TR>
<TR><TD>
	<?
	$Debug = 0;
	
#	if ($Debug) phpinfo();

	# you can only be here if you are logged in!
	$visitor = $_COOKIE["visitor"];
	if (!$visitor) {
		?>
		<P>
		You must <A HREF="/login.php?origin=<?echo $_SERVER["PHP_SELF"] ?>">login</A> before you can upload your package information.
		</P>
		<?
 	} else {
		global $gDBG;
		$gDBG  = false;

		$StagingInUse       = StagingAlreadyInUse($User->id, $db);
		$DisplayStagingArea = FALSE;
		$WatchListUpdated   = FALSE;

		# if the staging area is occupied, but they are doing a straight
		# upload, then clear out the staging area!
		#

if ($Debug) echo 'at line ' . __LINE__ . '<br>';
		if (IsSet($_REQUEST["upload"]) && $StagingInUse) {
if ($Debug) echo 'at line ' . __LINE__ . '<br>';
			if (StagingAreaClear($User->id, $db)) {
				$StagingInUse = FALSE;
			} else {
				DisplayError("I was unable to empty your staging area before proceeding.");
			}
		}

		#
		# is a file name supplied?
		#

		if ($StagingInUse) {
		if ($Debug) echo 'at line ' . __LINE__ . '<br>';
			if ($Debug) echo 'staging area is in use<br>';
			$DisplayStagingArea = TRUE;
			if ($_REQUEST["update_watch_list"]) {
				$ports = $_REQUEST["ports"];
				# save these things to the watch list
				# and clear out part of the staging area.
				$WatchListID = pg_escape_string($_REQUEST['wlid']);
				if (!IsSet($WatchListID) || $WatchListID === '') {
					syslog(LOG_NOTICE, "No watch list ID was supplied.  I cannot continue.  " .
					    __FILE__ . '::' . __LINE__ . " User id = " . $User->id);
					die('No watch list ID was supplied.  I cannot continue.');
				} 

				if ($Debug) echo ' you clicked on update_watch_list';
#phpinfo();
				if (MoveStagingToWatchList($User->id, $WatchListID, $db)) {
					$DisplayStagingArea = FALSE;
					$StagingInUse       = FALSE;
					$WatchListUpdated   = TRUE;
				}
			}
if ($Debug) echo '<br>' . __LINE__ . '<br>';
			if ($_REQUEST["clear"]) {
				if ($Debug) echo " you pressed clear!<br>";
				if (StagingAreaClear($User->id, $db)) {
					$StagingInUse		= FALSE;
					$DisplayStagingArea	= FALSE;
					DisplayError("Your staging area has been cleared.");
				}
			}
			
			if ($_REQUEST['wlid']) {
if ($Debug) echo '<br>' . __LINE__ . '<br>';
				if ($Debug) echo 'you selected a list<br>';
				# they clicked on the GO button and we have to apply the 
				# watch staging area against the watch list.
				$WatchListID = pg_escape_string($_REQUEST['wlid']);
				if ($Debug) echo "setting SetLastWatchListChosen => \$wlid='$WatchListID'";
				$User->SetLastWatchListChosen($WatchListID);
			} else {
if ($Debug) echo '<br>' . __LINE__ . '<br>';
				$WatchListID = $User->last_watch_list_chosen;
				if ($Debug) echo "\$WatchListID='$WatchListID'";
				if ($WatchListID == '') {
					$WatchLists = new WatchLists($db);
					$WatchListID = $WatchLists->GetDefaultWatchListID($User->id);
					if ($Debug) echo "GetDefaultWatchListID => \$WatchListID='$WatchListID'";
				}
			}
if ($Debug) echo '<br>' . __LINE__ . '<br>';
		} else {
if ($Debug) echo 'at line ' . __LINE__ . '<br>';
			$DisplayStagingArea = FALSE;
			# are they uploading a file?
/*
The file might look like this:

Array
(
    [name] => mypkg_info.txt
    [type] => text/plain
    [tmp_name] => /tmp/phpDltNul
    [error] => 0
    [size] => 8387
)
*/			

			if (IsSet($_FILES["pkg_info"]) && count($_FILES["pkg_info"]) != 0) {
if ($Debug) echo 'at line ' . __LINE__ . '<br>';
				$Destination = "/tmp/FreshPorts.tmp_pkg_output." . $User->name;
#				$Destination = $_FILES["pkg_info"]['tmp_name'];
				if (HandleFileUpload('pkg_info', $Destination)) {
if ($Debug) echo 'at line ' . __LINE__ . '<br>';
					require_once($_SERVER['DOCUMENT_ROOT'] . '/pkg_utils.inc');
					if (IsSet($_REQUEST["upload"])) {
if ($Debug) echo 'at line ' . __LINE__ . '<br>';
						StagingAreaClear($User->id, $db);
						$PortArray = ConvertFileContentsToArray($Destination);
						if (ProcessPackages($User->id, $PortArray, $db)) {
if ($Debug) echo 'at line ' . __LINE__ . '<br>';
							# we are not using the staging list
							$WatchListID = pg_escape_string($_REQUEST['wlid']);
							if ($Debug) echo ' you clicked on update_watch_list';

							if ($_REQUEST['replaceappend'] == 'replace') {
								$Overwrite = TRUE;
							} else {
								$Overwrite = FALSE;
							}

							if (!IsSet($WatchListID) || $WatchListID === '') {
								syslog(LOG_NOTICE, "No watch list ID was supplied.  I cannot continue.  pkg_upload.php::250 " .
									"User id = " . $User->id);
								die('No watch list ID was supplied.  I cannot continue.');
							} 

							if (CopyStagingToWatchList($db, $User->id, $WatchListID, $Overwrite)) {
								$DisplayStagingArea = FALSE;
								$StagingInUse       = FALSE;
								$WatchListUpdated   = TRUE;
								if (StagingAreaClear($User->id, $db)) {
									DisplayError("Your watch list has been updated.");
								} else {
									DisplayError("Your staging area was not cleared.");
								}
							} else {
								DisplayError('OH NO! CopyStagingToWatchList failed!');
							}
						}
					} else {
if ($Debug) echo 'at line ' . __LINE__ . '<br>';
   						$PortArray = ConvertFileContentsToArray($Destination);
						if (ProcessPackages($User->id, $PortArray, $db)) {
							$DisplayStagingArea = TRUE;
							$WatchListID = $User->last_watch_list_chosen;
							if ($Debug) echo "\$WatchListID='$WatchListID'";
							if ($WatchListID == '') {
								$WatchLists = new WatchLists($db);
								$WatchListID = $WatchLists->GetDefaultWatchListID($User->id);
								if ($Debug) echo "GetDefaultWatchListID => \$WatchListID='$WatchListID'";
							}
						}
					}
				}
				# let's not leave files sitting around...
				unlink($Destination);
			} else {
			  if ($Debug) echo 'at line ' . __LINE__ . '<br>';
			  if (IsSet($_REQUEST['staging_copypaste']) && trim($_REQUEST['copypaste']) != '') {
if ($Debug) echo 'at line ' . __LINE__ . '<br>';
if ($Debug) echo '<pre>' . $_REQUEST['copypaste'] . '</pre>';
                $PortArray = ConvertStringToArray($_REQUEST['copypaste']);
				if (ProcessPackages($User->id, $PortArray, $db)) {
					$DisplayStagingArea = TRUE;
					$WatchListID = $User->last_watch_list_chosen;
					if ($Debug) echo "\$WatchListID='$WatchListID'";
					if ($WatchListID == '') {
						$WatchLists = new WatchLists($db);
						$WatchListID = $WatchLists->GetDefaultWatchListID($User->id);
						if ($Debug) echo "GetDefaultWatchListID => \$WatchListID='$WatchListID'";
					}
				}
			  } else {
			    if (IsSet($_REQUEST['upload_copypaste']) && trim($_REQUEST['copypaste']) != '') {
                  $PortArray = ConvertStringToArray($_REQUEST['copypaste']);
                  if (ProcessPackages($User->id, $PortArray, $db)) {
#                  if ($Debug) phpinfo();
					$WatchListID = pg_escape_string($_REQUEST['wlid']);
					if ($_REQUEST['replaceappend'] == 'replace') {
					  $Overwrite = TRUE;
					} else {
						$Overwrite = FALSE;
					}

                    if (CopyStagingToWatchList($db, $User->id, $WatchListID, $Overwrite)) {
				      $DisplayStagingArea = FALSE;
				      $StagingInUse       = FALSE;
				      $WatchListUpdated   = TRUE;
				      if (StagingAreaClear($User->id, $db)) {
					    DisplayError("Your watch list has been updated.");
					    } else {
					    DisplayError("Your staging area was not cleared.");
                      }
                    } else {
					  DisplayError('OH NO! CopyStagingToWatchList failed!');
                    }
				  }
			    }
			  }
            }
			
		}

if ($Debug) echo '<br>' . __LINE__ . '<br>';

		#
		# either we display the staging area, or we display the upload form.
		#
		if ($DisplayStagingArea) {
			if ($WatchListUpdated) {
				DisplayError('<BIG>Your watch list has been updated. You may wish to empty your staging area now.</BIG>');
			}
			if ($WatchListID) {
				DisplayStagingArea($User->id, $WatchListID, $db);
			} else {
				ChooseWatchLists($User->id, $db);
			}
		} else {
			DisplayUploadForm($db, $User->id);
		}
	}
	?>
</TD>
</TR>
</TABLE>
</TD>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
	<?
	echo freshports_SideBar();
	?>
  </td>

</TR>
</TABLE>

<?
echo freshports_ShowFooter();
?>

</BODY>
</HTML>
