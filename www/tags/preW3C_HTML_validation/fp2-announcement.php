<?
	# $Id: fp2-announcement.php,v 1.1.2.1 2002-02-22 03:37:51 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");
	require("./include/getvalues.php");

	freshports_Start("Announcement",
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

?>
<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<tr><td valign="top" width="100%">
<table width="100%" border="0">

<tr>
	<? freshports_PageBannerText("Announcement"); ?>
</tr>
</tr><td>

FreshPorts 2 has been a long time in the making.  I think I've been working
on it for almost two years (not full time of course...).  I think you'll
find the changes are quite impressive.  Much of the work you cannot see
direct.  The following are the changes you can see:


<TABLE WIDTH="100%" CELLPADDING="4" CELLSPACING="4">
<TR>
<TD NOWRAP VALIGN="top" ALIGN="right"><B>Face lift</B></TD>
	<TD>
	I think you can see that the fonts
	have changed, and some page layouts are different.  It's
	not so much a huge change as it is evolution.
	</TD></TR>

<TD NOWRAP VALIGN="top" ALIGN="right"><B>full commit messages</B></TD>
    <TD>The switch to XML input allows us to capture more data</TD></TR>

<TD NOWRAP VALIGN="top" ALIGN="right"><B>directory structure</B></TD>
    <TD>You know the path to your favourite ports via /usr/ports.  Use the
        same path in FreshPorts (e.g <A HREF="/sysutils/portupgrade/">sysutils/portupgrade</A>).
    </TD></TR>

<TD NOWRAP VALIGN="top" ALIGN="right"><B>one-click add/remove</B></TD>
    <TD>See a port you like? You can add it to your watch list with
        a single click.
    </TD></TR>
<TD NOWRAP VALIGN="top" ALIGN="right"><B>link to commit details</B></TD>
    <TD>Want to know what files were changed in this commit?  It's now
    just one click away.  One more click will take to you the FreeBSD
    CVS repository.</TD></TR>

<TD NOWRAP VALIGN="top" ALIGN="right"><B>Forums are back!</B></TD>
    <TD>The <A HREF="/phorum/">support forums</A> are back, better than ever</TD></TR>

<TD NOWRAP VALIGN="top" ALIGN="right"><B>pkg_info == watch list</B></TD>
    <TD>pkg_info displays list of the ports installed
        on your system.  Now you can use <A HREF="/pkg_upload.php">our scripts</A>
        to use this data to upgrade your watch list!
    </TD></TR>

<TD NOWRAP VALIGN="top" ALIGN="right" ALIGN="right"><B>Search</B></TD>
    <TD>There is now a search on the front page.. in fact, it should be on every page!</TD></TR>
</DL>
</TABLE>

<H2>Technical changes</H2>
The following items deal with the technical changes which have occurred.

<TABLE WIDTH="100%" CELLPADDING="4" CELLSPACING="4">
<TR>
<TD NOWRAP VALIGN="top" ALIGN="right"><B>database</B></TD>
	<TD>
	FreshPorts now uses <A HREF="http://www.postgresql.org/">PostgreSQL</A>.  Why?  Because
	of stored procedures and transactions (yes, we know mySQL now has transactions, but it
	didn't when we started this...).
	</TD></TR>

<TD NOWRAP VALIGN="top" ALIGN="right"><B>XML</B></TD>
	<TD>
	Input for FreshPorts is first converted to XML, then processed.  This will be of great
	benefit to <A HREF="http://www.FreshSource.org/">FreshSource</A> which is the next big
	project.
	</TD></TR>

</TABLE>

</td></tr>
</table>
</td>
  <td valign="top" width="*">
    <? include("./include/side-bars.php") ?>
 </td>
</tr>
</table>
<? include("./include/footer.php") ?>
</body>
</html>
