<?
	# $Id: watch-list-maintenance.php,v 1.1.2.5 2002-12-08 03:23:46 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");

$visitor = $_COOKIE["visitor"];

// if we don't know who they are, we'll make sure they login first
if (!$visitor) {
        header("Location: login.php?origin=" . $_SERVER["PHP_SELF"]);  /* Redirect browser to PHP web site */
        exit;  /* Make sure that code below does not get executed when we redirect. */
}

	require_once($_SERVER['DOCUMENT_ROOT'] . "/../classes/watch_lists.php");

	freshports_Start("Watch list maintenance",
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");
					
#phpinfo();
$Debug = 0;

$ConfirmationNeeded{"delete"}     = 1;
$ConfirmationNeeded{"delete_all"} = 1;
$ConfirmationNeeded{"empty"}      = 1;
$ConfirmationNeeded{"empty_all"}  = 1;

$UserClickedOn = '';
$ErrorMessage  = '';

if ($_POST["delete"]) {
	$UserClickedOn = "delete";
}

if ($_POST["delete_all"]) {
	$UserClickedOn = "delete_all";
}

if ($_POST["empty"]) {
	$UserClickedOn = "empty";
}

if ($_POST["empty_all"]) {
	$UserClickedOn = "empty_all";
}

if ($_POST["add"]) {
	$UserClickedOn = "add";
}

if ($_POST["rename"]) {
	$UserClickedOn = "rename";
}

if ($_POST["set_default"]) {
	$UserClickedOn = "set_default";
}

if ($_POST["set_options"]) {
	$UserClickedOn = "set_options";
}

#
# Error checking
#
if ($UserClickedOn) {
	if ($ConfirmationNeeded{$UserClickedOn}) {
		if ($_POST["confirm"] != $_POST[$UserClickedOn]) {
			$ErrorMessage = "You did not supply the confirmation text";
		}
	}

	if ($ErrorMessage == '') {
		switch ($UserClickedOn) {
			case "add":
				if (AddSlashes($_POST["add_name"]) == '') {
					$ErrorMessage = 'When creating a new list, you must supply a name.';
				}
					break;

			case "rename":
				if (AddSlashes($_POST["rename_name"]) == '') {
					$ErrorMessage = 'When renaming an existing list, you must supply a name.';
				}
					break;
		}
	}
	
}

if ($UserClickedOn != '' && $ErrorMessage == '') {
	if ($Debug) echo "you clicked on = '$UserClickedOn'<br>";
	if ($Debug) echo "your confirmation text = '" . AddSlashes($_POST["confirm"]) . "'<br>";

	# all went well, so let us do what they told us to do
	switch ($UserClickedOn) {
		case "add":
			$WatchList = new WatchList($db);
			$NewWatchListID = $WatchList->Create($UserID, AddSlashes($_POST["add_name"]));
			if ($Debug) echo 'I just created \'' . AddSlashes($_POST["add_name"]) . '\' with ID = \'' . $NewWatchListID . '\'';
			break;

		case "rename":
			# check valid new name
			# check only one watch list supplied
			if (count($_POST["watch_list_id"]) == 1) {
				list($key, $WatchListIDToRename) = each($_POST["watch_list_id"]);
				$WatchList = new WatchList($db);
				$NewName = $WatchList->Rename($WatchListIDToRename, $_POST["rename_name"]);
				if ($Debug) echo 'I have renamed your list to \'' . AddSlashes($_POST["rename_name"]) . '\'';
			} else {
				$ErrorMessage = 'Select exactly one watch list to be renamed.  I can\'t handle zero or more than one.';
			}
			break;

		case "delete_all":
		case "delete":
			pg_query($db, "BEGIN");
			$WatchList = new WatchList($db);
			while (list($key, $WatchListIDToDelete) = each($_POST["watch_list_id"])) {
				if ($Debug) echo "\$key='$key' \$WatchListIDToDelete='$WatchListIDToDelete'<br>";
				$DeletedWatchListID = $WatchList->Delete(AddSlashes($WatchListIDToDelete));
				if ($DeletedWatchListID != $WatchListIDToDelete) {
					die("Failed to deleted '$WatchListIDToDelete' (return value '$DeletedWatchListID')" . pg_last_error());
				}
				if ($Debug) echo 'I have deleted watch list id = ' . $WatchListIDToDelete . '<br>';
			}
			pg_query($db, "COMMIT");
			
			break;

		case "empty":
		case "empty_all":
			pg_query($db, "BEGIN");
			$WatchList = new WatchList($db);
			while (list($key, $WatchListIDToEmpty) = each($_POST["watch_list_id"])) {
				if ($Debug) echo "\$key='$key' \$WatchListIDToEmpty='$WatchListIDToEmpty'<br>";
				$EmptydWatchListID = $WatchList->EmptyTheList(AddSlashes($WatchListIDToEmpty));
				if ($EmptydWatchListID != $WatchListIDToEmpty) {
					die("Failed to Emptyd '$WatchListIDToEmpty' (return value '$EmptydWatchListID')" . pg_last_error());
				}
				if ($Debug) echo 'I have emptied watch list id = ' . $WatchListIDToEmpty . '<br>';
			}
			pg_query($db, "COMMIT");
			break;

		case "set_default":
			if ($Debug) echo 'I have set your default lists.<br>';
			pg_query($db, "BEGIN");
			$WatchLists = new WatchLists($db);
			$numrows = $WatchLists->In_Service_Set($UserID, $_POST["watch_list_id"]);
			if ($Debug) echo "$numrows watchlists were affected by that action";
			if ($numrows >= 0) {
				pg_query($db, "COMMIT");
			} else {
				pg_query($db, "ROLLBACK");
			}
			break;

		case "set_options":
			echo 'I would have set options to: ';
			if ($_POST["ask"])     echo 'ask';
			if ($_POST["default"]) echo 'default';
			if ($_POST["main"])    echo 'main';
			break;

		default:
			echo 'Hmmm, I have no idea what you asked me to do';
	}
}

?>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><td VALIGN=TOP>
<TABLE border="0">
<TR>
	<? freshports_PageBannerText("Watch list maintenance"); ?>
</TR>
<TR><TD>
<?php
	if ($ErrorMessage) {
		freshports_ErrorMessage("Let\'s try that again!", $ErrorMessage);
	}
?>

<TABLE WIDTH="100%" BORDER="1" CELLSPACING="0" CELLPADDING="5">
<TR><td nowrap><BIG><b>Watch Lists</b></BIG></td><td><BIG><b>Actions</b></BIG></td><td><BIG><b>Options</b></BIG></td></tr>
  <TR>
    <TD valign="top">
<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST" NAME=f>
<?php
	echo freshports_WatchListDDLB($db, $UserID, '', 10, TRUE);
?>
    </TD>
    <TD>
    <INPUT id=add         style="WIDTH: 85px; HEIGHT: 24px" type=submit size=48 value="Add"          name=add>&nbsp;&nbsp;&nbsp; 
    <INPUT id=add_name    name=add_name    size=10><BR>
    <INPUT id=rename      style="WIDTH: 85px; HEIGHT: 24px" type=submit size=23 value="Rename"       name=rename>&nbsp;&nbsp;&nbsp; 
    <INPUT id=rename_name name=rename_name size=10><BR>
    <br>
    <INPUT id=delete      style="WIDTH: 85px; HEIGHT: 24px" type=submit size=29 value="Delete"       name=delete><br>
    <INPUT id=delete_all  style="WIDTH: 85px; HEIGHT: 24px" type=submit size=29 value="Delete All"   name=delete_all><br>
    <br>
    <INPUT id=empty       style="WIDTH: 85px; HEIGHT: 24px" type=submit size=29 value="Empty"        name=empty><br>
    <INPUT id=empty_all   style="WIDTH: 85px; HEIGHT: 24px" type=submit size=29 value="Empty All"    name=empty_all><br>
    <br>
    <INPUT id=default     style="WIDTH: 85px; HEIGHT: 24px" type=submit size=29 value="Set Default"  name=set_default><br>
    <br>
    When deleting or emptying a row, you must confirm your action by typing
    the button name into this field (e.g. if you click on <b>Empty All</b>",
    you must type <b>Empty All</b> into the box below in order for the action
    to be completed).<br>
    Confirm : <INPUT id=confirm name=confirm size=10>
    </TD>
</form>
<td valign="top" nowrap>
When clicking on Add/Remove for a port,<br> the action should affect
<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST" NAME=f>
<INPUT id=selected type=radio name=main>&nbsp;the watch list only<BR>
<INPUT id=default  type=radio name=default>&nbsp;the default watch list[s]<BR>
<INPUT id=ask      type=radio name=ask>&nbsp;Ask for watch list name[s] each time<br>
<INPUT id=set_options style="WIDTH: 85px; HEIGHT: 24px" type=submit size=29 value="Set options"  name=set_options>
 </form>
</td>
    </TR>
</TABLE>
</TABLE>
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
			in any mail notification messages for this watch lists.  Names do not have to unique but it is advisable.
			Valid characters are: TBA
	
	<li><b>Rename</b> - rename a new watch list.  Select the watch list and supply the new name.
	
	<li><b>Delete</b> - Deletes the selected watch lists.  Confirmation of this action must be supplied in the 
			confirmation text box.  Be careful: this action cannot be undone.
	
	<li><b>Delete All</b> - Deletes all of your watch lists.  Confirmation of this action must be supplied in the 
			confirmation text box.  Be careful: this action cannot be undone.
	
	<li><b>Empty</b> - Empties the selected watch lists. Confirmation of this action must be supplied in the 
			confirmation text box.  Be careful: this action cannot be undone.
	
	<li><b>Empty All</b> - Empties all of your watch lists.  Confirmation of this action must be supplied in the 
			confirmation text box.  Be careful: this action cannot be undone.
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
</p>
</TD>
  <TD VALIGN="top" WIDTH="*" ALIGN="center">
    <?
       include($_SERVER['DOCUMENT_ROOT'] . "/include/side-bars.php");
    ?>
 </TD>
</TABLE>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include($_SERVER['DOCUMENT_ROOT'] . "/include/footer.php") ?>
</TD></TR>
</TABLE>

</BODY>
</HTML>
