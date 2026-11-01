<?php
	#
	# $Id: announcements.php,v 1.3 2007-04-06 16:09:03 dan Exp $
	#
	# Copyright (c) 1998-2026 Dan Langille
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/user_tasks.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/announcements.php');

	GLOBAL $User;

// if we don't know who they are, we'll make sure they login first
if (!$User->id) {
	header('Location: /login.php');  /* Redirect browser to PHP web site */
	exit;  /* Make sure that code below does not get executed when we redirect. */
}

if (!$User->IsTaskAllowed(FRESHPORTS_TASKS_ANNOUNCEMENTS_MAINTAIN)) {
	http_response_code(403);

	freshports_Start('Announcements',
	                 'freshports - new ports, applications',
	                 'FreeBSD, index, applications, ports');

	echo freshports_MainTable();
?>
<tr>
	<td class="content">
	<p>I'm sorry, but you're not allowed to be here.  The police have been notified.  Please leave now.</p>
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

</body>
</html>
<?php
	exit;
}

#
# A date is either empty (no start / no end) or a full date, optionally
# with a time: YYYY-MM-DD, YYYY-MM-DD HH:MM, or YYYY-MM-DDTHH:MM (what
# datetime-local sends), with optional seconds.  Anything else, such as
# a bare time, would be rejected by PostgreSQL with a raw SQL error.
#
function AnnouncementDateValid($value) {
	if ($value == '') {
		return true;
	}

	if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})(?:[T ](\d{2}):(\d{2})(?::(\d{2}))?)?$/', $value, $matches)) {
		return false;
	}

	if (!checkdate(intval($matches[2]), intval($matches[3]), intval($matches[1]))) {
		return false;
	}

	if (isset($matches[4]) && (intval($matches[4]) > 23 || intval($matches[5]) > 59 || (isset($matches[6]) && intval($matches[6]) > 59))) {
		return false;
	}

	return true;
}

#
# datetime-local inputs need YYYY-MM-DDTHH:MM; the database gives us
# something like 2026-11-01 20:51:00+00.  Keep the wall-clock time as
# the database reported it.
#
function AnnouncementDateForInput($value) {
	# DateTime would turn a rejected "20:51" into today at 20:51; don't.
	if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $value ?? '')) {
		return '';
	}

	try {
		$date = new DateTime($value);
	} catch (Exception $e) {
		return '';
	}

	return $date->format('Y-m-d\TH:i');
}

function AnnouncementDatesCheck($start_date, $end_date) {
	$errors = '';

	if (!AnnouncementDateValid($start_date)) {
		$errors .= '<p>The start date "' . htmlspecialchars($start_date) . '" is not a valid date. Use YYYY-MM-DD HH:MM, or leave it empty.</p>' . "\n";
	}

	if (!AnnouncementDateValid($end_date)) {
		$errors .= '<p>The end date "' . htmlspecialchars($end_date) . '" is not a valid date. Use YYYY-MM-DD HH:MM, or leave it empty.</p>' . "\n";
	}

	if ($errors == '' && $start_date != '' && $end_date != ''
	    && str_replace('T', ' ', $end_date) < str_replace('T', ' ', $start_date)) {
		$errors .= '<p>The end date is before the start date.</p>' . "\n";
	}

	return $errors;
}

if (IsSet($_REQUEST['add'])) {
	$Announcement = new Announcement($db);

	$Announcement->TextSet     ($_REQUEST['announcement']);
	$Announcement->TextPlainSet($_REQUEST['announcement_plain']);
	$Announcement->StartDateSet(trim($_REQUEST['start_date']));
	$Announcement->EndDateSet  (trim($_REQUEST['end_date']));

	$DateErrors = AnnouncementDatesCheck($Announcement->StartDateGet(), $Announcement->EndDateGet());
	if ($DateErrors != '') {
		# keep $Announcement so the form shows what was submitted
		$errors = $DateErrors;
	} else {
		$Announcement->Insert();
		Unset($Announcement);
	}
}

if (IsSet($_REQUEST['update'])) {
	$Announcement = new Announcement($db);

	$Announcement->TextSet     ($_REQUEST['announcement']);
	$Announcement->TextPlainSet($_REQUEST['announcement_plain']);
	$Announcement->StartDateSet(trim($_REQUEST['start_date']));
	$Announcement->EndDateSet  (trim($_REQUEST['end_date']));
	$Announcement->IDSet       (intval($_REQUEST['id']));

	$DateErrors = AnnouncementDatesCheck($Announcement->StartDateGet(), $Announcement->EndDateGet());
	if ($DateErrors != '') {
		# keep $Announcement so the form shows what was submitted
		$errors = $DateErrors;
	} else {
		$Announcement->Update();
		Unset($Announcement);
	}
}

