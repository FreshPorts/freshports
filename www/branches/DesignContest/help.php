<?
	# $Id: help.php,v 1.2 2006-12-17 12:06:11 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_ConditionalGet(freshports_LastModified());

	freshports_Start('pkg_info >> watch list',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(NOBORDER); ?>
<TR>
	<? echo freshports_PageBannerText("pkg_info and your watch list "); ?>
</TR>

<TR><TD>
<P>
One of the most powerful FreshPorts features is the ability to tap
the existing information on your FreeBSD system and use it to 
modify your watch list.  This is achieved using the 
<A HREF="/pkg_upload.php">pkg_info upload facility</A>.  This is a two 
stage process:
<OL>
<LI>upload the pkg_info output from your system into your watch list
	staging area</LI>
<LI>choose the elements from your staging area and save them to your
	watch list</LI>
</OL>

The rest of this page should answer everything.

</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
	<? echo freshports_PageBannerText("How does pkg_upload work?"); ?>
</TR>
<TR><TD>
<P>
Mostly with smoke and mirrors, a small group of lowly trained but
highly motivated cats, and a pair of keas.  Actually, those
team members are behind the scenes and we do not like to let
them share the limelight.
In brief, FreshPorts uses the basic information
available on your system.  It's pretty basic.  And easy.
</P>

</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
	<? echo freshports_PageBannerText("How does pkg_upload really work?"); ?>
</TR>

<TR><TD>
<P>
For each installed port on your FreeBSD system, there is an entry in the
ports database.  This database is at <CODE CLASS="code">/var/db/pkg/</CODE>.
The <CODE CLASS="code">pkg_info</CODE> command interrogates this database
and can produce a list of the installed ports.  The basic command outputs
the name of the installed package and a brief description. Using the 
<CODE CLASS="code">-qoa</CODE> options, FreshPorts obtains the 
&lt;category&gt;/&lt;port&gt; format of the installed packages.  This 
format is useful for three reasons:

<OL>
<LI>It mirrors the directory structures of the ports tree</LI>
<LI>It is easily parsed and understood by FreshPorts</LI>
<LI>It is the minimal information necessary to obtain the objective</LI>
</OL>

Here is a short example of the output from that command:

<BLOCKQUOTE><PRE CLASS="code">
irc/bitchx
www/apache13-modssl
devel/autoconf213
shells/bash2
net/cvsup-without-gui
sysutils/daemontools
.
[snip]
.
devel/ruby-optparse
security/sudo
misc/xtail
</PRE></BLOCKQUOTE>

<P>
The 
The output from this command is uploaded and saved into the FreshPorts 
database in your personal staging area.

</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
	<? echo freshports_PageBannerText("What is a staging area?"); ?>
</TR>
<TR><TD>
The watch list staging area is a temporary storage area
for your upload pkg_info output.  It serves as an area in which you
can compare the new and old watch lists before saving any changes.
</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
	<? echo freshports_PageBannerText("What are the different sections in my staging area?"); ?>
</TR>
<TR><TD>
There are four sections to your staging area.  These will be listed from
left to right.

<OL>
<LI>pkg_info - The first section lists the ports found in your pkg_info 
	upload.  Those which also appear in your watch list will be marked
	by a 'W'.
</LI>
<LI>Not found - It is possible for ports from your pkg_info output to
	not be found in FreshPorts.  Over time, ports are deleted, renamed,
	or moved.  This section contains the ports from your pkg_info 
	output that could not be found within FreshPorts.
</LI>
<LI>Duplicates - Sometimes you install the same port twice, and not always
	by design.  If that happens, these ports will be listed in this section.
	Unless you need both versions of these ports for a particular reason, you
	should consolidate this situation.</LI>
<LI>Watch List - This section contains the ports which are on your watch list
	which did not appear in your pkg_info upload.</LI>
</OL>

You should inspect that information carefully before saving it to your watch list.

</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
	<? echo freshports_PageBannerText("What are these check boxes in my staging area?"); ?>
</TR>
<TR><TD>
Your staging area is just a temporary situation.  You must save this information
to your watch list.  You can choose the ports you wish to watch using the
check boxes.  Only selected ports will be saved to your watch list.
</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
	<? echo freshports_PageBannerText("Why would I want to clear my staging area?"); ?>
</TR>
<TR><TD>
You cannot upload anything to your staging area unless it is empty.  If you 
don't wish to save the data to your watch list, you must first clear
your staging area before uploading again.
</TD></TR>


	
</TABLE>
</TD>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
	<?
	echo freshports_SideBar();
	?>
  </td>

</TR>
</TABLE>

<?
echo freshports_ShowFooter();
?>

</body>
</html>
