<?
	# $Id: welcome.php,v 1.1.2.14 2002-12-11 04:44:42 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	freshports_Start('New User',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');

	$origin = $_GET["origin"];

?>
<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD VALIGN="top">
<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
  <TR>
	<? freshports_PageBannerText("Account created"); ?>
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
    <? require_once($_SERVER['DOCUMENT_ROOT'] . '/include/side-bars.php') ?>
 </TD>
</TR>
</TABLE>
<? require_once($_SERVER['DOCUMENT_ROOT'] . '/include/footer.php') ?>
</BODY>
</HTML>
