<?php
	#
	# $Id: announcements.php,v 1.1.2.1 2003-05-09 19:42:42 dan Exp $
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

if ($_REQUEST['Add']) {
   $Debug = 0;

	$Announcement = new Announcement($db);
	$Announcement->TextSet     ($_REQUEST['announcement']);
	$Announcement->StartDateSet($_REQUEST['start_date']);
	$Announcement->EndDateSet  ($_REQUEST['end_date']);

	$Announcement->Insert();
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
if ($AccountModified) {
   echo "Your account details were successfully updated.";
} else {

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
$HTML .= $Announcement->$title;
$HTML .= '</TEXTAREA>';
$HTML .= '</td>'  . "\n";

$HTML .= '<td valign="top">'  . "\n";
$HTML .= '<INPUT id="start_date" name="start_date" value="' . $Announcement->start_date . '" size=10>' . "\n";
$HTML .= '</td>'  . "\n";

$HTML .= '<td valign="top">'  . "\n";
$HTML .= '<INPUT id="end_date"   name="end_date"   value="' . $Announcement->end_date   . '" size=10>' . "\n";
$HTML .= '</td>'  . "\n";

$HTML .= '</tr>'  . "\n";

$HTML .= '<tr><td colspan="3">'  . "\n";
$HTML .= '<div align="center"><INPUT id="add" style="WIDTH: 85px; HEIGHT: 24px" type="submit" size="29" value="Add" name="Add"></div>' . "\n";
$HTML .= '</tr>'  . "\n";

$HTML .= '</table>' . "\n";

$HTML .= '</form>';

echo $HTML;

echo "<p></blockquote></TD>
</TR>
</TABLE>";
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
