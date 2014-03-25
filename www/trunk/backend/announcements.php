<?php
	#
	# $Id: announcements.php,v 1.3 2007-04-06 16:09:03 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/user_tasks.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/announcements.php');

	GLOBAL $User;

$origin		= isset($_REQUEST['origin']) ? $_REQUEST['origin'] : null;
$submit 	= isset($_REQUEST['submit']) ? $_REQUEST['submit'] : null;
$visitor	= isset($_COOKIE['visitor']) ? $_COOKIE['visitor'] : null;

if ($origin == '/index.php' || $origin == '') {
	$origin = '/';
}

// if we don't know who they are, we'll make sure they login first
if (!$User->id) {
	header('Location: /login.php?origin=' . $_SERVER['PHP_SELF']);  /* Redirect browser to PHP web site */
	exit;  /* Make sure that code below does not get executed when we redirect. */
}

if (!$User->IsTaskAllowed(FRESHPORTS_TASKS_ANNOUNCEMENTS_MAINTAIN)) {
	die("I'm sorry, but you're not allowed to be here.  The police have been notified.  Please leave now.");
}

if (IsSet($_REQUEST['add'])) {
	$Announcement = new Announcement($db);

	$Announcement->TextSet     ($_REQUEST['announcement']);
	$Announcement->TextPlainSet($_REQUEST['announcement_plain']);
	$Announcement->StartDateSet($_REQUEST['start_date']);
	$Announcement->EndDateSet  ($_REQUEST['end_date']);

	$Announcement->Insert();
	Unset($Announcement);
}

if (IsSet($_REQUEST['update'])) {
	$Announcement = new Announcement($db);

	$Announcement->TextSet     ($_REQUEST['announcement']);
	$Announcement->TextPlainSet($_REQUEST['announcement_plain']);
	$Announcement->StartDateSet($_REQUEST['start_date']);
	$Announcement->EndDateSet  ($_REQUEST['end_date']);
	$Announcement->IDSet       ($_REQUEST['id']);

	$Announcement->Update();
	Unset($Announcement);
}

if (IsSet($_REQUEST['delete'])) {
	$Announcement = new Announcement($db);

	$Announcement->IDSet($_REQUEST['delete']);

	$Announcement->Delete();
	Unset($Announcement);
}

# create another new one
$Announcement = new Announcement($db);

if (IsSet($_REQUEST['edit'])) {
	$Announcement->Fetch($_REQUEST['edit']);
}

	#echo '<br>the page size is ' . $page_size . ' : ' . $email;
	$Title = 'Announcements';
   freshports_Start($Title,
               'freshports - new ports, applications',
               'FreeBSD, index, applications, ports');
?>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD VALIGN="top" width="100%">
<?php


if (isset($errors)) {
echo '
  <TABLE width="100%" CELLPADDING="3" BORDER="0">
  <TR VALIGN=top>
   <TD><img src="/images/warning.gif"></TD>
   <TD width="100%">
  <p>Some errors have occurred which must be corrected before your login can be created.</p>';

echo $errors;

echo '<p>If you need help, please post a message on the forum. </p>
 </TD>
 </TR>
 </TABLE>
<br>';
}

echo '<TABLE CELLPADDING="1" CELLSPACING="3" BORDER="0" BGCOLOR="' . BACKGROUND_COLOUR . '" WIDTH="100%">
<TR>
<TD BGCOLOR="' . BACKGROUND_COLOUR . '" COLSPAN="1"><FONT COLOR="#FFFFFF"><BIG><BIG>' . $Title . '</BIG></BIG></FONT></TD>
</TR>
<TR BGCOLOR="#ffffff">
<TD>';

echo 'Current annoucements<blockquote>';

$HTML  = '';
$HTML .= '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">' . "\n";

$HTML .= '<table cellpadding="4" cellspacing="0" border="1">' . "\n";

$HTML .= '<tr><td><b>Announcement Text (can be HTML)</b></td><td><b>Start Date</b></td><td><b>End Date</b></td></tr>' . "\n";

$HTML .= '<tr>'  . "\n";

$HTML .= '<td>'  . "\n";
$HTML .= '<TEXTAREA NAME="announcement" ROWS="10" COLS="60">'          . "\n";
$HTML .= $Announcement->TextGet();
$HTML .= '</TEXTAREA>';
$HTML .= '</td>'  . "\n";

