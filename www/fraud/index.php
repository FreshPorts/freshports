<?php
	#
	# $Id: index.php,v 1.3 2007-10-25 11:57:23 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

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
freshports.net.  This website was attempting to pass itself off as FreshPorts and 
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


<h3>Screen shots and code</h3>

<ul>
<li><a href="/fraud/freshports.net.jpg">Their fake page</a>
<li><a href="/fraud/freshports.net.code.php">Source code for their page</a>
<li><a href="/fraud/freshports.net.paypal.jpg">Their paypal page</a>
<li><a href="/fraud/freshports.net.openbsdinstead.jpg">OpenBSD instead</a>
<li><a href="/fraud/freshports.net.search.msn.com.jpg">What MSN says about freshports.net</a> (see for <a href="http://search.msn.com/results.aspx?q=freshports.net&FORM=SMCRT">yourself</a>)
</ul>

<h3>Misc</h3>
<ul>
<li><a href="/fraud/freshports.net.mozilla.cache.php">Mozilla cache</a>
<li><a href="/fraud/whois.freshports.net.php">whois freshports.net</a>
<li><a href="/fraud/freshports.net.log.php">web server logs (IP addresses have been obscured for privacy)</a>
</ul>

<h3>Correspondence</h3>
<ul>
<li><a href="/fraud/first-email.php">My request to them</a>
<li><a href="/fraud/their-reply.php">Their reply<a/>
<li><a href="/fraud/second-email.php">Remaining email of 5 December 2003<a/>
</ul>

See also <a href="http://www.freebsddiary.org/freshports-fraud.php">this FreeBSD Diary article</a>.

<h2>DNS stops working - 8 Dec 2003</h2>

<p>On Dec 8, I noticed the DNS had stopped working for the fraudster's domain.
I saved the <a href="/fraud/whois-dec-9.php">whois output</a>.  As you can see,
the status is REGISTRAR-LOCK.

<h2>An apology arrives - 12 Dec 2003</h2>

<p>
I received an email from Gabriel Medina today.  He apologized.

<p>
I also received email from the owner of the colo box on which 
Gabriel Medina has an account and which was the mail server for freshports.net.
The domain is forever banned from that server.  Gabriel was hosting
the website from home.  My first complaint was CC'd to his ISP (bostream.com) but I never
received a reply from them.

<p>
I have asked Gabriel Medina to transfer freshports.net to me.  For
what it's worth, at one time I owned it.  He has already relinquished the 
domain back to the registrar.

</td></tr>

</TABLE>


<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? echo freshports_ShowFooter(); ?>
</TD></TR>
</TABLE>

</BODY>
</HTML>
