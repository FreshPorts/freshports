<?php
	#
	# $Id: welcome.php,v 1.1.2.18 2005-01-05 23:14:39 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	freshports_Start('New User',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');

	$origin = $_GET["origin"];

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(); ?>
  <TR>
	<? echo freshports_PageBannerText("Account created"); ?>
  </TR>
	<TR>
	<TD>
	<P>
	Your account has been created.
	</P>

	<P>
	You should soon receive an email at the mail address you supplied.
	It will contain instructions to enable your account.

	<P>
	If you do not receive that email, please attempt to login and you
	will have an opportunity to resend the notification.
	</P>

	<P>
	Click <a href="<? echo $origin?>">here</a> to return to your previous page.
	</P>

</TD></TR>
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
