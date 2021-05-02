<?php
	#
	# $Id: welcome.php,v 1.2 2006-12-17 12:06:19 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	header( "HTTP/1.1 410 Gone" );

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_Start('The forums are gone',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');

	$origin = $_GET["origin"];

	echo freshports_MainTable();
?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(); ?>
  <TR>
	<?php echo freshports_PageBannerText("The forums are gone"); ?>
  </TR>
	<TR>
	<TD>
	<P>
	The forums have been removed <a href="https://github.com/FreshPorts/freshports/issues/134">via issue #134</a>.
	</P>

	<P>
	The software they used was outdated. The usage was low. Better tools exist, such as:

<ul>
<li><a href="https://github.com/FreshPorts/freshports/issues">Github issues</a></li>
<li><a href="https://www.freebsd.org/community/mailinglists.html">mailing lists</a></li>
<li><a href="https://forums.freebsd.org/">FreeBSD forums</a></li>
</ul>

	</P>

</TD></TR>
</TABLE>
</TD>

  <TD class="sidebar" VALIGN="top" WIDTH="*" ALIGN="center">
	<?
	echo freshports_SideBar();
	?>
  </td>
</TR>
</TABLE>

<?
echo freshports_ShowFooter();
?>

</BODY>
</HTML>
