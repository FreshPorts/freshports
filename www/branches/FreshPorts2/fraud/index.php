<?php
	#
	# $Id: index.php,v 1.1.2.3 2003-12-05 00:09:34 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	freshports_Start("Fraud",
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

?>
<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><td VALIGN=TOP>
<TABLE WIDTH="100%" ALIGN="left" border="0">
<TR>
	<? echo freshports_PageBannerText("Fraud"); ?>
</TR>

<TR><TD>
<P>
On 4 December 2003 at about 16:30 EST, I received email which told me about
freshports.net.  This websites was attempting to pass itself off as FreshPorts and 
asking for donations.  freshports.net is not associated with FreshPorts in any
way.

<p>
To their credit, the contents was promptly removed upon request.  I have notified 
Paypal to inform them of the fraud.  If any of you have donated money to freshports.net,
please inform Paypal and get a refund.

<p>
Please let any and all FreshPorts users know about this incident.

<p>
Here a few supporting documents:

<ul>
<li><a href="/fraud/freshports.net.jpg">Their fake page</a>
<li><a href="/fraud/freshports.net.code.php">Source code for their page</a>
<li><a href="/fraud/freshports.net.paypal.jpg">Their paypal page</a>
<li><a href="/fraud/freshports.net.mozilla.cache.php">Mozilla cache</a>
<li><a href="/fraud/whois.freshports.net.php">whois freshports.net</a>
<li><a href="/fraud/first-email.php">My request to them</a>
<li><a href="/fraud/their-reply.php">Their reply<a/>
</ul>
</td></tr>

</TABLE>


<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? freshports_ShowFooter(); ?>
</TD></TR>
</TABLE>

</BODY>
</HTML>
