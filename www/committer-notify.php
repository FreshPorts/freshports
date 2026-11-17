<?php
	#
	# Copyright (c) 2026 Dan Langille
	#
	# The page behind the links mailed to <login>@FreeBSD.org: the one in the
	# confirmation mail from committer-opt-in.php, and the one at the end of
	# every sanity-test mail from ingress.  See include/committer-notify.php.
	#
	# No login is needed: whoever has the link has the mailbox.  Opening the
	# link changes nothing; only the buttons do (a POST carrying the token),
	# because mail filters and link scanners open links on their own.
	#
	#   pending, link not expired: Confirm, or Refuse (removes the request)
	#   active:                    Stop these messages
	#
	# Stopping removes every opt-in for that login, not only this one, so a
	# committer who was opted in from several accounts gets no more mail.
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/committer-notify.php');

	if (IN_MAINTENANCE_MODE) {
		header('Location: /' . MAINTENANCE_PAGE, TRUE, 307);
		exit;
	}

	$Token  = $_REQUEST['token'] ?? '';
	$Action = (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') ? (string) ($_POST['action'] ?? '') : '';

	$Message = '';

	if (!freshports_CommitterNotifyTokenValid($Token)) {
		$Token = '';
	}

	if ($Token !== '' && $Action === 'confirm') {
		$sql = "UPDATE committer_notify
		           SET status = 'A'
		         WHERE token  = $1
		           AND status = 'P'
		           AND token_created > now() - make_interval(days => $2)
		     RETURNING committer";
		$result = pg_query_params($db, $sql, array($Token, COMMITTER_NOTIFY_CONFIRM_DAYS)) or die(freshports_QueryFailed($db));
		if (pg_num_rows($result)) {
			$Committer = pg_fetch_result($result, 0, 'committer');
			syslog(LOG_NOTICE, "committer opt-in confirmed for '$Committer'");
			$Message = sprintf(_('Confirmed.  FreshPorts will now mail %s@FreeBSD.org when it has trouble processing a port change you committed.'), $Committer);
		}
	}

	if ($Token !== '' && ($Action === 'refuse' || $Action === 'stop')) {
		$sql = "DELETE FROM committer_notify
		         WHERE lower(committer) = (SELECT lower(committer) FROM committer_notify WHERE token = $1)
		     RETURNING committer";
		$result = pg_query_params($db, $sql, array($Token)) or die(freshports_QueryFailed($db));
		if (pg_num_rows($result)) {
			$Committer = pg_fetch_result($result, 0, 'committer');
			syslog(LOG_NOTICE, "committer opt-in removed by its link for '$Committer': " . pg_num_rows($result) . " row(s)");
			$Message = sprintf(_('Done.  FreshPorts will not mail %s@FreeBSD.org about your commits.'), $Committer);
		}
	}

	# what the link is for now, after any change above
	$Row = null;
	if ($Token !== '') {
		$sql = "SELECT committer, status,
		               token_created > now() - make_interval(days => $2) AS fresh
		          FROM committer_notify
		         WHERE token = $1";
		$result = pg_query_params($db, $sql, array($Token, COMMITTER_NOTIFY_CONFIRM_DAYS)) or die(freshports_QueryFailed($db));
		if (pg_num_rows($result)) {
			$Row = pg_fetch_array($result, 0);
		}
	}

	$Title = _('Committer notifications');
	freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');

	echo freshports_MainTable();
?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(NOBORDER); ?>
<tr>
	<?php echo freshports_PageBannerText($Title); ?>
</tr>

<tr><td class="textcontent">
<?php
	if ($Message !== '') {
		echo '<p><b>' . htmlspecialchars($Message) . "</b></p>\n";
	}

	if ($Row === null) {
		if ($Message === '') {
			echo '<p>' . _('This link is not valid any more, so there is nothing to do.  No mail is sent for it.') . "</p>\n";
		}
	} else {
		$Committer = $Row['committer'];
		$Form = '<form action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" method="POST">' .
		        '<input type="hidden" name="token" value="' . htmlspecialchars($Token) . '">';

		if ($Row['status'] === 'P' && $Row['fresh'] === 't') {
			echo '<p>' . htmlspecialchars(sprintf(_('A FreshPorts user asked to mail %s@FreeBSD.org when FreshPorts has trouble processing a port change committed by %s.  Nothing is sent unless you confirm.'), $Committer, $Committer)) . "</p>\n";
			echo $Form .
			     '<button type="submit" name="action" value="confirm">' . htmlspecialchars(_('Confirm')) . '</button> ' .
			     '<button type="submit" name="action" value="refuse">' . htmlspecialchars(_('Refuse')) . '</button>' .
			     "</form>\n";
		} elseif ($Row['status'] === 'P') {
			echo '<p>' . htmlspecialchars(sprintf(_('This confirmation link has expired.  Nothing is sent to %s@FreeBSD.org.'), $Committer)) . "</p>\n";
			echo $Form .
			     '<button type="submit" name="action" value="refuse">' . htmlspecialchars(_('Remove the request')) . '</button>' .
			     "</form>\n";
		} else {
			echo '<p>' . htmlspecialchars(sprintf(_('FreshPorts mails %s@FreeBSD.org when it has trouble processing a port change committed by %s.'), $Committer, $Committer)) . "</p>\n";
			echo $Form .
			     '<button type="submit" name="action" value="stop">' . htmlspecialchars(_('Stop these messages')) . '</button>' .
			     "</form>\n";
		}
	}

	echo '<p>' . sprintf(_('See the <a href="%s">committer opt-in</a> page for what this service does.'), '/committer-opt-in.php') . "</p>\n";
?>
</td></tr>
</table>
</td>

  <td class="sidebar">
	<?php
	echo freshports_SideBar();
	?>
  </td>

</table>

<?php
echo freshports_ShowFooter();
?>

</BODY>
</HTML>
