<?
	# $Id: welcome.php,v 1.1.2.3 2002-02-23 21:32:42 dan Exp $
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
<table width="100%" border="0">
<tr><td valign="top">
<table width="100%" border="0" CELLSPACING="0" CELLPADDING="5"
            bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">
  <tr>
	<? freshports_PageBannerText("Account created"); ?>
  </tr>
	<tr>
	<td>
	<P>
	Your account '<? echo $UserName ?>'has been created.
	</P>

	<P>
	You should soon received an email at the mail address you supplied.
	It will contain instructions to enable your account.
	</P>

	<P>
	Click <a href="<? echo $origin?>">here</a> to return to your previous page.
	</P>

</td></tr>
</table>
</td>
  <td valign="top" width="*">
    <? include("./include/side-bars.php") ?>
 </td>
</tr>
</table>
<? include("./include/footer.php") ?>
</body>
</html>
