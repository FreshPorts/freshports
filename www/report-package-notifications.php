<?php
	#
	# Copyright (c) 2024 Dan Langille
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/watch-lists.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/abi.php');

function freshports_ABI_list($dbh) {
	# return the HTML which forms a dropdown list box.
	# optionally, select the item identified by $selected.

	$Debug = 0;
    $multiple = 1;

	$HTML = '<select name="abi" size="23"';
	if ($multiple) {
		$HTML .= '[]';
	}

	$HTML .= '" title="Select a watch list"';

	if ($multiple) {
		$HTML .= ' multiple';
	}
	$HTML .= ">\n";

	$ABI = new ABI($dbh);
	$NumRows = $ABI->Fetch();

	if ($Debug) {
		echo "$NumRows rows found!<br>";
		echo "selected = '$selected'<br>";
	}

	if ($NumRows) {
		for ($i = 0; $i < $NumRows; $i++) {
			$ABI->FetchNth($i);
			$HTML .= '<option value="' . htmlspecialchars(pg_escape_string($dbh, $ABI->id)) . '"';
			$HTML .= '>' . htmlspecialchars(pg_escape_string($dbh, $ABI->name));

			$HTML .= "</option>\n";
		}
	}

	$HTML .= '</select>';

	if (!$NumRows) {
		$HTML .= '<br><h2> You have no watch lists.  You must <a href="watch-list-maintenance.php">create one</a>.</h2>';
	}

	return $HTML;
}


if (IsSet($_REQUEST['wlid']) && $_REQUEST['wlid']) {
	# they clicked on the GO button and we have to apply the
	# watch staging area against the watch list.
	$wlid = pg_escape_string($db, intval($_REQUEST['wlid']));
	if ($Debug) echo "setting SetLastWatchListChosen => \$wlid='$wlid'";
	$User->SetLastWatchListChosen($wlid);
	if ($Debug) echo "\$wlid='$wlid'";
} else {
	$wlid = $User->last_watch_list_chosen;
	if ($Debug) echo "\$wlid='$wlid'";
	if ($wlid == '') {
		$WatchLists = new WatchLists($db);
		$wlid = $WatchLists->GetDefaultWatchListID($User->id);
		if ($Debug) echo "GetDefaultWatchListID => \$wlid='$wlid'";
	}
}

if (!IsSet($_COOKIE[USER_COOKIE_NAME])) {
		header('Location: /login.php');  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}

	if (IN_MAINTENANCE_MODE) {
                header('Location: /' . MAINTENANCE_PAGE, TRUE, 307);
	}

	$ArticleTitle = 'Package Notifications';

	freshports_Start(	$ArticleTitle,
					'',
					'FreeBSD, daemon copyright');

	$Debug = 0;
	if ($Debug) phpinfo();




?>

<?php echo freshports_MainTable(); ?>

  <tr>

<td class="content">
<table class="fullwidth borderless">
<tr>
<td class="accent"><span><?php echo $ArticleTitle; ?></span></td>
</tr>
<tr>
<td>

<p>
This page allows you to choose which watch lists will be used for Package Notifications.
</p>
<p>
For each watch list, select one or more ABI.  When a new package is available for something on your watch list,
in the ABI you selected, an email will be sent as soon FreshPorts discovers the fresh package.
</p>


				<?php
				if ($Debug) echo 'when calling freshports_WatchListDDLBForm, $wlid = \'' . $wlid . '\'';
				echo freshports_WatchListDDLBForm($db, $User->id, $wlid);


                echo freshports_ABI_list($db);
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
