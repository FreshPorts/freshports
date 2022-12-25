<?php
	#
	# $Id: watch-list.php,v 1.4 2013-01-29 16:02:57 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/watch_list_element.php');

	if (IN_MAINTENANCE_MODE) {
                header('Location: /' . MAINTENANCE_PAGE, TRUE, 307);
	}

	$Debug = 0;

	if (IsSet($_POST["Origin"])) {
		$Origin = pg_escape_string($db, $_POST["Origin"]);
	} else {
		$Origin = $_SERVER["HTTP_REFERER"] ?? '/';
	}
	$Redirect = 1;
#phpinfo();

function RemoveElementFromWatchLists($db, $UserID, $ElementID, $WatchListsIDs) {
	$Debug = 0;

	if ($Debug) echo "I'm removing $ElementID\n<BR>";
	$WatchListElement = new WatchListElement($db);
	foreach ($WatchListsIDs as $key => $WatchListID) {
		$result = $WatchListElement->Delete($UserID, $WatchListID, $ElementID);
		if ($result == -1) {
			break;
		}
	}

	return $result;
}

function AddElementToWatchLists($db, $UserID, $ElementID, $WatchListsIDs) {
	if ($Debug) echo "I'm adding $ElementID\n<BR>";
	$WatchListElement = new WatchListElement($db);
	foreach ($WatchListsIDs as $key => $WatchListID) {
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

	if (IsSet($_REQUEST["ask"])) {
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/watch-lists.php');
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');

		$Title = 'Watch list maintenance';
		freshports_Start($Title,
						$TItle,
						'FreeBSD, index, applications, ports');
		?>

	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(); ?>
<TR>
	<? echo freshports_PageBannerText("Watch list maintenance"); ?>
</TR>
<TR><td class="content">
<?php
		if ($ErrorMessage) {
			echo freshports_ErrorMessage("Let\'s try that again!", $ErrorMessage);
		}
	
		$PostURL = $_SERVER["PHP_SELF"];
		if (IsSet($_REQUEST["remove"])) {
			$ButtonName = "Update";
			$Action     = "remove";
			$Verb       = 'removed';
			$FromTo     = 'from';
			$Object     = pg_escape_string($db, $_REQUEST["remove"]);
		} else {
			if (IsSet($_REQUEST["add"])) {
				$ButtonName = "Update";
				$Action     = "add";
				$Verb       = 'added';
				$FromTo     = 'to';
				$Object     = pg_escape_string($db, $_REQUEST["add"]);
			} else {
				die("I don't know whether you are removing or adding, so I'll just stop here shall I?");
			}
		}

		if ($Object == '') {
			die('I have no idea what I\'m supposed to add or remove here...');
		}
		$port = new Port($db);
		$port->FetchByElementID($Object, $User->id);

		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/port-display.php');

		$port_display = new port_display($db, $User);
		$port_display->SetDetailsReports();

		$port_display->SetPort($port);

		$Port_HTML = $port_display->Display();
		
		$HTML = $port_display->ReplaceWatchListToken($port->{'onwatchlist'}, $Port_HTML, $port->{'element_id'});

		echo $HTML;
?>
Please select the watch lists which should contain this port:
<blockquote>
		<form action="<?php echo $PostURL; ?>" method="POST" NAME=f>
		<?php
		echo freshports_WatchListDDLB($db, $User->id, '', 10, TRUE, TRUE, $Object);
		?>
		<br><br>
		<INPUT id=submit style="WIDTH: 85px; HEIGHT: 24px" type=submit size=29 
		   value="<?php echo $ButtonName; ?>" name="submit"><br>
		<INPUT TYPE="hidden" NAME="Origin" VALUE="<?php echo $Origin?>">
		<INPUT TYPE="hidden" NAME="Update" VALUE="<?php echo $Object?>">
<?php
		if ($WatchListID) {
			echo '		<INPUT TYPE="hidden" NAME="wlid" VALUE="' . $WatchListID . '">';
		}
?>
		</form>
</blockquote>

<?php
		if ($Action == 'remove') {
?>
NOTES
<ul>
<li>'+' indicates that the port is on this watch list.
<li>'*' indicates a default watch list.
<li>the watch lists which are selected are those upon which the port appears
</ul>
<?php
		}
?>
</TD>
</tr>
</table>
  <td class="sidebar">
  <?
  echo freshports_SideBar();
  ?>
  </td>

</TABLE>

<?
echo freshports_ShowFooter();
?>

</BODY>
</HTML>

<?php
		$Redirect = 0;
	} else {
		if (IsSet($_REQUEST['Update'])) {
			pg_exec($db, 'BEGIN');
			$Error = '';
			$ElementID = pg_escape_string($db, $_REQUEST['Update']);
			$WatchListElement = new WatchListElement($db);

			if ($Debug) echo "userid = '$User->id' and ElementID = '$ElementID'<br>";

			if ($WatchListElement->DeleteElementFromWatchLists($User->id, $ElementID) == -1) {
				$Error = 'removing element failed : Please try again, and if the problem persists, please contact the webmaster: ' . pg_last_error($db);
			}
			if ($Debug) {
				echo "Error is '$Error'<br>";

				if (IsSet($_REQUEST['wlid'])) {
					echo 'yes, it is set<br>';
				} else {
					echo 'no, there is nothing set!<br>';
					phpinfo();
				}
			}

			if ($Error == '' && IsSet($_REQUEST['wlid'])) {

				if ($Debug) echo "userid = $User->id and ElementID = $ElementID <br>";
				if (AddElementToWatchLists($db, $User->id, $ElementID, $_REQUEST['wlid']) == -1) {
					$Error = 'adding element failed : Please try again, and if the problem persists, please contact the webmaster: ' . pg_last_error($db);
				}
			}

			if ($Error == '') {
				pg_exec($db, 'COMMIT');
			} else {
				pg_exec($db, 'ROLLBACK');
				die($Error);
			}
		} else {
			if (IsSet($_REQUEST['add'])) {
				pg_exec($db, 'BEGIN');
				$Error = '';
				$ElementID = pg_escape_string($db, $_REQUEST['add']);
				if ($ElementID == '') {
					die('The target for addition was not supplied');
				}
	
				$WatchListElement = new WatchListElement($db);
				if ($WatchListElement->AddToDefault($User->id, $ElementID) == 1) {
					pg_exec($db, 'COMMIT');
				} else {
					pg_exec($db, 'ROLLBACK');
					die(pg_last_error($db));
				}
			} else {
				if (IsSet($_REQUEST['remove'])) {
					pg_exec($db, 'BEGIN');
					$ElementID = pg_escape_string($db, $_REQUEST['remove']);
					if ($ElementID == '') {
						die('The target for removal was not supplied');
					}

					$WatchListElement = new WatchListElement($db);
					if ($WatchListElement->DeleteFromDefault($User->id, $ElementID) >= 0) {
						pg_exec($db, 'COMMIT');
					} else {
						pg_exec($db, 'ROLLBACK');
						die(pg_last_error($db));
					}
				} else {
					die("I don't know what I was supposed to do there!");
				}
			}
		}
	} // end if Ask

#	echo 'when done, I will return to ' . $Origin;
	if ($Redirect) {
		if ($Origin) {
			if ($Debug) echo "Origin supplied is $Origin\n<BR>";
			$Origin = str_replace(' ', '&', $Origin);
		}

		if ($Debug) echo "redirecting to $Origin\n<BR>";

		header("Location: $Origin");  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}

#	phpinfo();

?>
