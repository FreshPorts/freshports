<?
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

<TR>
	<? echo freshports_PageBannerText($Title); ?>
</TR>
<TR><TD>

<p>
FreshPorts 2 has been a long time in the making.  I think I've been working
on it for almost two years (not full time of course...).  I think you'll
find the changes are quite impressive.
</P>

<P>Much of the work you cannot see directly.  Please read the <A HREF="/faq.php">FAQ</A>
for a full list of features.  The following is a short list of some of the changes you can see:
</P>


<TABLE class="announce-notes fullwidth">
<TR>
<TH>Face lift</TH>
	<TD>
	I think you can see that the fonts
	have changed, and some page layouts are different.  It's
	not so much a huge change as it is evolution.
	</TD></TR>

<TR>
<TH>full commit messages</TH>
    <TD>The switch to XML input allows us to capture more data.  And provide
	you with a link to the original commit message.  <? echo freshports_Mail_Icon(); ?>
	</TD></TR>

<TR>
<TH>directory structure</TH>
    <TD>You know the path to your favourite ports via /usr/ports.  Use the
        same path in FreshPorts (e.g <A HREF="/sysutils/portupgrade/">sysutils/portupgrade</A>).
    </TD></TR>

<TR>
<TH>one-click add/remove</TH>
    <TD>See a port you like? You can add it to your watch list with
        a single click. <? echo freshports_Watch_Icon_Add(); ?>
    </TD></TR>

<TR>
<TH>link to commit details</TH>
    <TD>Want to know what files were changed in this commit?  It's now
    just one click away.  One more click will take to you the FreeBSD
    CVS repository. <? echo freshports_Files_Icon(); ?>
	</TD></TR>

<TR>
<TH>pkg_info == watch list</TH>
    <TD>pkg_info displays list of the ports installed
        on your system.  Now you can use <A HREF="/pkg_upload.php">our scripts</A>
        to use this data to upgrade your watch list!
    </TD></TR>

<TR>
<TH>Search</TH>
    <TD>There is now a search on the front page.. in fact, it should be on every page!</TD></TR>

<TR>
<TH>Graphs are back</TH>
    <TD>We've improved the <A HREF="/graphs.php">graphs</A>.  They are now data driven.  All we need to do is
		add the SQL to the database, and your query is there.  Just ask us for what you want.!</TD></TR>
</TABLE>

<H2>Technical changes</H2>
The following items deal with the technical changes which have occurred.

<TABLE class="announce-notes fullwidth">
<TR>
<TH>database</TH>
	<TD>
	FreshPorts now uses <A HREF="https://www.postgresql.org/">PostgreSQL</A>.  Why?  Because
	of stored procedures and transactions (yes, we know mySQL now has transactions, but it
	didn't when we started this...).
	</TD></TR>

<TR>
<TH>XML</TH>
	<TD>
	Input for FreshPorts is first converted to XML, then processed.  This will be of great
	benefit to <A HREF="https://www.FreshSource.org/">FreshSource</A> which is the next big
	project.
	</TD></TR>

</TABLE>

</TD></TR>
</TABLE>
</td>
  <td class="sidebar">

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
