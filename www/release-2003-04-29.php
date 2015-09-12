<?php
	#
	# $Id: release-2003-04-29.php,v 1.3 2012-09-01 16:33:19 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_ConditionalGet(freshports_LastModified());

	$Title = "New Release - 29 April 2003";

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
<P>
This is the biggest release of FreshPorts since 
<a href="/fp2-announcement.php">FreshPorts 2</a> was released.
Over the past 14 months, I've been working on various things,
the biggest of which must be the ability to have multiple
watch lists.  I have no doubt that once people start using
more than one watch list, they'll come up with new ideas as to 
how to use FreshPorts... which will lead to a new release.
</P>

<p>
My thanks to the people who contributed code, ideas, and time
to this new version.
</TD></TR>

<TR>
<td align="left" valign="top" width="100%"></td>
</tr>

	<?php 
	echo freshports_BannerSpace();
	?>

<TR>
	<?
	echo freshports_PageBannerText("Multiple Watch Lists"); 
	?>
</TR>

<TR><TD>

<P>
When FreshPorts started, a watch list was a single entity.  That was to 
keep things simple.  The underlying data structures were designed with
multiple watch lists in mind.  However, the user interface was the biggest
issue.  Now we have an interface which will allow you to have more than one
watch list.

<p>
For the time being, you are restricted to five watch lists per user.  Let's see
how that goes and then we'll look at raising that limit.  If you have a pressing
need, I'm willing to consider changes on a case by case basis.  Just contact
me.

</TD></TR>

	<?php 
	echo freshports_BannerSpace();
	?>

<TR>
	<?
	echo freshports_PageBannerText("Virtual categories");
	?>
</TR>

<TR><TD>

<P>
FreshPorts now caters for virtual categories.  What's a virtual category?
A virtual category is a category which does not correspond to a physical
directory within the ports tree.

<p>
Ports are divided into categories.  A port resides within a 
category which corresponds to a physical directory within the ports tree.
For example, <a href="/www/">www</a> is a real category.  Some ports
appear in more than one category.  The first category is always the
primary category and is the directory within which the port files will
be found.
</P>

<P>
Categories which
do not correspond to a physical directory are deemed to be virtual categories.
As new virtual categories are added, trusted FreshPorts users can set the
description for the new category.
</P>

</TD></TR>

<?php echo freshports_BannerSpace(); ?>
<TR>
<?php echo freshports_PageBannerText("Newsfeed changes"); ?>
</TR>

<TR><TD>
<P>
The newsfeed now contains the version/revision information for the port.
For more information on news feeds, please read the <a href="/faq.php">FAQ</a>.
</P>
</TD></TR>

<?php echo freshports_BannerSpace(); ?>
<TR>
<?php echo freshports_PageBannerText("Security Notifications"); ?>
</TR>

<TR><TD>
<P>
FreshPorts has a new <a href="/report-subscriptions.php">subscription</a>
entry.  If you subscribe to the new Security Notification report, you will
receive a separate notification of any commits which are deemed to be security
related.

<p>
Trusted FreshPorts users can designate a commit as being security related.
This will ensure you are notified of that commit in the Security Notification
report.

<p>
NOTE: Do not rely up on this service for your security purposes.  You are urged
to subscribe to the announcements list for whatever software you use.
</P>
</TD></TR>

<?php echo freshports_BannerSpace(); ?>
<TR>
<?php echo freshports_PageBannerText("Master websites"); ?>
</TR>

<TR><TD>
<P>
You can now view the master websites for the port.  Each
ports has a list of websites from which their source can be downloaded.
This information has always been recorded within FreshPorts, but 
it's never been available until now.
</P>
</TD></TR>

<?php echo freshports_BannerSpace(); ?>
<TR>
<?php echo freshports_PageBannerText("Category paging"); ?>
</TR>

<TR><TD>
<P>
Some categories have a very large number of ports.  This can make loading
the page very slow, especially for those on dial up.  Therefore, we've introduced
paging to the <a href="/categories.php">categories</a> page.

<p>
You can set the paging length via the 
<a href="/customize.php">customization</> page.
</P>
</TD></TR>

<?php echo freshports_BannerSpace(); ?>
<TR>
<?php echo freshports_PageBannerText("Faster pages"); ?>
</TR>

<TR><TD>
<P>
Much work has gone into finding faster ways to extract data
from the FreshPorts database.  Thanks to the amazing capabilities
of <a href="http://www.postgresql.org/">PostgreSQL</a>, I've been able
to create very fast queries through the use of nested queries, outer
joins, and static functions.

<p>
These changes to the web pages have enabled the removal of a housekeeping
process which used to refresh a hidden caching table.  Most webpages used
this cache table.  Instead, they now access the main tables directly.  This
means the website is faster overall and the background process is no longer
needed.
</P>
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
