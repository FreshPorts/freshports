<?php
	#
	# Copyright (c) 2024 Dan Langille
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/abi.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/report_subscriptions_abi.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/watch-lists.php');

# used for the key value pair of watch_list_id and abi_id
define('MY_DELIMITER', ':::');

if (IN_MAINTENANCE_MODE) {
	header('Location: /' . MAINTENANCE_PAGE, TRUE, 307);
}

if (!IsSet($_COOKIE[USER_COOKIE_NAME])) {
	header('Location: /login.php');  /* Redirect browser to PHP web site */
	exit;  /* Make sure that code below does not get executed when we redirect. */
}

function freshports_ABI_list($dbh) {
	# return the HTML which forms a dropdown list box.
	# optionally, select the item identified by $selected.

	$Debug = 0;
    $multiple = 1;

	$HTML = '<select name="abi[]" size="23"';
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



function freshports_ABI_list_watching($UserID, $dbh) {
	# return the HTML which forms a dropdown list box.
	# It will contain the ABI which a user is watching.
	# For now, it returns an empty list.

	$Debug = 0;

	$HTML = '<select name="watch_list_abi[]" size="23" max-width="95%" title="Select the ABI" multiple>' . "\n";

	$rpn = new report_subscriptions_abi($dbh);
    $numrows = $rpn->Fetch($UserID);
    for ($i = 0; $i < $numrows; $i++) {
        $rpn->FetchNth($i);
        $key = $rpn->watch_list_id . MY_DELIMITER . $rpn->abi_id;
        $value = htmlspecialchars($rpn->watch_list_name . ' -> ' . $rpn->abi_name);
		$HTML .= '<option value="' . $key . '">' . $value . '</option>';
	}
	$HTML .= '</select>';

	return $HTML;
}

$wlid = 0;

#phpinfo();
# was add or delete pressed?
if (IsSet($_REQUEST['add']) || IsSet($_REQUEST['delete'])) {

    # which did they click?
	$add    = false;
	$delete = false;
	if (isset($_REQUEST['delete'])) {
		$delete = true;
	} elseif (isset($_REQUEST['add'])) {
		$add = true;
	} else {
		syslog(LOG_ERR, "neither delete nor add was specified when abi and watch list was supplied.");
		exit("not sure what's going on there, but I'm stopping right now.");
	}

	if ($add) {
        if (!IsSet($_REQUEST['wlid']) || !IsSet($_REQUEST['abi'])) {
			syslog(LOG_ERR, "one of wlid or abi was not selected.");
			exit("You have to click at least one watch list and one ABI. I'm stopping right now. You can click back and try again.");
        }
        # convert single value to an array
        if (is_array($_REQUEST['abi'])) {
            $abi_id_array = $_REQUEST['abi'];
        } else {
            $abi_id_array = array(intval($_REQUEST['abi']));
        }

        # convert single value to an array
        if (is_array($_REQUEST['wlid'])) {
            $wl_id_array = $_REQUEST['wlid'];
        } else {
            $wl_id_array = array(intval($_REQUEST['wlid']));
        }

        $rsa = new report_subscriptions_abi($db);

        # enforce a bit of sanity, to deter abuse.
        $num_abi_id = min(sizeof($abi_id_array), 30);
        $num_wl_id  = min(sizeof($wl_id_array), 30);

        echo '$num_abi_id=' . $num_abi_id. "\n<br>";
        echo '$num_wl_id='  . $num_wl_id.  "\n<br>";
        $sql = 'were we go: ';
        echo 'Hitting the for loops<br>';

        for ($abi_ix = 0; $abi_ix <  $num_abi_id; $abi_ix++) {
            echo '$abi_ix=' . $abi_ix . "\n<br>";
            for ($wl_ix = 0; $wl_ix <  $num_wl_id; $wl_ix++) {
                echo '$wl_ix=' . $wl_ix . "\n<br>";
                echo $User->id . ', ', $abi_id_array[$abi_ix]. ', '  . $wl_id_array[$wl_ix];
                $sql .= "$User->id, " . $abi_id_array[$abi_ix]. ', '  . $wl_id_array[$wl_ix] . "<br>";

				$rsa->Save($User->id, $abi_id_array[$abi_ix], $wl_id_array[$wl_ix]);
            }
        }
    }

    if ($delete) {
        # convert single value to an array
        if (is_array($_REQUEST['watch_list_abi'])) {
            $watch_list_abi_array = $_REQUEST['watch_list_abi'];
        } else {
            $watch_list_abi_array = array(intval($_REQUEST['watch_list_abi']));
        }
		$rsa = new report_subscriptions_abi($db);

        # enforce a bit of sanity, to deter abuse.
        $num_to_delete = min(sizeof($watch_list_abi_array), 80);
        for ($i = 0; $i < $num_to_delete; $i++) {
            # We have a watch list id and an ABI id, looking like:
            # 19901:::2

			$key_value = explode(MY_DELIMITER, $watch_list_abi_array[$i]);
            if (sizeof($key_value) != 2) {
				syslog(LOG_ERR, "The key-value pair had more than two items.");
				exit("The key-value pair had more than two items. Not sure what's going on there, but I'm stopping right now.");
            }
            $watch_list_id = intval($key_value[0]);
            $abi_id        = intval($key_value[1]);

            echo "deleting  $watch_list_id $abi_id<br>\n";
            $num_rows = $rsa->Delete($User->id, $abi_id, $watch_list_id);
            if ($num_rows >= 1) {
				syslog(LOG_ERR, "The key-value pair deleted more than one row ($num_rows): $watch_list_id ::: $abi_id.");
				exit("The key-value pair deleted more than one row ($num_rows): $watch_list_id ::: $abi_id. Not sure what's going on there, but I'm stopping right now.");
            }
        }
    }

    echo 'This is the SQL<pre>' . $sql . '</pre>';


}



	$ArticleTitle = 'Package Notifications';

	freshports_Start(	$ArticleTitle,
					'',
					'FreeBSD, daemon copyright');

	$Debug = 0;
	if ($Debug) phpinfo();




?>

<?php echo freshports_MainTable(); ?>

<tr><td class="content">

		<?php echo freshports_MainContentTable(NOBORDER); ?>
<tr>
	<?php echo freshports_PageBannerText($ArticleTitle); ?>
<tr>
<td colspan="1">

<p>
This page allows you to choose which watch lists will be used for Package Notifications.
</p>
<p>
For each watch list, select one or more ABI.  When a new package is available for something on your watch list,
in the ABI you selected, an email will be sent as soon FreshPorts discovers the fresh package.
</p>
    </tr>
<tr>
    <td>
    <form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">
        <table>
            <tr>
                <td>


<?php
    if ($Debug) echo 'when calling freshports_WatchListDDLBForm, $wlid = \'' . $wlid . '\'';
    echo freshports_WatchListDDLB($db, $User->id, $wlid, 15, 1);
?>
<td>
<?php
		echo freshports_ABI_list($db);
?>
</td>
<td>
            <input type="submit" value="add" name="add">Add Selected>>></input>

            <br>		<br>
            <input type="submit" value="delete" name="delete">Delete Selected >>></input>
	</td>
	<td>

		list of chosen ABI
		<br>
		<?php
		echo freshports_ABI_list_watching($User->id, $db);
		?>
		</td>
</tr>
        </table>
    </form>
    </td>
</tr>
</table>
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
