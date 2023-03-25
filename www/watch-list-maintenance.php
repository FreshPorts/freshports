<?php
	#
	# $Id: watch-list-maintenance.php,v 1.2 2006-12-17 12:06:19 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
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

$Debug = 0;
define('MAX_WLID_SIZE', 50);

if ($Debug) echo phpinfo();

# first validate the wlid parameter.

if (array_key_exists('wlid', $_REQUEST) and is_array($_REQUEST['wlid'])) {
	if ($Debug) echo 'wlid is an array<br>';
	$wlid = $_REQUEST['wlid'];

	if ($Debug) echo 'There are ' . count($wlid) . ' items in $wlid<br>';
	if (count($wlid) > MAX_WLID_SIZE) {
		syslog(LOG_ERR, __FILE__ . ' found more than ' . MAX_WLID_SIZE . ' entries in wlid - that should never happen');
		exit;
	}

	# now, make sure everything in there is an integer
	foreach($wlid as $key => $value) {
		$wlid[intval($key)] = intval($value);
	}
} else {
	if ($Debug) echo 'wlid is not an array<br>';
	$wlid = array();
}

$visitor = $_COOKIE[USER_COOKIE_NAME] ?? '';
// if we don't know who they are, we'll make sure they login first
if (!$visitor) {
        header('Location: /login.php');  /* Redirect browser to PHP web site */
        exit;  /* Make sure that code below does not get executed when we redirect. */
}

unset($add_name);
unset($rename_name);

$ValidCharacters = 'a-z, A-Z, and 0-9';

$WatchListNameMessage = 'Watch list names must contain only A..Z, a..z, or 0..9.';

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/watch_lists.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/user.php');

	$Title = 'Watch list maintenance';
	freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');
					
#phpinfo();
$Debug = 0;

$ConfirmationNeeded['delete']      = 1;
$ConfirmationNeeded['delete_all']  = 1;
$ConfirmationNeeded['empty']       = 1;
$ConfirmationNeeded['empty_all']   = 1;
$ConfirmationNeeded['add']         = 0;
$ConfirmationNeeded['rename']      = 0;
$ConfirmationNeeded['set_default'] = 0;
$ConfirmationNeeded['set_options'] = 0;

$UserClickedOn = '';
$ErrorMessage  = '';

if (IsSet($_POST['delete'])) {
	$UserClickedOn = 'delete';
}

if (IsSet($_POST['delete_all'])) {
	$UserClickedOn = 'delete_all';
}

if (IsSet($_POST['empty'])) {
	$UserClickedOn = 'empty';
}

if (IsSet($_POST['empty_all'])) {
	$UserClickedOn = 'empty_all';
}

if (IsSet($_POST['add'])) {
	$UserClickedOn = 'add';
}

if (IsSet($_POST['rename'])) {
	$UserClickedOn = 'rename';
}

if (IsSet($_POST['set_default'])) {
	$UserClickedOn = 'set_default';
}

if (IsSet($_POST['set_options'])) {
	$UserClickedOn = 'set_options';
}

#
# Error checking
#
if ($UserClickedOn) {
	if ($ConfirmationNeeded[$UserClickedOn]) {
		if ($_POST['confirm'] != $_POST[$UserClickedOn]) {
			$ErrorMessage = 'You did not supply the confirmation text';
		}
	}

	if ($ErrorMessage == '') {
		switch ($UserClickedOn) {
			case 'add':
				$add_name = pg_escape_string($db, $_POST['add_name']);
				if ($add_name == '') {
					$ErrorMessage = 'When creating a new list, you must supply a name.';
				}
				if (preg_match("/[^a-zA-Z0-9]/", $add_name)) {
					$ErrorMessage = $WatchListNameMessage;
				}
				break;

			case 'rename':
				$rename_name  = pg_escape_string($db, $_POST['rename_name']);
				if ($rename_name == '') {
					$ErrorMessage = 'When renaming an existing list, you must supply a name.';
				}
				if (preg_match("/[^a-zA-Z0-9]/", $rename_name)) {
					$ErrorMessage = $WatchListNameMessage;
				}
				break;
		}
	}
	
}

