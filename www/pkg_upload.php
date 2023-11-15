<?php
	#
	# $Id: pkg_upload.php,v 1.8 2006-12-17 12:06:13 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/watch-lists.php');

	if (IN_MAINTENANCE_MODE) {
                header('Location: /' . MAINTENANCE_PAGE, TRUE, 307);
	}

	$Title = 'Uploading pkg_info';
	freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');
$Debug = 0;
if ($Debug) {
#phpinfo();
#exit;
}

function StagingAlreadyInUse($UserID, $dbh) {

        $UserID = pg_escape_string($dbh, $UserID);

	$Result = 1;	// yes, already in progress.

	$sql = "select WatchListStagingExists($1)";

	$result = pg_query_params($dbh, $sql, array($UserID));
	if ($result && pg_num_rows($result)) {
		$row = pg_fetch_array($result, 0);
		if ($row[0] == 0) {
			$Result = 0;
		}
	} else {
		echo pg_last_error($dbh) . " sql = $sql";
	}

	return $Result;
}

	require_once($_SERVER['DOCUMENT_ROOT'] . '/pkg_process.inc');

function DisplayUploadForm($db, $UserID) {
	?>

	<P>
	You can update your watch lists from the packages database on your computer.  Use the output
	from the <code class="code">pkg info</code> command as the input for this page.  FreshPorts
	will take this information, analyze it, and use that data to update your watch list.
	<SMALL><a href="/help.php">help</a></SMALL>
	</P>

	<p>
	You can either save the output to a file and update the file, or you can
	copy/paste the results into a form.
	</p>

	<table class="pkg-upload-info fullwidth bordered">
	<tr>
	<td>
	<h2>Uploading a file</h2>

	<P>Here are the steps you should perform:</P>

	<ol>

	<li>
	<P>
	You should first issue this command on your FreeBSD computer:
	</P>

	<blockquote>
		<code class="code">pkg info -qoa > mypkg_info.txt</code>
	</blockquote>

	</li>

	<li>
	<P>
	Then click on the <B>Choose</B> button and select the file you created in the previous step.
	<P>
	</li>

	<li>
	Then click on either <b>Staging</b> or <B>Upload</B>.
	</li>

	</ol>

	<hr>


	<FORM ACTION="<?php echo $_SERVER["PHP_SELF"]; ?>" METHOD="post" enctype="multipart/form-data">
		<table>
			<tr><td>The file name containing the output from step 1:</td></tr>
			<tr><td><INPUT TYPE="file"   NAME="pkg_info" SIZE="40" ></td></tr>
			<tr><td><INPUT TYPE="submit" NAME="staging"  SIZE="20" VALUE="Staging"> &lt;= Click here to go to staging area<hr></td></tr>

			<tr><td>Use this Watch List: 
			<?php
echo freshports_WatchListDDLB($db, $UserID); 

?>
</td></tr>
			<tr><td><input type="radio" name="replaceappend" value="replace" checked>Replace list contents<br>
                    <input type="radio" name="replaceappend" value="append">Append to list (duplicates will be removed)</td></tr>
			<tr><td><input type="submit" name="upload" size="40" value="Upload"> &lt;= Click here here to avoid staging area</td></tr>
		</table>
	</FORM>

	</td>
	<td>
	<h2>Copy/Paste</h2>

	<FORM ACTION="<?php echo $_SERVER["PHP_SELF"]; ?>" METHOD="post" enctype="multipart/form-data">
		<table>
			<tr><td>Paste the output of <code>pkg info -qoa</code> here:</td></tr>
			<tr><td><textarea name="copypaste" rows="20" cols="30"></textarea></td></tr>
			<tr><td><INPUT TYPE="submit" NAME="staging_copypaste" SIZE="20" VALUE="Staging"> &lt;= Click here to go to staging area<hr></td></tr>

			<tr><td>Use this Watch List: 
			<?php
echo freshports_WatchListDDLB($db, $UserID); 

?>
</td></tr>
			<tr><td><input type="radio" name="replaceappend" value="replace" checked>Replace list contents<br>
                    <input type="radio" name="replaceappend" value="append" >Append to list (duplicates will be removed)</td></tr>
			<tr><td><input type="submit" name="upload_copypaste" size="40" value="Upload"> &lt;= Click here here to avoid staging area</td></tr>
		</table>
	</FORM>


	</td>
	</tr>
	</table>

<?php
}

