<?php
	#
	# $Id: watch-list-maintenance.php,v 1.2 2006-12-17 12:06:19 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/watch-lists.php');

$visitor = $_COOKIE['visitor'];

unset($add_name);
unset($rename_name);

$ValidCharacters = 'a-z, A-Z, and 0-9';

$WatchListNameMessage = 'Watch list names must contain only A..Z, a..z, or 0..9.';

// if we don't know who they are, we'll make sure they login first
if (!$visitor) {
        header('Location: /login.php?origin=' . $_SERVER['PHP_SELF']);  /* Redirect browser to PHP web site */
        exit;  /* Make sure that code below does not get executed when we redirect. */
}

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/watch_lists.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/user.php');

	freshports_Start('Watch list maintenance',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');
					
#phpinfo();
$Debug = 0;

$ConfirmationNeeded{'delete'}     = 1;
$ConfirmationNeeded{'delete_all'} = 1;
$ConfirmationNeeded{'empty'}      = 1;
$ConfirmationNeeded{'empty_all'}  = 1;

$UserClickedOn = '';
$ErrorMessage  = '';

if ($_POST['delete']) {
	$UserClickedOn = 'delete';
}

if ($_POST['delete_all']) {
	$UserClickedOn = 'delete_all';
}

if ($_POST['empty']) {
	$UserClickedOn = 'empty';
}

if ($_POST['empty_all']) {
	$UserClickedOn = 'empty_all';
}

if ($_POST['add']) {
	$UserClickedOn = 'add';
}

if ($_POST['rename']) {
	$UserClickedOn = 'rename';
}

if ($_POST['set_default']) {
	$UserClickedOn = 'set_default';
}

if ($_POST['set_options']) {
	$UserClickedOn = 'set_options';
}

#
# Error checking
#
if ($UserClickedOn) {
	if ($ConfirmationNeeded{$UserClickedOn}) {
		if ($_POST['confirm'] != $_POST[$UserClickedOn]) {
			$ErrorMessage = 'You did not supply the confirmation text';
		}
	}

	if ($ErrorMessage == '') {
		switch ($UserClickedOn) {
			case 'add':
				if (pg_escape_string($_POST['add_name']) == '') {
					$ErrorMessage = 'When creating a new list, you must supply a name.';
				}
				if (preg_match("/[^a-zA-Z0-9]/", $_POST['add_name'])) {
					$ErrorMessage = $WatchListNameMessage;
					$add_name     = $_POST['add_name'];
				}
				break;

			case 'rename':
				if (pg_escape_string($_POST['rename_name']) == '') {
					$ErrorMessage = 'When renaming an existing list, you must supply a name.';
				}
				if (preg_match("/[^a-zA-Z0-9]/", $_POST['rename_name'])) {
					$ErrorMessage = $WatchListNameMessage;
					$rename_name  = $_POST['rename_name'];
				}
				break;
		}
	}
	
}

if ($UserClickedOn != '' && $ErrorMessage == '') {
	if ($Debug) echo "you clicked on = '$UserClickedOn'<br>";
	if ($Debug) echo "your confirmation text = '" . pg_escape_string($_POST['confirm']) . "'<br>";

	# all went well, so let us do what they told us to do
	switch ($UserClickedOn) {
		case 'add':
			$WatchList = new WatchList($db);
			$NewWatchListID = $WatchList->Create($User->id, pg_escape_string($_POST['add_name']));
			if ($Debug) echo 'I just created \'' . pg_escape_string($_POST['add_name']) . '\' with ID = \'' . $NewWatchListID . '\'';
			break;

		case 'rename':
			# check valid new name
			# check only one watch list supplied
			if (count($_POST['wlid']) == 1) {
				list($key, $WatchListIDToRename) = each($_POST['wlid']);
				$WatchList = new WatchList($db);
				$NewName = $WatchList->Rename($User->id, $WatchListIDToRename, $_POST['rename_name']);
				if ($Debug) echo 'I have renamed your list to \'' . pg_escape_string($_POST['rename_name']) . '\'';
			} else {
				$ErrorMessage = 'Select exactly one watch list to be renamed.  I can\'t handle zero or more than one.';
			}
			break;

		case 'delete':
			pg_query($db, 'BEGIN');
			$WatchList = new WatchList($db);
			while (list($key, $WatchListIDToDelete) = each($_POST['wlid'])) {
				if ($Debug) echo "\$key='$key' \$WatchListIDToDelete='$WatchListIDToDelete'<br>";
				$DeletedWatchListID = $WatchList->Delete($User->id, pg_escape_string($WatchListIDToDelete));
				if ($DeletedWatchListID != $WatchListIDToDelete) {
					die("Failed to deleted '$WatchListIDToDelete' (return value '$DeletedWatchListID')" . pg_last_error());
				}
				if ($Debug) echo 'I have deleted watch list id = ' . $WatchListIDToDelete . '<br>';
			}
			pg_query($db, 'COMMIT');
			break;
			
		case 'delete_all':
			pg_query($db, 'BEGIN');
			$WatchLists = new WatchLists($db);
			if ($WatchLists->DeleteAllLists($User->id) == 1) {
				pg_query($db, 'COMMIT');
			} else {
				pg_query($db, 'ROLLBACK');
			}
			break;

		case 'empty':
			pg_query($db, 'BEGIN');
			$WatchList = new WatchList($db);
			while (list($key, $WatchListIDToEmpty) = each($_POST['wlid'])) {
				if ($Debug) echo "\$key='$key' \$WatchListIDToEmpty='$WatchListIDToEmpty'<br>";
				$EmptydWatchListID = $WatchList->EmptyTheList($User->id, pg_escape_string($WatchListIDToEmpty));
				if ($EmptydWatchListID != $WatchListIDToEmpty) {
					die("Failed to Empty '$WatchListIDToEmpty' (return value '$EmptydWatchListID')" . pg_last_error());
				}
				if ($Debug) echo 'I have emptied watch list id = ' . $WatchListIDToEmpty . '<br>';
			}
			pg_query($db, 'COMMIT');
			break;

		case 'empty_all':
			pg_query($db, 'BEGIN');
			$WatchList = new WatchList($db);
			$NumRows = $WatchList->EmptyAllLists($User->id, pg_escape_string($WatchListIDToEmpty));
			if (!IsSet($NumRows)) {
				die("Failed to Empty '$WatchListIDToEmpty' (return value '$EmptydWatchListID')" . pg_last_error());
			}
			if ($Debug) echo 'I have emptied all the watch lists.<br>';

			pg_query($db, 'COMMIT');
			break;

		case 'set_default':
			if ($Debug) echo 'I have set your default lists.<br>';
			pg_query($db, 'BEGIN');
			$WatchLists = new WatchLists($db);
			$numrows = $WatchLists->In_Service_Set($User->id, $_POST['wlid']);
			if ($Debug) echo "$numrows watchlists were affected by that action";
			if ($numrows >= 0) {
				pg_query($db, 'COMMIT');
			} else {
				pg_query($db, 'ROLLBACK');
			}
			break;

		case 'set_options':
			if ($Debug) echo 'I have set options to: ' . pg_escape_string($_POST['addremove']);

			$User->SetWatchListAddRemove(pg_escape_string($_POST['addremove']));
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

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(); ?>
<TR>
	<? echo freshports_PageBannerText("Watch list maintenance"); ?>
</TR>

<?php
	if ($ErrorMessage != '') {
		echo freshports_ErrorMessage("Let's try that again!", $ErrorMessage);
	}
?>
<tr><td>
<table WIDTH="100%" BORDER="0">
<tr><td valign="top">

<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST" NAME=f>
<TABLE WIDTH="100%" BORDER="1" CELLSPACING="0" CELLPADDING="5">
<TR><td nowrap><BIG><b>Watch Lists</b></BIG></td><td><BIG><b>Actions</b></BIG> (scroll down for instructions)</td></tr>
  <TR>
    <TD valign="top">
<?php
	echo freshports_WatchListDDLB($db, $User->id, '', 10, TRUE);
?>
    </TD>
    <TD>
    <INPUT id=add         style="WIDTH: 85px; HEIGHT: 24px" type=submit size=48 value="Add"          name=add>&nbsp;&nbsp;&nbsp; 
    <INPUT id=add_name    name=add_name    <?php if (IsSet($add_name))    echo 'value="' . $add_name    . '" '; ?>size=10><small><sup>(1)</sup></small><BR>
    <INPUT id=rename      style="WIDTH: 85px; HEIGHT: 24px" type=submit size=23 value="Rename"       name=rename>&nbsp;&nbsp;&nbsp; 
    <INPUT id=rename_name name=rename_name <?php if (IsSet($rename_name)) echo 'value="' . $rename_name . '" '; ?>size=10><small><sup>(1)</sup></small><BR>
    <?php echo "&nbsp;<small>(1) - only $ValidCharacters</small>" ?>
		<br>

    <br>
    <INPUT id=delete      style="WIDTH: 85px; HEIGHT: 24px" type=submit size=29 value="Delete"       name=delete><br>
    <INPUT id=delete_all  style="WIDTH: 85px; HEIGHT: 24px" type=submit size=29 value="Delete All"   name=delete_all><br>
    <br>
    <INPUT id=empty       style="WIDTH: 85px; HEIGHT: 24px" type=submit size=29 value="Empty"        name=empty><br>
    <INPUT id=empty_all   style="WIDTH: 85px; HEIGHT: 24px" type=submit size=29 value="Empty All"    name=empty_all><br>
    <br>
    <INPUT id=default     style="WIDTH: 85px; HEIGHT: 24px" type=submit size=29 value="Set Default"  name=set_default><br>
    <br>

    Confirm: <INPUT id=confirm name=confirm size=10>
	 <br>(case sensitive)

    </TD>
</tr>
</table>
</form>

</td><td valign="top">

<TABLE WIDTH="100%" BORDER="1" CELLSPACING="0" CELLPADDING="5">
<TR><td><BIG><b>Options</b></BIG></td></tr>
  <TR>
<td valign="top" nowrap>
When clicking on Add/Remove for a port,<br> the action should affect
<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST" NAME=f>
<INPUT type=radio name=addremove value=default<?php if ($User->watch_list_add_remove == 'default') echo ' checked'; ?>>&nbsp;the default watch list[s]<BR>
<INPUT type=radio name=addremove value=ask<?php     if ($User->watch_list_add_remove == 'ask')     echo ' checked'; ?>>&nbsp;Ask for watch list name[s] each time<br>
<INPUT style="WIDTH: 85px; HEIGHT: 24px" type=submit size=29 value="Set options"  name=set_options>
 </form>
</td>
    </TR>
</TABLE>

</td></tr>

</table>

<H2>Information</H2>
<ul>
<li>Names do not have to unique but it is advisable.
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
			<li>when displaying a port, the add/remove link reflects whether or not  
			</ul>
	</ul>
	
	<br>
<li><b>Options</b> - this affects the display/actions on other pages
	<ul>
	<li><b>Set Options</b> - Set the options to be used when clicking on Add/Remove for a port.
	</ul>
</ul>

<p>
		<sup>*</sup>These items confirmation by typing the button name in the 
			confirmation text box (case sensitive).  Be careful: these actions cannot be undone.
    For example, when deleting or emptying a row, you must confirm your action by typing
    the button name into this field (e.g. if you click on <b>Empty All</b>",
    you must type <b>Empty All</b> into the box below in order for the action
    to be completed). This is case sensitive.<br>

</p>
</td></tr></table>
</td>
<td valign="top">
	<?
	echo freshports_SideBar();
	?>
</td>
</tr>
</table>

<?
echo freshports_ShowFooter();
?>

</BODY>
</HTML>
