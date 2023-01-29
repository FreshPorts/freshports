<?php
	#
	# $Id: fraud.php,v 1.3 2007-10-25 11:57:23 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

?>
<table class="fullwidth borderless" ALIGN="center">
<tr><td VALIGN=TOP>
<table class="fullwidth borderless" ALIGN="left">
<tr>
	<?php echo freshports_PageBannerText("Fraud - This is not FreshPorts.org!"); ?>
</tr>

<tr><td>
<P>
Someone is trying to scam you.  Please note the URL you are at and compare it to where
you want to be.  I suspect you may be at freshports.net, not FreshPorts.org.  

<p>
Please read <a href="/fraud/">this</a> for more information about how freshports.net
tries to scam money from unsuspecting people.
</td></tr>
</table>

</BODY>
</HTML>
