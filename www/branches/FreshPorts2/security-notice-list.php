<?php
	#
	# $Id: security-notice-list.php,v 1.1.2.5 2004-02-13 17:45:29 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/watch-lists.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/security_notice.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/security_notice_audit.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/user_tasks.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/htmlify.php');

function MassageStatus($InStatus) {
	$OutStatus = '';
	switch ($InStatus) {
		case SECURITY_NOTICE_STATUS_ACTIVE:
		case SECURITY_NOTICE_STATUS_CANDIDATE:
		case SECURITY_NOTICE_STATUS_IGNORE:
			$OutStatus = $InStatus;
			break;

		default:
			die('unexpected value for Status: \'$InStatus\'');
	}

	return $OutStatus;	
}

	$PageTitle = 'Security Notice';

	$UserCanEdit = $User->IsTaskAllowed(FRESHPORTS_TASKS_SECURITY_NOTICE_ADD);

	freshports_Start($PageTitle,
					$FreshPortsName . ' - new ports, applications',
					'FreeBSD, index, applications, ports');
	$Debug = 0;
#	phpinfo();

	$message_id = AddSlashes($_REQUEST[message_id]);

	$SecurityNotice = new SecurityNotice($db);

	if (IsSet($_REQUEST[submit])) {
		$result = "ROLLBACK";

		$SecurityNotice->user_id     = $User->id;
		$SecurityNotice->ip_address  = AddSlashes($_SERVER['REMOTE_ADDR']);
		$SecurityNotice->description = AddSlashes($_REQUEST['description']);
		$SecurityNotice->status      = MassageStatus(AddSlashes($_REQUEST['status']));

		pg_exec($db, "BEGIN");
		if ($SecurityNotice->Create($message_id)) {
			if ($SecurityNotice->FetchByMessageID($message_id)) {
				$result = "COMMIT";
			}
		}

		pg_exec($db, $result);
		if ($result == "ROLLBACK") {
			unset($SecurityNotice->id);
		}
	} else {
		$SecurityNotice->FetchByStatus('');
	}
?>

<TABLE width="<? echo $TableWidth ?>" border="0" ALIGN="center">
<TR><TD VALIGN=TOP WIDTH="100%">
<TABLE WIDTH="100%" border="0">
<TR>
	<? echo freshports_PageBannerText($PageTitle); ?>
<TR><TD>
<?php
	if ($UserCanEdit) {
		echo "<p>\n";
		echo 'This page allows you to mark a commit as being security related.  Such commits will be included on the Security Notification report' . "\n";
		echo 'mailed out to users and marked with a <a href="/faq.php">security lock</a> whereever that commit appears.';
		echo "</p>\n";
	}
?>

</TD></TR>
<?php
	# now get all the changed values from before...
	$SecurityNotice = new SecurityNotice($db);
	$numrows = $SecurityNotice->FetchByStatus('');
	if ($numrows > 0) {
?>
<tr><td>
<table border=1 CELLSPACING="0" CELLPADDING="5">
<tr><td><b>Commit date</b></td><td><b>Commit Message</b></td><td><b>Date Added</b></td><td><b>Security Reason</b></td><td><b>User Name</b></td><td><b>IP Address</b></td><td><b>e-mail</b></td><td><b>status</b></td></tr>
<?php
	for ($i = 0; $i < $numrows; $i++) {
		$SecurityNotice->FetchNth($i);
?>
<tr>
<?php
		$HTML = '';
		$HTML .= '<td nowrap valign="top">' . FormatTime($SecurityNotice->commit_date, 0, "D, j M Y");

		$HTML .= ' <SMALL>';
		$HTML .= '[ ' . $SecurityNotice->commit_time . ' ' . $SecurityNotice->committer . ' ]';
		$HTML .= '</SMALL>';
		$HTML .= '&nbsp;';
		$HTML .= freshports_Email_Link($SecurityNotice->message_id);
		$HTML .= '&nbsp;&nbsp;'. freshports_Commit_Link($SecurityNotice->message_id);
		$HTML .= '</td><td>';


		$HTML .= freshports_PortDescriptionPrint($SecurityNotice->commit_description, $SecurityNotice->encoding_losses);

		$HTML .= '<td nowrap valign="top">' . $SecurityNotice->date_added   . '</td>';
		$HTML .= '<td nowrap valign="top">' . $SecurityNotice->description  . '</td>';
		$HTML .= '<td nowrap valign="top">' . $SecurityNotice->user_name    . '</td>';
		$HTML .= '<td valign="top">' . $SecurityNotice->ip_address   . '</td>';
		$HTML .= '<td valign="top">' . $SecurityNotice->user_email   . '</td>';
		$HTML .= '<td valign="top"><a href="/security-notice.php?message_id=' . 
                     $SecurityNotice->message_id . '">' . $SecurityNotice->StatusText() . '</a></td>';
		$HTML .= '</tr>';

	echo $HTML;
?>
<?php
	}  // end for
?>

</table>
<?php
	} // if ($numrows > 0) {
?>

<p>
<?php echo $SecurityFlag; ?>
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

</BODY>
</HTML>
