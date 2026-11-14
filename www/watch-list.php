<?php
	#
	# $Id: watch-list.php,v 1.4 2013-01-29 16:02:57 dan Exp $
	#
	# Copyright (c) 1998-2026 Dan Langille
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/watch_list_element.php');

	if (IN_MAINTENANCE_MODE) {
                header('Location: /' . MAINTENANCE_PAGE, TRUE, 307);
                exit;
	}

	$Debug = 0;

	if (IsSet($_POST["Origin"])) {
		$Origin = pg_escape_string($db, $_POST["Origin"]);
	} else {
		$Origin = $_SERVER["HTTP_REFERER"] ?? '/';
	}
	#
	# After a change, the visitor goes back to $Origin.  Since a link from
	# another site now lands on the form below (see $Ask), its Referer would
	# become $Origin, and Update would send them to that site.  Only go back
	# to a page on this site.
	#
	$OriginHost = parse_url((string) $Origin, PHP_URL_HOST);
	if (!is_string($Origin) || preg_match('|^/[^/\\\\]|', $Origin) !== 1 && ($OriginHost === null || $OriginHost === false || strcasecmp($OriginHost, $_SERVER['HTTP_HOST'] ?? '') != 0)) {
		$Origin = '/';
	}
	$Redirect = 1;
#phpinfo();

