<?
	# $Id: thanks.php,v 1.1.2.2 2002-01-05 23:01:19 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");
	require("./include/getvalues.php");

	freshports_Start("what do you want done next?",
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

?>
<table width="100%">
<tr><td valign="top" width="100%">
<h2>Thanks for voting.</h2>

<p>I'll let you know what's next.  Cheers</p>
</td>
<td valign="top">
    <? include("./include/side-bars.php") ?>
 </td>
</tr>
</table>
<? include("./include/footer.php") ?>
</body>
</html>