if (IsSet($_REQUEST['delete'])) {
	$Announcement = new Announcement($db);

	$Announcement->IDSet(intval(pg_escape_string($db, $_REQUEST['delete'])));

	$Announcement->Delete();
	Unset($Announcement);
}

# create another new one, unless we are redisplaying one which had errors
if (!isset($errors)) {
	$Announcement = new Announcement($db);
}

if (IsSet($_REQUEST['edit'])) {
	$Announcement->Fetch(intval(pg_escape_string($db, $_REQUEST['edit'])));
}

	#echo '<br>the page size is ' . $page_size . ' : ' . $email;
	$Title = 'Announcements';
   freshports_Start($Title,
               'freshports - new ports, applications',
               'FreeBSD, index, applications, ports');
?>

<table class="fullwidth borderless">
<tr><td class="content">
<?php


if (isset($errors)) {
echo '
  <table class="fullwidth borderless cellpadding3">
  <tr class="vtop">
   <td><img src="/images/warning.gif" alt="Warning"></td>
   <td class="fullwidth">
  <p>Some errors have occurred which must be corrected before your announcement can be saved.</p>';

echo $errors;

echo '
 </td>
 </tr>
 </table>
<br>';
}

echo '<table class="fullwidth borderless" style="border-spacing: 3px">
<tr>
<td class="accent"><big>' . $Title . '</big></td>
</tr>
<tr>
<td>';

echo 'Current annoucements<blockquote>';

$HTML  = '';
$HTML .= '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">' . "\n";

$HTML .= '<table class="cellpadding4 bordered">' . "\n";

$HTML .= '<tr><td><b>Announcement Text (can be HTML)</b></td><td><b>Start Date</b></td><td><b>End Date</b></td></tr>' . "\n";

$HTML .= '<tr>'  . "\n";

$HTML .= '<td>'  . "\n";
$HTML .= '<TEXTAREA NAME="announcement" ROWS="10" COLS="60">'          . "\n";
$HTML .= $Announcement->TextGet();
$HTML .= '</TEXTAREA>';
$HTML .= '</td>'  . "\n";

$HTML .= '<td class="vtop">'  . "\n";
$HTML .= '<INPUT id="start_date" name="start_date" type="datetime-local" value="' . AnnouncementDateForInput($Announcement->StartDateGet()) . '">' . "\n";
$HTML .= '</td>'  . "\n";

$HTML .= '<td class="vtop">'  . "\n";
$HTML .= '<INPUT id="end_date"   name="end_date"   type="datetime-local" value="' . AnnouncementDateForInput($Announcement->EndDateGet())   . '">' . "\n";
$HTML .= '</td>'  . "\n";

$HTML .= '</tr>'  . "\n";

$HTML .= '<tr>'  . "\n";

$HTML .= '<td colspan="3"><b>Plain text Version</b>'  . "\n";
$HTML .= '<TEXTAREA NAME="announcement_plain" ROWS="10" COLS="60">'          . "\n";
$HTML .= htmlspecialchars($Announcement->TextPlainGet() ?? '');
$HTML .= '</TEXTAREA>';
$HTML .= '</td>'  . "\n";
$HTML .= '</tr>'  . "\n";


$ControlName  = $Announcement->IDGet() ? 'update' : 'add';
$ControlValue = $Announcement->IDGet() ? 'Update' : 'Add';

$HTML .= '<tr><td colspan="3">'  . "\n";
$HTML .= '<div class="vcentered"><INPUT id="' . $ControlName . '" style="WIDTH: 85px; HEIGHT: 24px" type="submit" size="29" value="' . $ControlValue . '"';
$HTML .= ' name="' . $ControlName . '"></div>' . "\n";
$HTML .= '</tr>'  . "\n";

$HTML .= '</table>' . "\n";

$HTML .= '<INPUT NAME="id" TYPE="hidden" value="' . $Announcement->IDGet(). '">' . "\n";

$HTML .= '</form>';

echo $HTML;

echo "<p></blockquote></td>
</tr>
</table>";

function MyDisplayAnnouncements($Announcement) {
        $HTML = '';
	$HTML .= '<table class="cellpadding4 bordered">' . "\n";
	$HTML .= '<tr><td><b>Announcement Text</b></td><td><b>Start Date</b></td><td><b>End Date</b></td><td><b>Edit</b></td><td><b>Delete</b</td></tr>' . "\n";

	$NumRows = $Announcement->NumRows();

	for ($i = 0; $i < $NumRows; $i++) {
		$Announcement->FetchNth($i);
		$HTML .= '<tr>' . "\n";
		$HTML .= '<td>' . $Announcement->TextGet()      . '</td>';
		$HTML .= '<td>' . ($Announcement->StartDateGet() != '' ? $Announcement->StartDateGet() : '&nbsp;') . '</td>';
		$HTML .= '<td>' . ($Announcement->EndDateGet()   != '' ? $Announcement->EndDateGet()   : '&nbsp;') . '</td>';
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

</body>
</html>
