<?php
	#
	# $Id: security-notice.php,v 1.1.2.12 2004-02-13 15:13:40 dan Exp $
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
			die("unexpected value for Status: '$InStatus'");
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
		$SecurityNotice->FetchByMessageID($message_id);
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
<p>
<?php

	if ($SecurityNotice->status == SECURITY_NOTICE_STATUS_ACTIVE) {
		$SecurityFlag = freshports_Security_Icon() . '<b>This commit is set as security related.</b>';
	} else {
		if ($SecurityNotice->status == SECURITY_NOTICE_STATUS_CANDIDATE) {
			$SecurityFlag = '<b>This commit is a security notice candidate.</b>';
		} else {
			$SecurityFlag = '<b>This commit is not security related.</b>';
		}
	}

	echo $SecurityFlag;

?>
</p>
</TD></TR>
<?php
	$Debug = 0;

	$Commit = new Commit($db);
	if ($Commit->FetchByMessageId($message_id) == $message_id) {

		$HTML .= '<TR><TD COLSPAN="3" BGCOLOR="#AD0040" HEIGHT="0">' . "\n";
		$HTML .= '   <FONT COLOR="#FFFFFF"><BIG>' . FormatTime($Commit->commit_date, 0, "D, j M Y") . '</BIG></FONT>' . "\n";
		$HTML .= '</TD></TR>' . "\n\n";

		$HTML .= "<TR><TD>\n";

		$HTML .= '<SMALL>';
		$HTML .= '[ ' . $Commit->commit_time . ' ' . $Commit->committer . ' ]';
		$HTML .= '</SMALL>';
		$HTML .= '&nbsp;';
		$HTML .= freshports_Email_Link($Commit->message_id);
		$HTML .= '&nbsp;&nbsp;'. freshports_Commit_Link($Commit->message_id);


		$HTML .= "\n<BLOCKQUOTE>\n";
		$HTML .= freshports_PortDescriptionPrint($Commit->commit_description, $Commit->encoding_losses);
		$HTML .= "\n</BLOCKQUOTE>\n</TD></TR>\n\n\n";

		$HTML .= '<tr><td><hr width="97%" align="center"></td></tr>';
	} else {
		$HTML = '<TR><TD>I did not find that commit</TD></TR>';
	}

	echo $HTML;
?>

<tr><td>
<?php

	if (IsSet($SecurityNotice->id)) {
		echo '<h2>Notification reason</h2>' . "\n";
	}

	if ($UserCanEdit) {
?>
<p>
Please enter your reasoning for marking the above commit as a security issue.
</p>

<FORM ACTION="<? echo $_SERVER["PHP_SELF"] . '?message_id=' . $message_id; ?>" method="POST">
	<TEXTAREA NAME="description" ROWS="10" COLS="60"><?php
	if (IsSet($SecurityNotice->description)) {
		echo $SecurityNotice->description;
	} else {
		echo 'enter security issue description here';
	}
?></TEXTAREA>
	<BR>
	<INPUT TYPE="radio" <? if ($SecurityNotice->status == SECURITY_NOTICE_STATUS_ACTIVE)    echo 'CHECKED'; ?> VALUE=<?php echo SECURITY_NOTICE_STATUS_ACTIVE;    ?> NAME=status> Security related<br>
	<INPUT TYPE="radio" <? if ($SecurityNotice->status == SECURITY_NOTICE_STATUS_CANDIDATE) echo 'CHECKED'; ?> VALUE=<?php echo SECURITY_NOTICE_STATUS_CANDIDATE; ?> NAME=status> Candidate<br>
	<INPUT TYPE="radio" <? if ($SecurityNotice->status == SECURITY_NOTICE_STATUS_IGNORE)    echo 'CHECKED'; ?> VALUE=<?php echo SECURITY_NOTICE_STATUS_IGNORE;    ?> NAME=status> Ignore<br>
	<INPUT TYPE="submit" VALUE="Save Security Info" NAME="submit">
	<INPUT TYPE="hidden" NAME="message_id" VALUE="<? echo $message_id; ?>">
</FORM>
<?php
	} else {
		echo htmlify(htmlspecialchars($SecurityNotice->commit_description)) . "\n";
	}

	if (IsSet($SecurityNotice->id) && $UserCanEdit) {
		$UserAlt = new User($db);
		$UserAlt->Fetch($SecurityNotice->user_id);
?>
<h2>Audit trail</h2>
<table border=1 CELLSPACING="0" CELLPADDING="5">
<tr><td><b>Date marked</b></td><td><b>Notification Reaason</b></td><td><b>User Name</b></td><td><b>IP Address</b></td><td><b>e-mail</b></td><td><b>status</b></td></tr>
<tr>
<td><?php echo $SecurityNotice->date_added;  ?></td>
<td><?php echo $SecurityNotice->description; ?></td>
<td><?php echo $SecurityNotice->user_name;   ?></td>
<td><?php echo $SecurityNotice->ip_address;  ?></td>
<td><?php echo $SecurityNotice->user_email;  ?></td>
<td><?php echo $SecurityNotice->status;      ?></td>
</tr>

<?php
	# now get all the changed values from before...
	$SecurityNoticeAudit = new SecurityNoticeAudit($db);
	$numrows = $SecurityNoticeAudit->FetchByMessageID($message_id);
	for ($i = 0; $i < $numrows; $i++) {
		$SecurityNoticeAudit->FetchNth($i);
?>
<tr>
<td><?php echo $SecurityNoticeAudit->date_added;  ?></td>
<td><?php echo $SecurityNoticeAudit->description; ?></td>
<td><?php echo $SecurityNoticeAudit->user_name;   ?></td>
<td><?php echo $SecurityNoticeAudit->ip_address;  ?></td>
<td><?php echo $SecurityNoticeAudit->user_email;  ?></td>
<td><?php echo $SecurityNoticeAudit->status;      ?></td>
</tr>
<?php
	}  // end for
?>

</table>

<?php
} // User Can Edit
?>

<p>
<?php echo $SecurityFlag; ?>
</p>
</TD>
</TR>
</TABLE>
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
