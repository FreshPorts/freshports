<?php
	#
	# $Id: committer-opt-in.php,v 1.5 2009-01-08 19:47:08 dan Exp $
	#
	# Copyright (c) 1998-2026 Dan Langille
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

	# Every form on this page carries the user's token, and a POST without it
	# changes nothing: see include/csrf.php.  Before, a form on another site
	# could post here with the user's login cookie.
	if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
		freshports_CsrfRequire();
	}

	# The account's email address must end in @FreeBSD.org.  The anchor
	# stops look-alike domains such as x@FreeBSD.org.example.com.  This does
	# not prove the user is a committer (the account's address is not
	# confirmed when it changes, and the login comes from the form), so a new
	# or changed opt-in only becomes active once the link mailed to
	# <login>@FreeBSD.org is used: see include/committer-notify.php.
	const FREEBSD_EMAIL_REGEX = '/@FreeBSD\.org$/i';

	$Title = _('Committer opt-in');
	freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');

	# what happened on a subscribe or update, shown above the status
	$OptInMessage = '';

	if (IsSet($User->email) && !preg_match(FREEBSD_EMAIL_REGEX, $User->email)) {
		# nothing yet
	} else {
		if (((IsSet($_POST["subscribe"]) && $_POST["subscribe"]) || (IsSet($_POST["update"]) && $_POST["update"])) && !empty($visitor)) {
			$committer = strtolower(trim((string) ($_POST["email"] ?? '')));
			if (strpos($committer, '@') !== false) {
				die(_('please enter just your login, not your email address'));
			}

			if (freshports_CommitterLoginValid($committer)) {
				$OptInMessage = freshports_CommitterNotifyRequest($db, $User, $committer);
			} else {
				$OptInMessage = _('That is not a FreeBSD login: use lower case letters, digits, - and _ only.');
			}
		}

		if (IsSet($_POST["unsubscribe"]) && $_POST["unsubscribe"] && !empty($visitor)) {
			$sql = 'delete from committer_notify where user_id = $1';

			if ($Debug) echo "sql=$sql<br>\n";

			$result = pg_query_params($db, $sql, array($User->id))  or die(freshports_QueryFailed($db));

			if (!$result) {
				die(freshports_QueryFailed($db));
				}
			}
	}

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(NOBORDER); ?>
<tr>
	<?php echo freshports_PageBannerText(_("Committer opt-in")); ?>
</tr>

<tr><td class="textcontent">
<P>
<?php
	if (!IsSet($User->email) || !preg_match(FREEBSD_EMAIL_REGEX, $User->email)) {
?>
<p><b><big><?php echo _('This page only works if you are logged in and using a @FreeBSD.org email address.'); ?></big></b></p>
<?php
	}
?>

<?php echo _('Mistakes happen.  And when they do, they are best corrected quickly.  To that end, FreshPorts provides an opt-in service for all FreeBSD committers.  If you subscribe to this service, FreshPorts will notify you of any problems it encounters when processing a port change which you committed.  In the past, such problems are related to syntax errors in the Makefile.'); ?>
</P>

<P>
<?php echo _('One committer referred to this service as an automated nagging mentor...'); ?>
</P>

<P>
<?php echo htmlspecialchars(_('When you subscribe, or change your login, FreshPorts mails a link to <login>@FreeBSD.org.  The service starts when that link is used.  Every report ends with a link which stops them.')); ?>
</P>
</td></tr>

<?php

if (IsSet($User->email)) {
?>

<tr>
	<?php
	echo freshports_PageBannerText(_("Your opt-in status"));
	?>
</tr>

<tr><td>
<P>
<?php
if ($OptInMessage != '') {
	echo '<p><b>' . htmlspecialchars($OptInMessage) . "</b></p>\n";
}

if (!empty($visitor)) {
	$sql = 'select committer, status
			  from committer_notify
			 where user_id = $1';

	if ($Debug) {
		echo "sql=$sql<br>\n";
	}

	$result = pg_query_params($db, $sql, array($User->id))  or die(freshports_QueryFailed($db));

	if ($result) {
		if ($Debug) echo "we found a result there...\n<br>";
		$numrows = pg_num_rows($result);
		if ($numrows) {
			$myrow = pg_fetch_array ($result, 0);
			if ($myrow) {
				$committer = $myrow["committer"];
				if ($myrow["status"] == 'A') {
					echo htmlspecialchars(sprintf(_('You are: subscribed as %s'), $committer));
				} else {
					echo htmlspecialchars(sprintf(_('You are: waiting for confirmation, sent to %s@FreeBSD.org'), $committer));
				}
			}
		} else {
			echo _('You are: not subscribed');
			$committer = preg_replace('|^(.*)@FreeBSD\.org$|i', '\\1', $User->email);
		}
	}
}
?>

<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST" NAME=f>
<?php echo freshports_CsrfField(); ?>
               <?php echo _('your freefall login:'); ?>
               <INPUT SIZE="35" NAME="email" VALUE="<?php echo htmlspecialchars($committer ?? '') ?>"><br><br>
<?php
			if (IsSet($numrows) && $numrows) {
?>
				<INPUT TYPE="submit" VALUE="<?php echo htmlspecialchars(_('update')); ?>"      NAME="update">
				<INPUT TYPE="submit" VALUE="<?php echo htmlspecialchars(_('unsubscribe')); ?>" NAME="unsubscribe">
<?php
			} else {
?>
				<INPUT TYPE="submit"  VALUE="<?php echo htmlspecialchars(_('subscribe')); ?>" NAME="subscribe">
<?php
			}
?>

</FORM>

<p>
<BIG><?php echo _('Please do not include @FreeBSD.org in your login name.'); ?></BIG>
</p>

</td></tr>

<?php
} // $User->email
?>

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
