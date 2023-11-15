<?php
	# $Id: fp2-announcement.php,v 1.2 2006-12-17 12:06:10 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_ConditionalGet(freshports_LastModified());

	$Title = "New Release - 22 February 2002";

	freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(NOBORDER); ?>

<tr>
	<?php echo freshports_PageBannerText($Title); ?>
</tr>
<tr><td>

<p>
FreshPorts 2 has been a long time in the making.  I think I've been working
on it for almost two years (not full time of course...).  I think you'll
find the changes are quite impressive.
</P>

<P>Much of the work you cannot see directly.  Please read the <a href="/faq.php">FAQ</a>
for a full list of features.  The following is a short list of some of the changes you can see:
</P>


<table class="announce-notes fullwidth">
<tr>
<TH>Face lift</TH>
	<td>
	I think you can see that the fonts
	have changed, and some page layouts are different.  It's
	not so much a huge change as it is evolution.
	</td></tr>

<tr>
<TH>full commit messages</TH>
    <td>The switch to XML input allows us to capture more data.  And provide
	you with a link to the original commit message.  <?php echo freshports_Mail_Icon(); ?>
	</td></tr>

<tr>
<TH>directory structure</TH>
    <td>You know the path to your favourite ports via /usr/ports.  Use the
        same path in FreshPorts (e.g <a href="/sysutils/portupgrade/">sysutils/portupgrade</a>).
    </td></tr>

<tr>
<TH>one-click add/remove</TH>
    <td>See a port you like? You can add it to your watch list with
        a single click. <?php echo freshports_Watch_Icon_Add(); ?>
    </td></tr>

<tr>
<TH>link to commit details</TH>
    <td>Want to know what files were changed in this commit?  It's now
    just one click away.  One more click will take to you the FreeBSD
    CVS repository. <?php echo freshports_Files_Icon(); ?>
	</td></tr>

<tr>
<TH>pkg_info == watch list</TH>
    <td>pkg_info displays list of the ports installed
        on your system.  Now you can use <a href="/pkg_upload.php">our scripts</a>
        to use this data to upgrade your watch list!
    </td></tr>

<tr>
<TH>Search</TH>
    <td>There is now a search on the front page.. in fact, it should be on every page!</td></tr>

<tr>
<TH>Graphs are back</TH>
    <td>We've improved the <a href="/graphs.php">graphs</a>.  They are now data driven.  All we need to do is
		add the SQL to the database, and your query is there.  Just ask us for what you want.!</td></tr>
</table>

<H2>Technical changes</H2>
The following items deal with the technical changes which have occurred.

<table class="announce-notes fullwidth">
<tr>
<TH>database</TH>
	<td>
	FreshPorts now uses <a href="https://www.postgresql.org/">PostgreSQL</a>.  Why?  Because
	of stored procedures and transactions (yes, we know mySQL now has transactions, but it
	didn't when we started this...).
	</td></tr>

<tr>
<TH>XML</TH>
	<td>
	Input for FreshPorts is first converted to XML, then processed.  This will be of great
	benefit to <a href="https://www.FreshSource.org/">FreshSource</a> which is the next big
	project.
	</td></tr>

</table>

</td></tr>
</table>
</td>
  <td class="sidebar">

	<?php
	echo freshports_SideBar();
	?>

  </td>

</tr>
</table>

<?php
echo freshports_ShowFooter();
?>

</BODY>
</HTML>
