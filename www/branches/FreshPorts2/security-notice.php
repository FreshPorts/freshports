<?
	# $Id: security-notice.php,v 1.1.2.1 2003-01-10 15:56:14 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/watch-lists.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/security_notice.php');

	$PageTitle = 'Security Notice';

	freshports_Start($PageTitle,
					$FreshPortsName . ' - new ports, applications',
					'FreeBSD, index, applications, ports');
	$Debug = 0;
#	phpinfo();

	// if we don't know who they are, we'll make sure they login first
	if (!$visitor) {
		header("Location: login.php?origin=" . $_SERVER["PHP_SELF"]);  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}

	$message_id = AddSlashes($_REQUEST[message_id]);

	$SecurityNotice = new SecurityNotice($db);

	if (IsSet($_REQUEST[submit])) {
		$description = AddSlashes($_REQUEST[description]);
		$SecurityNotice->Create($User->id, $message_id, $description, $_SERVER[REMOTE_ADDR]);
	}

	$SecurityNotice->FetchByMessageID($message_id);
?>

<TABLE width="<? echo $TableWidth ?>" border="0" ALIGN="center">
<TR><TD VALIGN=TOP>
<TABLE WIDTH="100%" border="0">
<TR>
	<? echo freshports_PageBannerText($PageTitle); ?>
<TR><TD>
This page allows you to mark a commit as being security related.  Such commits will be included on the Security Notification report
mailed out to users and marked with a <a href="/faq.php">security lock</a> whereever that commit appears.
<?
?>
</TD><TR>
<TR><TD>
	<?
	$Debug = 0;

	# you can only be here if you are logged in!
	$visitor = $_COOKIE["visitor"];
	if (!$visitor) {
		?>
		<P>
		You must <A HREF="login.php?origin=<?echo $_SERVER["PHP_SELF"] ?>">login</A> before you can come here.
		</P>
		<?
 	} else {
		$Commit = new Commit($db);
		$Commit->FetchByMessageId($message_id);

		$HTML .= '<TR><TD COLSPAN="3" BGCOLOR="#AD0040" HEIGHT="0">' . "\n";
		$HTML .= '   <FONT COLOR="#FFFFFF"><BIG>' . FormatTime($Commit->commit_date, 0, "D, j M Y") . '</BIG></FONT>' . "\n";
		$HTML .= '</TD></TR>' . "\n\n";

		$HTML .= "<TR><TD>\n";

		$HTML .= '<SMALL>';
		$HTML .= '[ ' . $Commit->commit_time . ' ' . $Commit->committer . ' ]';
		$HTML .= '</SMALL>';
		$HTML .= '&nbsp;';
		$HTML .= freshports_Email_Link($Commit->message_id);

		$HTML .= "\n<BLOCKQUOTE>\n";
		$HTML .= freshports_PortDescriptionPrint($Commit->commit_description, $Commit->encoding_losses);
		$HTML .= "\n</BLOCKQUOTE>\n</TD></TR>\n\n\n";

		echo $HTML;
?>
<tr><td height=20>
<hr width="97%" align="center">
</tr></td>

<tr><td>
<p>
Please enter your reasoning for marking the above commit as a security issue.
</p>

<FORM ACTION="<? echo $_SERVER["PHP_SELF"]; ?>" method="POST">
	<TEXTAREA NAME="description" ROWS="10" COLS=60"<?php
	if (IsSet($SecurityNotice->description)) echo ' readonly'; ?>><?php
	if (IsSet($SecurityNotice->description)) {
		echo $SecurityNotice->description;
	} else {
		echo 'enter security issue description here';
	}
?></TEXTAREA>
	<BR>
<?php
	if (!IsSet($SecurityNotice->id)) {
?>
	<INPUT TYPE="submit" VALUE="Save Security Info" NAME="submit">
<?php
	}
?>
	<INPUT TYPE="hidden" NAME="message_id" VALUE="<? echo $message_id; ?>">
</FORM>
<?php
	if (IsSet($SecurityNotice->id)) {
		echo freshports_Security_Icon() . 'This commit is set as security related';
	}
?>
</td></tr>
<?php
	}
	?>
</TD>
</TR>
</TABLE>
</TD>

	<?
	freshports_SideBar();
	?>

</TR>
</TABLE>

<?
freshports_ShowFooter();
?>

</BODY>
</HTML>
