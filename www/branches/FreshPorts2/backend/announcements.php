<?php
	#
	# $Id: announcements.php,v 1.1.2.2 2003-05-09 21:33:00 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/user_tasks.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/announcements.php');

	GLOBAL $User;

$origin	= $_REQUEST['origin'];
$submit 	= $_REQUEST['submit'];
$visitor	= $_COOKIE['visitor'];

if ($origin == '/index.php' || $origin == '') {
	$origin = '/';
}

// if we don't know who they are, we'll make sure they login first
if (!$visitor) {
	header('Location: /login.php?origin=' . $_SERVER['PHP_SELF']);  /* Redirect browser to PHP web site */
	exit;  /* Make sure that code below does not get executed when we redirect. */
}

if (!$User->IsTaskAllowed(FRESHPORTS_TASKS_ANNOUNCEMENTS_MAINTAIN)) {
	die("I'm sorry, but you're not allowed to be here.  The police have been notified.  Please leave now.");
}

$Announcement = new Announcement($db);

phpinfo();
die();
if (IsSet($_REQUEST['Add'])) {
	$Announcement->TextSet     (AddSlashes($_REQUEST['announcement']));
	$Announcement->StartDateSet(AddSlashes($_REQUEST['start_date']));
	$Announcement->EndDateSet  (AddSlashes($_REQUEST['end_date']));

	$Announcement->Insert();
}

if (IsSet($_REQUEST['Update'])) {
	$Announcement->TextSet     (AddSlashes($_REQUEST['announcement']));
	$Announcement->StartDateSet(AddSlashes($_REQUEST['start_date']));
	$Announcement->EndDateSet  (AddSlashes($_REQUEST['end_date']));

	$Announcement->Update();
}

# create another new one so after a save, we don't redisplay the old one.
$Announcement = new Announcement($db);

if (IsSet($_REQUEST['id'])) {
	$Announcement->Fetch(AddSlashes($_REQUEST['id']));
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


if ($errors) {
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

echo '<TABLE CELLPADDING="1" CELLSPACING="3" BORDER="0" BGCOLOR="#AD0040" WIDTH="100%">
<TR>
<TD BGCOLOR="#AD0040" COLSPAN="1"><FONT COLOR="#FFFFFF"><BIG><BIG>' . $Title . '</BIG></BIG></FONT></TD>
</TR>
<TR BGCOLOR="#ffffff">
<TD>';

echo 'Current annoucements<blockquote>';

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
$HTML .= '<INPUT id="start_date" name="start_date" value="' . $Announcement->StartDateGet() . '" size=10>' . "\n";
$HTML .= '</td>'  . "\n";

$HTML .= '<td valign="top">'  . "\n";
$HTML .= '<INPUT id="end_date"   name="end_date"   value="' . $Announcement->EndDateGet()   . '" size=10>' . "\n";
$HTML .= '</td>'  . "\n";

$HTML .= '</tr>'  . "\n";

$ControlName  = IsSet($_REQUEST['id']) ? 'update' : 'add';
$ControlValue = IsSet($_REQUEST['id']) ? 'Update' : 'Add';

$HTML .= '<tr><td colspan="3">'  . "\n";
$HTML .= '<div align="center"><INPUT id="' . $ControlName . '" style="WIDTH: 85px; HEIGHT: 24px" type="submit" size="29" value="' . $ControlValue . '"';
$HTML .= ' name="' . $ControlName . '"></div>' . "\n";
$HTML .= '</tr>'  . "\n";

$HTML .= '</table>' . "\n";

$HTML .= '</form>';

echo $HTML;

echo "<p></blockquote></TD>
</TR>
</TABLE>";

$NumRows = $Announcement->FetchAllActive();

if ($NumRows > 0) {
	echo '<blockquote>' . "\n";
	echo '<h2>Existing Announcements</h2>' . "\n";
	echo '<table cellpadding="4" cellspacing="0" border="1">' . "\n";
	echo '<tr><td><b>Announcement Text</b></td><td><b>Start Date</b></td><td><b>End Date</b></td><td><b>Edit</b></td></tr>' . "\n";
	for ($i = 0; $i < $NumRows; $i++) {
		$Announcement->FetchNth($i);
		echo '<tr>' . "\n";
		echo '<td>' . $Announcement->TextGet()      . '</td>';
		echo '<td>' . ($Announcement->StartDateGet() != '' ? $Announcement->StartDateGet() : '&nbsp') . '</td>';
		echo '<td>' . ($Announcement->EndDateGet()   != '' ? $Announcement->EndDateGet()   : '&nbsp') . '</td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF']  . '?id=' . $Announcement->IDGet() . '">Edit</a></td>';
      echo '</tr>' . "\n";
	}
	echo '</table>' . "\n";
	echo '</blockquote>' . "\n";
} else {
	echo 'There are no active announcements';
}



?>

<p>

</TD>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
	<?
	freshports_SideBar();
	?>
  </td>

</TR>
</TABLE>

<?
freshports_ShowFooter();
?>

</body>
</html>