function RemoveElementFromWatchLists($db, $UserID, $ElementID, $WatchListsIDs) {
	$Debug = 0;

	if ($Debug) echo "I'm removing $ElementID\n<br>";
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
	GLOBAL $Debug;
	if ($Debug) echo "I'm adding $ElementID\n<br>";
	$WatchListElement = new WatchListElement($db);
	foreach ($WatchListsIDs as $key => $WatchListID) {
		$result = $WatchListElement->Add($UserID, $WatchListID, $ElementID);
		if ($result == -1) {
			break;
		}
	}

	return $result;
}

	if (!$User->id) {
		# OI!  you aren't logged in!
		# just what are you doing here?
		header("Location: $Origin");  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}

	#
	# Adding and removing change the database, so they happen only on a POST
	# carrying the user's token (include/csrf.php): from the watch-list
	# icons, or from the form below.  This page used to act on any request,
	# so a link on another site could change a logged-in user's watch lists.
	# Anything else - the icon link when the user has chosen to be asked, an
	# old link or bookmark, a link from elsewhere, a form left open too long -
	# shows the form below, and nothing changes until Update is clicked.
	#
	$Ask = IsSet($_REQUEST["ask"]) || !freshports_CsrfValid();

	if ($Ask) {
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/watch-lists.php');
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');

		$Title = _('Watch list maintenance');
		freshports_Start($Title,
						$Title,
						'FreeBSD, index, applications, ports');
		?>

	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(); ?>
<tr>
	<?php echo freshports_PageBannerText(_("Watch list maintenance")); ?>
</tr>
<tr><td class="content">
<?php
#		if ($ErrorMessage) {
#			echo freshports_ErrorMessage("Let\'s try that again!", $ErrorMessage);
#		}

		#
		# This page used to echo PHP_SELF, and the add/remove value, into the
		# form below without HTML escaping, so a crafted link such as
		# ?ask=1&add="><script>... ran script for a logged-in user.  The form
		# posts back to this page, so name it.  The value is an element id,
		# so make it an integer, as the add and remove paths further down do.
		#
		$PostURL = '/watch-list.php';
		if (IsSet($_REQUEST["remove"])) {
			$ButtonName = _('Update');
			$Action     = "remove";
			$Verb       = 'removed';
			$FromTo     = 'from';
			$Object     = intval($_REQUEST["remove"]);
		} else {
			if (IsSet($_REQUEST["add"])) {
				$ButtonName = _('Update');
				$Action     = "add";
				$Verb       = 'added';
				$FromTo     = 'to';
				$Object     = intval($_REQUEST["add"]);
			} elseif (IsSet($_REQUEST["Update"])) {
				# the form below, sent without a valid token: show it again
				$ButtonName = _('Update');
				$Action     = "remove";
				$Verb       = 'removed';
				$FromTo     = 'from';
				$Object     = intval($_REQUEST["Update"]);
			} else {
				die("I don't know whether you are removing or adding, so I'll just stop here shall I?");
			}
		}

		if (!$Object) {
			die('I have no idea what I\'m supposed to add or remove here...');
		}
		$port = new Port($db);
		$port->FetchByElementID($Object, $User->id);

		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/port-display.php');

		#
		# Show the top of the port page only, down to the maintainer: name,
		# short description, the whole version line, any FORBIDDEN, BROKEN,
		# etc. notes, and the maintainer.  That keeps the watch-list form below near the top of the page.
		# With the full port details, the form was below the fold and easy to
		# miss: Update was never pressed, nothing changed, and the icon on the
		# page the visitor returned to looked broken.  No watch-list icon here
		# either: it would only link back to this page.
		#
		$port_display = new port_display($db, $User);
		$port_display->SetDetailsMinimal();
		$port_display->ShowCategory        = true;
		$port_display->ShowWatchListCount  = true;
		$port_display->ShowVersionLine     = true;
		$port_display->ShowUpstream        = true;
		$port_display->ShowMaintainedBy    = true;
		$port_display->ShowWatchListStatus = false;

		$port_display->SetPort($port);

		$Port_HTML = $port_display->Display();

		$HTML = $port_display->ReplaceWatchListToken($port->{'onwatchlist'}, $Port_HTML, $port->{'element_id'});

		echo $HTML;
?>
<?php echo _('Please select the watch lists which should contain this port:'); ?>
<blockquote>
		<form action="<?php echo $PostURL; ?>" method="POST" NAME=f>
		<?php
		# The list box, with Update under it, and the notes which explain its
		# + and * marks beside it; freshports.css puts the notes under the
		# list box when there is no room beside it, e.g. on a phone.
		?>
		<div class="watch-list-choose">
		<div>
		<?php
		# when adding, start with the user's default lists selected, as the
		# add icon does when it is not set to ask; Update then adds it there
		echo freshports_WatchListDDLB($db, $User->id, '', 10, TRUE, TRUE, $Object, $Action == 'add');
		?>
		<br><br>
		<INPUT id=submit style="WIDTH: 85px; HEIGHT: 24px" type=submit size=29
		   value="<?php echo htmlspecialchars($ButtonName); ?>" name="submit">
		</div>
		<div class="watch-list-notes">
<?php
		# both forms mark lists with + and *, so explain them on both;
		# only what starts out selected differs
?>
<?php echo _('NOTES'); ?>
<ul>
<li><?php echo _("'+' indicates that the port is on this watch list."); ?>
<li><?php echo _("'*' indicates a default watch list."); ?>
<?php
		if ($Action == 'remove') {
?>
<li><?php echo _('the watch lists which are selected are those upon which the port appears'); ?>
<?php
		} else {
?>
<li><?php echo _('the watch lists which are selected are your default watch lists, and any which already contain the port'); ?>
<?php
		}
?>
</ul>
		</div>
		</div>
		<INPUT TYPE="hidden" NAME="Origin" VALUE="<?php echo htmlspecialchars($Origin); ?>">
		<INPUT TYPE="hidden" NAME="Update" VALUE="<?php echo $Object; ?>">
		<?php echo freshports_CsrfField(); ?>
<?php
		if (IsSet($WatchListID)) {
			echo '		<INPUT TYPE="hidden" NAME="wlid" VALUE="' . $WatchListID . '">';
		}
?>
		</form>
</blockquote>
</TD>
</tr>
</table>
  <td class="sidebar">
  <?php
  echo freshports_SideBar();
  ?>
  </td>

</table>

<?php
echo freshports_ShowFooter();
?>

</BODY>
</HTML>

<?php
		$Redirect = 0;
	} else {
		if (IsSet($_POST['Update'])) {
			pg_exec($db, 'BEGIN');
			$Error = '';
			$ElementID = pg_escape_string($db, intval($_POST['Update']));
			# the watch lists which should contain this element; none selected means none
			$WLIDs     = isset($_POST['wlid']) && is_array($_POST['wlid']) ? array_map('intval', $_POST['wlid']) : array();
			$WatchListElement = new WatchListElement($db);

			if ($Debug) echo "userid = '$User->id' and ElementID = '$ElementID'<br>";

			if ($WatchListElement->DeleteElementFromWatchLists($User->id, $ElementID) == -1) {
				$Error = freshports_QueryFailed($db);
			}
			if ($Debug) {
				echo "Error is '$Error'<br>";

				if (IsSet($_POST['wlid'])) {
					echo 'yes, it is set<br>';
				} else {
					echo 'no, there is nothing set!<br>';
					phpinfo();
				}
			}

			if ($Error == '' && count($WLIDs)) {

				if ($Debug) echo "userid = $User->id and ElementID = $ElementID <br>";
				if (AddElementToWatchLists($db, $User->id, $ElementID, $WLIDs)== -1) {
					$Error = freshports_QueryFailed($db);
				}
			}

			if ($Error == '') {
				pg_exec($db, 'COMMIT');
			} else {
				pg_exec($db, 'ROLLBACK');
				die($Error);
			}
		} else {
			# An icon on a page showing one watch list (watch.php) sends that list's
			# id, so the port is added to or removed from that list.  Without it,
			# the default lists are used.  The Update form above sends an array.
			$OneListID = isset($_POST['wlid']) && is_string($_POST['wlid']) ? intval($_POST['wlid']) : 0;

			if (IsSet($_POST['add'])) {
				pg_exec($db, 'BEGIN');
				$Error = '';
				$ElementID = pg_escape_string($db, intval($_POST['add']));
				if ($ElementID == '') {
					die('The target for addition was not supplied');
				}

				$WatchListElement = new WatchListElement($db);
				if ($OneListID) {
					$Added = $WatchListElement->Add($User->id, $OneListID, $ElementID);
				} else {
					$Added = $WatchListElement->AddToDefault($User->id, $ElementID);
				}
				if ($Added == 1) {
					pg_exec($db, 'COMMIT');
				} else {
					pg_exec($db, 'ROLLBACK');
					die(freshports_QueryFailed($db));
				}
			} else {
				if (IsSet($_POST['remove'])) {
					pg_exec($db, 'BEGIN');
					$ElementID = pg_escape_string($db, intval($_POST['remove']));
					if ($ElementID == '') {
						die('The target for removal was not supplied');
					}

					$WatchListElement = new WatchListElement($db);
					if ($OneListID) {
						$Removed = $WatchListElement->Delete($User->id, $OneListID, $ElementID);
					} else {
						$Removed = $WatchListElement->DeleteFromDefault($User->id, $ElementID);
					}
					if ($Removed >= 0) {
						pg_exec($db, 'COMMIT');
					} else {
						pg_exec($db, 'ROLLBACK');
						die(freshports_QueryFailed($db));
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
			if ($Debug) echo "Origin supplied is $Origin\n<br>";
			$Origin = str_replace(' ', '&', $Origin);
		}

		if ($Debug) echo "redirecting to $Origin\n<br>";

		header("Location: $Origin");  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}

#	phpinfo();

?>