$HTML .= '<td valign="top">'  . "\n";
$HTML .= '<INPUT id="start_date" name="start_date" value="' . $Announcement->StartDateGet() . '" size=25>' . "\n";
$HTML .= '</td>'  . "\n";

$HTML .= '<td valign="top">'  . "\n";
$HTML .= '<INPUT id="end_date"   name="end_date"   value="' . $Announcement->EndDateGet()   . '" size=25>' . "\n";
$HTML .= '</td>'  . "\n";

$HTML .= '</tr>'  . "\n";

$HTML .= '<tr>'  . "\n";

$HTML .= '<td><b>Plain text Version</b>'  . "\n";
$HTML .= '<TEXTAREA NAME="announcement_plain" ROWS="10" COLS="60">'          . "\n";
$HTML .= htmlspecialchars($Announcement->TextPlainGet());
$HTML .= '</TEXTAREA>';
$HTML .= '</td>'  . "\n";
$HTML .= '</tr>'  . "\n";


$ControlName  = IsSet($_REQUEST['edit']) ? 'update' : 'add';
$ControlValue = IsSet($_REQUEST['edit']) ? 'Update' : 'Add';

$HTML .= '<tr><td colspan="3">'  . "\n";
$HTML .= '<div align="center"><INPUT id="' . $ControlName . '" style="WIDTH: 85px; HEIGHT: 24px" type="submit" size="29" value="' . $ControlValue . '"';
$HTML .= ' name="' . $ControlName . '"></div>' . "\n";
$HTML .= '</tr>'  . "\n";

$HTML .= '</table>' . "\n";

$HTML .= '<INPUT NAME="id" TYPE="hidden" value="' . $Announcement->IDGet(). '">' . "\n";

$HTML .= '</form>';

echo $HTML;

echo "<p></blockquote></TD>
</TR>
</TABLE>";

function MyDisplayAnnouncements($Announcement) {
        $HTML = '';
	$HTML .= '<table cellpadding="4" cellspacing="0" border="1">' . "\n";
	$HTML .= '<tr><td><b>Announcement Text</b></td><td><b>Start Date</b></td><td><b>End Date</b></td><td><b>Edit</b></td><td><b>Delete</b</td></tr>' . "\n";

	$NumRows = $Announcement->NumRows();

	for ($i = 0; $i < $NumRows; $i++) {
		$Announcement->FetchNth($i);
		$HTML .= '<tr>' . "\n";
		$HTML .= '<td>' . $Announcement->TextGet()      . '</td>';
		$HTML .= '<td>' . ($Announcement->StartDateGet() != '' ? $Announcement->StartDateGet() : '&nbsp') . '</td>';
		$HTML .= '<td>' . ($Announcement->EndDateGet()   != '' ? $Announcement->EndDateGet()   : '&nbsp') . '</td>';
		$HTML .= '<td><a href="' . $_SERVER['PHP_SELF']  . '?edit='   . $Announcement->IDGet() . '">Edit</a></td>';
		$HTML .= '<td><a href="' . $_SERVER['PHP_SELF']  . '?delete=' . $Announcement->IDGet() . '">Delete</a></td>';
      $HTML .= '</tr>' . "\n";
	}
	$HTML .= '</table>' . "\n";

	return $HTML;
}

$NumRows = $Announcement->FetchAll();

echo DisplayAnnouncements($Announcement);

if ($NumRows > 0) {
	echo '<blockquote>'  . "\n";
	echo '<h2>Existing Announcements</h2>' . "\n";
	echo MyDisplayAnnouncements($Announcement);
	echo '</blockquote>' . "\n";

	$NumRows = $Announcement->FetchAllActive();
	if ($NumRows > 0) {
		echo '<blockquote>'  . "\n";
		echo '<h2>Active Announcements</h2>' . "\n";
		echo MyDisplayAnnouncements($Announcement);
		echo '</blockquote>' . "\n";


	} else {
		echo '<p>There are no active announcements.</p>';
	}

} else {
	echo '<p>There are no announcements.</p>';
}



?>

<p>

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

</body>
</html>
