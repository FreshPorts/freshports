<?php
	#
	# $Id: release-2004-10.php,v 1.3 2012-07-21 23:23:58 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	$Title = "New Release - October 2004";

	freshports_ConditionalGet(freshports_LastModified());

	freshports_Start($Title,
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(NOBORDER); ?>

<TR>
	<?php echo freshports_PageBannerText($Title); ?>
</TR>

<TR><TD>

<CENTER>
<?php
	if ($ShowAds) echo Ad_728x90();
?>
</CENTER>

<p>
This page is rather dated.  Most news is now published on the
<a href="http://news.freshports.org/">FreshPorts Blog</a>.

<p>
This is the biggest release of FreshPorts since 
<a href="/release-2003-04-29.php">April 2003</a> (nearly 18 months ago).
Some of the changes are big. Some are little.  Those that appear below
are in no particular order.

<p>
As always, my thanks to those people who provided suggestions
and ideas which lead to the changes I've made.
</TD></TR>

<TR>
<td align="left" valign="top" width="100%"></td>
</tr>

	<?php 
	echo freshports_BannerSpace();
	?>

<TR>
	<?
	echo freshports_PageBannerText('Deleted icon changed'); 
	?>
</TR>

<TR><TD>

<P>
The deleted icon has changed.  It is now <?php
echo freshports_Deleted_Icon(); ?>.  I would show you what it used to be, but,
well, it's been deleted.  It was a square with a X in it.

</TD></TR>

	<?php 
	echo freshports_BannerSpace();
	?>

<TR>
	<?
	echo freshports_PageBannerText('VuXML');
	?>
</TR>

<TR><TD>

<P>
The <a href="http://www.vuxml.org/freebsd/">VuXML</a> project documents
security issues that affect the <a href="http://www.freebsd.org/">FreeBSD</a>
operating system or applications in the
<a href="http://www.freebsd.org/ports/">FreeBSD Ports Collection</a>.
This documentation takes the form of of an XML file provided by the 
<a href="/security/vuxml/">vuxml</a> port.  FreshPorts now integrates 
the information from that file with the commit history of each affect port.

<p>
For an example, have a look at the <a href="/www/firefox/">Firefox</a>
port.  Under commit history, you will see several instances of the
icon chosen for VuXML (<?php echo freshports_VuXML_Icon(); ?>).  Click
on the icon to view details of the vulnerability.

<p>
Matthew Seaman is the man to thank for this feature. He wrote the code
that parses the vuln.xml file and allows me to record the information
in FreshPorts.  Also thank the VuXML team for their work and for helping
me to understand the inner workings of the vuln XML data.
</P>

</TD></TR>

	<?
	echo freshports_BannerSpace();
	echo '<tr>' . freshports_PageBannerText('Link by package') . '</tr>';
	?>

<TR><TD>
<P>
You can now link to FreshPorts using just the package name.  For example, you
can link to the Firefox port using this link:

<blockquote><code class="code">
<?php

$HostName = $_SERVER['HTTP_HOST'];

echo '<a href="http://';
echo $HostName;
echo '/?package=firefox">http://';
echo $HostName;
echo '/?package=firefox</a>';
?>
</code></blockquote>

<p>
FreshPorts takes that package name, figures out where in the tree it lives,
and redirects you to the correct location.  In this case, it takes to you to
<?php

$HostName = $_SERVER['HTTP_HOST'];

echo '<a href="http://';
echo $HostName;
echo '/www/firefox/">http://';
echo $HostName;
echo '/www/firefox/</a>';
?>.  Ahh, the magic of <a href="http://www.php.net">PHP</a> and
<a href="http://www.postgresql.org/">PostgreSQL</a>!

</TD></TR>

<?	echo freshports_BannerSpace(); ?>
<TR>
<?	echo freshports_PageBannerText('Revision details'); ?>
</TR>

<TR><TD>
<P>
If you click on the Files icon (<?php echo freshports_Files_Icon(); ?>) in the
Commit History for any port, you'll see a new link.  This link is represented
by the Revision Details icon (<?php echo freshports_Revision_Icon(); ?>).
</P>
</TD></TR>

<?	echo freshports_BannerSpace(); ?>
<TR>
<?	echo freshports_PageBannerText('Expanded search options'); ?>
</TR>

<TR><TD>
<P>
The <a href="/search.php">search page</a> now allows you to search by 
the following fields.
<blockquote>
<table cellpadding="5" cellspacing="0" border="0">
<tr><td><b>Field</b></td><td><b>Origin</b></td></tr>
<tr><td>Port Name</td><td><code class="code">PORTNAME</code></td></tr>
<tr><td>Package Name</td><td><code class="code">PKGNAME</code></td></tr>
<tr><td>Latest Link</td><td><code class="code">PKGNAME</code></td></tr>
<tr><td>Maintainer</td><td><code class="code">MAINTAINER</code></td></tr>
<tr><td>Short Description</td><td><code class="code">COMMENT</code></td></tr>
<tr><td>Long Description</td><td><code class="code">pkg-descr<sup>1</sup></code></td></tr>
<tr><td>Depends Build</td><td><code class="code">BUILD_DEPENDS</code></td></tr>
<tr><td>Depends Lib</td><td><code class="code">LIB_DEPENDS</code></td></tr>
<tr><td>Depends Run</td><td><code class="code">RUN_DEPENDS</code></td></tr>
<tr><td>Message ID</td><td>The message id in the original commit email</td></tr>
</table>
</blockquote>

</TD></TR>

</TABLE>
</TD>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
	<?
	echo freshports_SideBar();
	?>
  </td>

</TABLE>

<TABLE WIDTH="<?php echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<?php echo freshports_ShowFooter(); ?>
</TD></TR>
</TABLE>

</BODY>
</HTML>
