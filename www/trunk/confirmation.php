<?php
	#
	# $Id: confirmation.php,v 1.2 2006-12-17 12:06:09 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_Start('Account confirmation',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');
	$Debug = 0;

	$ResultConfirm = 999;

	$token = $_GET['token'];
	if (IsSet($token)) {
		$token = pg_escape_string($token);
		if ($Debug) echo "I'm confirming with token $token\n<BR>";
		$sql = "select ConfirmUserAccount('$token')";
		$result = pg_exec($db, $sql);
		if ($result) {
			$row = pg_fetch_array($result,0);
			$ResultConfirm = $row[0];
		} else {
			echo pg_errormessage() . $sql;
		}
	}
?>


<?php echo freshports_MainTable(); ?>
<tr><td VALIGN=TOP width="100%">
<TABLE WIDTH="100%">
<TR>
	<? echo freshports_PageBannerText("Account confirmation"); ?>
</TR>

<TR><TD>
<P>
<?
	if ($Debug) echo $ResultConfirm;
	switch ($ResultConfirm) {
		case 0:
			echo "I don't know anything about that token.";
			break;

		case 1:
			echo 'Your account has been enabled.  Please proceed to the <A HREF="login.php">login page</A>';
			break;

		case 2:
			echo "Well.  This just isn't supposed to happen.  For some strange and very rare reason,
				 there is more than one person with that token.<BR><BR>Please contact webmaster&#64;freshports.org for help.";
			break;

		case -1:
			echo "An error has occurred.  Sorry.";
			break;

		case 999:
			echo "Hi there.  What you are doing here?";
			break;

		default:
	}

?>
</P>
</TD></TR>

</table>
</td>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">

	<?
	echo freshports_SideBar();
	?>

  </td>

</tr>
</table>

<?
echo freshports_ShowFooter();
?>

</body>
</html>
