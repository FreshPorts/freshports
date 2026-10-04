<?php
	#
	# $Id: confirmation.php,v 1.2 2006-12-17 12:06:09 dan Exp $
	#
	# Copyright (c) 1998-2026 Dan Langille
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	$Title = _('Account confirmation');
	freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');
	$Debug = 0;

	$ResultConfirm = 999;

	$token = $_REQUEST['token'] ?? null;
	if (IsSet($token)) {
		$token = pg_escape_string($db, $token);
		if ($Debug) echo "I'm confirming with token $token\n<br>";
		$sql = "select ConfirmUserAccount($1)";
		$result = pg_query_params($db, $sql, array($token));
		if ($result) {
			$row = pg_fetch_array($result,0);
			$ResultConfirm = $row[0];
		} else {
			echo pg_last_error($db) . $sql;
		}
	}
?>


<?php echo freshports_MainTable(); ?>
<tr><td class="content">
<table class="fullwidth">
<tr>
	<?php echo freshports_PageBannerText(_("Account confirmation")); ?>
</tr>

<tr><td>
<P>
<?php
	if ($Debug) echo $ResultConfirm;
	switch ($ResultConfirm) {
		case 0:
			echo _("I don't know anything about that token.");
			break;

		case 1:
			echo _('Your account has been enabled.  Please proceed to the <a href="login.php">login page</a>');
			break;

		case 2:
			echo _("Well.  This just isn't supposed to happen.  For some strange and very rare reason, there is more than one person with that token.") . '<br><br>' .
			     sprintf(_('Please contact %s for help.'), 'webmaster&#64;freshports.org');
			break;

		case -1:
			echo _("An error has occurred.  Sorry.");
			break;

		case 999:
			echo _("Hi there.  What are you doing here?");
			break;

		default:
	}

?>
</P>
</td></tr>

</table>
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