if ($UserClickedOn != '' && $ErrorMessage == '') {
	if ($Debug) echo "you clicked on = '$UserClickedOn'<br>";
	if ($Debug) echo "your confirmation text = '" . pg_escape_string($db, $_POST['confirm']) . "'<br>";

	# all went well, so let us do what they told us to do
	switch ($UserClickedOn) {
		case 'add':
			$WatchList = new WatchList($db);
			$WatchList->Debug = $Debug;
			$NewWatchListID = $WatchList->Create($User->id, $add_name);
			if ($Debug) echo 'I just created \'' . $add_name . '\' with ID = \'' . $NewWatchListID . '\'';
			$add_name = '';
			break;

		case 'rename':
			# check valid new name
			# check only one watch list supplied
			if (count($wlid) == 1) {
				foreach ($wlid as $key => $WatchListIDToRename) {
					$WatchListIDToRename = intval($WatchListIDToRename);
					$WatchList = new WatchList($db);
					$WatchList->Debug = $Debug;
					$NewName = $WatchList->Rename($User->id, $WatchListIDToRename, $rename_name);
					if ($Debug) echo 'I have renamed your list to \'' . $rename_name . '\'';
					$rename_name = '';
					break;
				}
			} else {
				$ErrorMessage = 'Select exactly one watch list to be renamed.  I can\'t handle zero or more than one.';
			}
			break;

		case 'delete':
			pg_query($db, 'BEGIN');
			$WatchList = new WatchList($db);
			$WatchList->Debug = $Debug;
			foreach ($wlid as $key => $WatchListIDToDelete) {
				$WatchListIDToDelete = intval($WatchListIDToDelete);
				if ($Debug) echo "\$key='$key' \$WatchListIDToDelete='$WatchListIDToDelete'<br>";
				$DeletedWatchListID = $WatchList->Delete($User->id, $WatchListIDToDelete);
				if ($DeletedWatchListID != $WatchListIDToDelete) {
					die("Failed to deleted '$WatchListIDToDelete' (return value '$DeletedWatchListID')" . pg_last_error($db));
				}
				if ($Debug) echo 'I have deleted watch list id = ' . $WatchListIDToDelete . '<br>';
			}
			pg_query($db, 'COMMIT');
			break;
			
		case 'delete_all':
			pg_query($db, 'BEGIN');
			$WatchLists = new WatchLists($db);
			$WatchLists->Debug = $Debug;
			if ($WatchLists->DeleteAllLists($User->id) == 1) {
				pg_query($db, 'COMMIT');
			} else {
				pg_query($db, 'ROLLBACK');
			}
			break;

		case 'empty':
			pg_query($db, 'BEGIN');
			$WatchList = new WatchList($db);
			$WatchList->Debug = $Debug;
			foreach ($wlid as $key => $WatchListIDToEmpty) {
				$WatchListIDToEmpty = intval($WatchListIDToEmpty);
				if ($Debug) echo "\$key='$key' \$WatchListIDToEmpty='$WatchListIDToEmpty'<br>";
				$EmptydWatchListID = $WatchList->EmptyTheList($User->id, $WatchListIDToEmpty);
				if ($EmptydWatchListID != $WatchListIDToEmpty) {
					die("Failed to Empty '$WatchListIDToEmpty' (return value '$EmptydWatchListID')" . pg_last_error($db));
				}
				if ($Debug) echo 'I have emptied watch list id = ' . $WatchListIDToEmpty . '<br>';
			}
			pg_query($db, 'COMMIT');
			break;

		case 'empty_all':
			pg_query($db, 'BEGIN');
			$WatchList = new WatchList($db);
			$WatchList->Debug = $Debug;
			$NumRows = $WatchList->EmptyAllLists($User->id, pg_escape_string($db, $WatchListIDToEmpty));
			if (!IsSet($NumRows)) {
				die("Failed to Empty '$WatchListIDToEmpty' (return value '$EmptydWatchListID')" . pg_last_error($db));
			}
			if ($Debug) echo 'I have emptied all the watch lists.<br>';

			pg_query($db, 'COMMIT');
			break;

		case 'set_default':
			if ($Debug) echo 'I have set your default lists.<br>';
			pg_query($db, 'BEGIN');
			$WatchLists = new WatchLists($db);
			$WatchLists->Debug = $Debug;
			$numrows = $WatchLists->In_Service_Set($User->id, $wlid);
			if ($Debug) echo "$numrows watchlists were affected by that action";
			if ($numrows >= 0) {
				pg_query($db, 'COMMIT');
			} else {
				pg_query($db, 'ROLLBACK');
			}
			break;

		case 'set_options':
			# assign valid values to $option based on what is supplied
			$option = $_POST['addremove'] == 'default' ? 'default' : 'ask';

			if ($Debug) echo 'I have set options to: ' . $option;

			$User->SetWatchListAddRemove(pg_escape_string($db, $option));
			break;

		default:
			echo 'Hmmm, I have no idea what you asked me to do';
	}
}

if ($Debug) echo 'add remove = ' . $User->watch_list_add_remove;

function CheckForNoDefaultAndAddToDefault($db, $User) {
	$Message = '';

	if (freshports_WatchListCountDefault($db, $User->id) == 0) {
		if ($User->watch_list_add_remove == 'default') {
			$Message = 'You have no default watch lists.  You have chosen to act ' .
				'upon the default watch list[s].  With this combination, you will be unable to add ' .
				'ports using the one-click method.  It is suggested that you set at least one watch list ' .
				'to be the default watch list.';
		}
	}

	return $Message;
}

$ErrorMessage .= CheckForNoDefaultAndAddToDefault($db, $User);

?>

	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(); ?>
<tr>
	<?php echo freshports_PageBannerText("Watch list maintenance"); ?>
