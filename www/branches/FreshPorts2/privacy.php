<?
	# $Id: privacy.php,v 1.1.2.4 2002-04-19 19:50:41 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");
	require("./include/getvalues.php");

	freshports_Start("Privacy Policy",
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

?>
<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD VALIGN="top" WIDTH="100%">
<TABLE WIDTH="100%" BORDER="0">

<TR>
	<? freshports_PageBannerText("Privacy statement"); ?>
</TR>
<TR><TD>
<P>All the information we
    gather is for our own use.  We do not release it to anyone else.</P>
    <P>For example, when you subscribe to our mailing list, we
    keep that to ourselves and nobody else will know.  We don't sell our mailing lists.
      Or any other private information for that matter.</P>
    <P>Most websites gather statistics regarding the number of times a page was accessed.
      We do this.  This means your IP address, or the IP address of your proxy will
    be recorded in our access logs.  We do not release this information to anyone.  
    It wouldn't be much use to anyone anyway.</P>
    <P>The New Zealand Privacy Commissioner has some interesting reading at 
		<A href="http://www.knowledge-basket.co.nz/privacy/top.html">http://www.knowledge-basket.co.nz/privacy/top.html</A>.
</TD></TR>
</TABLE>
</TD>
<TD>
    <? include("./include/side-bars.php") ?>
</TD>
</TR>
</TABLE>
<? include("./include/footer.php") ?>
</body>
</html>