function DisplayStagingArea($UserID, $WatchListID, $db) {

	echo '<table class="pkg-upload-info fullwidth bordered">';
?>

	<tr><td colspan="4">The following information is in your Staging Area.  To save it to a Watch List,
		please click on the
			"Update watch list" button. <SMALL><a href="/help.php">help</a></SMALL></td></tr>

	<tr><td colspan="4">
	<form class="pkg-upload-controls" ACTION="<?php echo $_SERVER["PHP_SELF"]; ?>" method="POST">
			<INPUT TYPE="submit" VALUE="Update watch list"  NAME="update_watch_list" SIZE="40">
 			<INPUT TYPE="submit" VALUE="Empty staging area" NAME="clear">
			<?php echo freshports_WatchListDDLB($db, $UserID, $WatchListID); ?>
			<?php echo freshports_WatchListSelectGoButton() ?>
	</form>
	</td></tr>
	<tr>
<?php

	echo '<td><B>Ports found from your uploaded data.</B><br>Those marked with a W are already on your watch list.</td>' . "\n";
	echo '<td><B>Ports not found.</B><br>These ports are installed on your system but could not be located within FreshPorts.  Perhaps they have
								been renamed or removed from the ports tree.  You could use the search link, locate the ports, and add them to your
								watch list manually.</td>' . "\n";
	echo '<td><B>Ports duplicated</B><br>The following ports have been installed multiple times, most definitely with different versions on
										 your system.</td>' . "\n";

	echo '<td><B>Port from your watch lists</B><br>These ports are on your watch list but do not appear in your pkg info data.</td>' . "\n";

	echo '</tr><tr>';


	echo '<td>' . "\n";
	UploadDisplayStagingResultsMatches($UserID, $WatchListID, $db);
	echo '</td>';

	echo '<td>' . "\n";
	UploadDisplayStagingResultsMatchesNo($UserID, $db);
	echo '</td>';

	echo '<td>' . "\n";
	UploadDisplayStagingResultsMatchesDuplicates($UserID, $WatchListID, $db);
	echo '</td>';

	echo '<td>' . "\n";
	UploadDisplayWatchListItemsNotInStagingArea($WatchListID, $db);
	echo '</td>';

	echo '</tr>';
	echo '</table>';
}

function ChooseWatchLists($UserID, $db) {

	echo '<table class="pkg-upload-info fullwidth bordered"><tr>';
?>

	<tr><td>Your staging area contains your uploaded information.  Please choose a watch list, and click on Go.
		 <SMALL><a href="/help.php">help</a></SMALL></td></tr>

	<tr><td>
			<FORM class="pkg-upload-controls" ACTION="<?php echo $_SERVER["PHP_SELF"]; ?>" method="POST">
 			<INPUT TYPE="submit" VALUE="Empty staging area" NAME="clear">
			<?php echo freshports_WatchListDDLB($db, $UserID); ?>
			<?php echo freshports_WatchListSelectGoButton() ?>
			</FORM>
	</td></tr>

	</table>
	<?php
}

?>

	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(); ?>
<tr>
	<?php echo freshports_PageBannerText("Uploading pkg info"); ?>
<tr><td>
<span class="element-details">WARNING</span>: The system will clear out your staging area from time to time.
</td></tr>
<tr><td>
	<?php
	$Debug = 0;
	
#	if ($Debug) phpinfo();

	# you can only be here if you are logged in!
	$visitor = $_COOKIE[USER_COOKIE_NAME] ?? null;
	if (!$visitor) {
		?>
		<P>
		You must <a href="/login.php">login</a> before you can upload your package information.
		</P>
		<?php
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
			if (IsSet($_REQUEST["update_watch_list"])) {
#phpinfo();
#				$ports = $_REQUEST["ports"];
				# save these things to the watch list
				# and clear out part of the staging area.
				$WatchListID = pg_escape_string($db, $_REQUEST['wlid']);
				if (!IsSet($WatchListID) || $WatchListID === '') {
					syslog(LOG_NOTICE, "No watch list ID was supplied.  I cannot continue.  " .
					    __FILE__ . '::' . __LINE__ . " User id = " . $User->id);
					die('No watch list ID was supplied.  I cannot continue.');
				} 

				if ($Debug) echo ' you clicked on update_watch_list';
				if (MoveStagingToWatchList($User->id, $WatchListID, $db)) {
					$DisplayStagingArea = FALSE;
					$StagingInUse       = FALSE;
					$WatchListUpdated   = TRUE;
				}
			}
if ($Debug) echo '<br>' . __LINE__ . '<br>';
			if (IsSet($_REQUEST["clear"])) {
				if ($Debug) echo " you pressed clear!<br>";
				if (StagingAreaClear($User->id, $db)) {
					$StagingInUse		= FALSE;
					$DisplayStagingArea	= FALSE;
					DisplayError("Your staging area has been cleared.");
				}
			}
			
			if (IsSet($_REQUEST['wlid'])) {
if ($Debug) echo '<br>' . __LINE__ . '<br>';
				if ($Debug) echo 'you selected a list<br>';
				# they clicked on the GO button and we have to apply the 
				# watch staging area against the watch list.
				$WatchListID = pg_escape_string($db, $_REQUEST['wlid']);
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
							$WatchListID = pg_escape_string($db, $_REQUEST['wlid']);
							if ($Debug) echo ' you clicked on update_watch_list';

							if ($_REQUEST['replaceappend'] == 'replace') {
								$Overwrite = TRUE;
							} else {
								$Overwrite = FALSE;
							}

							if (!IsSet($WatchListID) || $WatchListID === '') {
								syslog(LOG_NOTICE, "No watch list ID was supplied.  I cannot continue.  pkg_upload.php::" . __LINE__ .
									" User id = " . $User->id);
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
					$WatchListID = pg_escape_string($db, $_REQUEST['wlid']);
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
				DisplayError('Your watch list has been updated. You may wish to empty your staging area now.');
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
</td>
</tr>
</table>
</td>

  <td class="sidebar">
	<?php
	echo freshports_SideBar();
	?>
  </td>

</tr>
</table>

<?php
echo freshports_ShowFooter();
?>

</BODY>
</HTML>