</tr>

<tr><td>
<table class="watch-maintenance fullwidth borderless">
<tr><td>
<?php
	if ($ErrorMessage != '') {
		echo freshports_ErrorMessage("Let's try that again!", $ErrorMessage);
	}
?>

<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST" NAME=f>
<table class="fullwidth bordered">
<tr><td class="element-details">Watch Lists</td><td><span class="element-details">Actions</span> (scroll down for instructions)</td></tr>
  <tr>
    <td>
<?php
	echo freshports_WatchListDDLB($db, $User->id, '', 10, TRUE);
?>
    </td>
    <td>
    <INPUT id=add         type=submit size=48 value="Add"          name=add>&nbsp;&nbsp;&nbsp;
    <INPUT id=add_name    name=add_name    <?php if (IsSet($add_name))    echo 'value="' . htmlspecialchars($add_name)    . '" '; ?>pattern="[a-zA-Z0-9]+" size=10><small><sup>(1)</sup></small><br>
    <INPUT id=rename      type=submit size=23 value="Rename"       name=rename>&nbsp;&nbsp;&nbsp;
    <INPUT id=rename_name name=rename_name <?php if (IsSet($rename_name)) echo 'value="' . htmlspecialchars($rename_name) . '" '; ?>pattern="[a-zA-Z0-9]+" size=10><small><sup>(1)</sup></small><br>
    <?php echo "&nbsp;<small>(1) - only $ValidCharacters</small>" ?>
		<br>

    <br>
    <INPUT id=delete      type=submit size=29 value="Delete"       name=delete><br>
    <INPUT id=delete_all  type=submit size=29 value="Delete All"   name=delete_all><br>
    <br>
    <INPUT id=empty       type=submit size=29 value="Empty"        name=empty><br>
    <INPUT id=empty_all   type=submit size=29 value="Empty All"    name=empty_all><br>
    <br>
    <INPUT id=default     type=submit size=29 value="Set Default"  name=set_default><br>
    <br>

    <label>Confirm: <INPUT id=confirm name=confirm pattern="(Delete|Empty)( All)?" size=10></label>
	 <br>(case sensitive)

    </td>
</tr>
</table>
</form>

</td><td>

<table class="fullwidth bordered">
<tr><td class="element-details">Options</td></tr>
  <tr>
<td>
When clicking on Add/Remove for a port,<br> the action should affect
<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST" NAME=f>
<INPUT type=radio name=addremove value=default<?php if ($User->watch_list_add_remove == 'default') echo ' checked'; ?>>&nbsp;the default watch list[s]<br>
<INPUT type=radio name=addremove value=ask<?php     if ($User->watch_list_add_remove == 'ask')     echo ' checked'; ?>>&nbsp;Ask for watch list name[s] each time<br>
<INPUT type=submit size=29 value="Set options"  name=set_options>
 </form>
</td>
    </tr>
</table>

</td></tr>

</table>

<H2>Information</H2>
<ul>
<li>Names do not have to be unique but it is advisable.
<li>Valid characters are: <?php echo $ValidCharacters; ?>
<li>Please contact the webmaster if you want more than 5 lists.
</ul>

<H2>Help</H2>

<ul>
<li><b>Watch Lists</b> - this is what it's all about
	<ul>
	<li>These are your existing watch lists.
	</ul>
	<br>
<li><b>Actions</b> - what you can do to your watch lists
	<ul>
	<li><b>Add</b> - add a new watch list.  Supply the name in the space provided.  This name will be supplied
			in any mail notification messages for this watch lists.  
	
	<li><b>Rename</b> - rename a new watch list.  Select the watch list and supply the new name.
	
		
	<li><b>Delete<sup>*</sup></b> - Deletes the selected watch lists.
	
	<li><b>Delete All<sup>*</sup></b> - Deletes all of your watch lists.
	
	<li><b>Empty<sup>*</sup></b> - Empties the selected watch lists.
	
	<li><b>Empty All<sup>*</sup></b> - Empties all of your watch lists.

	<li><b>Set Default</b> - Sets the default watch list[s].  The default watch list[s] is/are used when:
			<ul>
			<li>you click on the add/remove links
			<li>when displaying a port, the add/remove link reflects whether or not that port is on this list
			</ul>
	</ul>
	
	<br>
<li><b>Options</b> - this affects the display/actions on other pages
	<ul>
	<li><b>Set Options</b> - Set the options to be used when clicking on Add/Remove for a port.
	</ul>
</ul>

<p>
		<sup>*</sup>These items require confirmation by typing the button name in the 
			confirmation text box (case sensitive).  Be careful: these actions cannot be undone.
    For example, when deleting or emptying a list, you must confirm your action by typing
    the button name into this field (e.g. if you click on <b>Empty All</b>",
    you must type <b>Empty All</b> into the box below in order for the action
    to be completed). This is case sensitive.<br>

</p>
</td></tr></table>
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
