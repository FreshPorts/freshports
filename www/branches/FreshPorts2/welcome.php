<?
	# $Id: welcome.php,v 1.1.2.6 2002-04-20 03:23:36 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");
	require("./include/getvalues.php");

	freshports_Start("New User",
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

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
	You should soon received an email at the mail address you supplied.
	It will contain instructions to enable your account.
	</P>

	<P>
	Click <a href="<? echo $origin?>">here</a> to return to your previous page.
	</P>

</TD></TR>
</TABLE>
</TD>
  <TD VALIGN="top" WIDTH="*">
    <? include("./include/side-bars.php") ?>
 </TD>
</TR>
</TABLE>
<? include("./include/footer.php") ?>
</BODY>
</HTML>
