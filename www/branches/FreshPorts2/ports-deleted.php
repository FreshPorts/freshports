<?
	# $Id: ports-deleted.php,v 1.1.2.3 2002-02-16 23:52:51 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");
	require("./include/getvalues.php");

	freshports_Start("Ports removed",
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

?>

<table width="<? echo $TableWidth ?>" border="0" ALIGN="center">
<tr><td COLSPAN="2" valign="top" width="100%">
<table width="100%" border="0">
<tr>
	<? freshports_PageBannerText("recently removed ports"); ?>
  </tr>
<tr><td colspan="2">
This page shows the last <? echo $MaxNumberOfPorts ?> ports to be removed the ports tree.
</td></tr>
<tr><td>
Sorry, but we've disabled this page.  Sorry about that.  With luck, it will be back in FreshPorts2.
</td></tr>
</table>
</td>
  <td valign="top" width="*">
   <? include("./include/side-bars.php") ?>
 </td>
</tr>
</TABLE>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include("./include/footer.php") ?>
</TD></TR>
</TABLE>

</body>
</html>
