<?
	# $Id: faq.php,v 1.1.2.5 2002-03-01 03:13:27 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");
	require("./include/getvalues.php");

	freshports_Start("FAQ",
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

?>
<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<tr><td valign="top" width="100%">
<table width="100%" border="0">

<tr>
	<? freshports_PageBannerText("FAQ"); ?>
</tr>
<TR><TD>
<P>This page contains the FAQ for FreshPorts</P>
</TD></TR>
</TD></TR><TR><TD>&nbsp;</TD></TR>
<TR>

<? freshports_PageBannerText("What is this website about?"); ?>

	<TR><TD>
	This website will help you keep up with the latest releases of your
	favourite software.  When a new version of the software is available,
	FreshPorts will send you an email telling you about the change.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<? freshports_PageBannerText("How do I use this?"); ?>
	<TR><TD>
	Your primary FreshPorts tool is your watch list.  This is the
	collection of ports which you have selected for FreshPorts to
	keep track of.  You will be emailed when a change is found
	for one of your watched ports.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<? freshports_PageBannerText("How do I modify my watch list?"); ?>
	<TR><TD>
	There are three easy ways to modify your watch list:
	<OL>
	<LI>Wherever you see a port, you can click on the Add/Remove
		link as necessary.</LI>
	<LI>The 'watch list categories' link provides you with a list
		of categories.  Select the category, and then the ports within
		that category.</LI>
	<LI>Use the 'upload' link to upload your pkg_info data into your
		watch list staging area and then into your watch list.</LI>
	</OL>
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<? freshports_PageBannerText("What is a port"); ?>
	<TR><TD>
	A port is a simple easy way to install an application.
	A port is a collection of files.  These files contain the location
	of the source file, any patches which must be appplied,
	instructions for building the application, and the installation
	procedure.  Removing an installed port is also easy.  For full
	details on how to use ports, please refer to the offical port
	documents in the <A HREF="http://www.FreeBSD.org/handbook/">FreeBSD
	Handbook</A>.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<? freshports_PageBannerText("Where do ports come from?"); ?>

	<TR><TD>Ports are created by other FreeBSD volunteers, just like you
	and just like the creators of FreshPorts.  The FreshPorts team does
	not create ports; we just tell you about the latest changes.  The
	FreeBSD Ports team creates, maintains, and upgrades the ports.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<? freshports_PageBannerText("Who do I talk to about a port?"); ?>

	<TR><TD>The official mailing list is freebsd-ports&#64;freebsd.org.
		More information all FreeBSD mailing lists can be obtained
		from <A HREF="http://www.FreeBSD.org/handbook/eresources.html#ERESOURCES-MAIL">FreeBSD Mailing Lists</A>.
		You can ask for help there and in our <A HREF="/phorum/">Support
		Forum</A>.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<? freshports_PageBannerText("How do I get these ports?"); ?>
	<TR><TD>For full information on how to obtain the ports which appear on
	this webite, please see <A HREF="http://www.FreeBSD.org/ports/">FreeBSD Ports</A>.
	The easist way to get a port is via cvsup.  An abbreviated example is

	<BLOCKQUOTE>
	<CODE CLASS="code">cvsup -h cvsup.your.fav.server /usr/share/examples/cvsup/ports-supfile</CODE>
	</BLOCKQUOTE>
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<? freshports_PageBannerText("How is the website updated?"); ?>

	<TR><TD>
	The source code for the entire FreeBSD operating system and the Ports tree
	are stored in the official <A HREF="http://www.FreeBSD.org/cgi/cvsweb.cgi">FreeBSD 
	repository</A>.  Each time a change is committed to this <A HREF="http://cvshome.org/">CVS</A>
	repository, a mail message is sent out to the cvs-all mailing list.  FreshPorts
	takes these mail messages, parses them, and then loads them into a database.
	In theory, it's fairly straight forward.  In practice, there's much more to
	it than first meets the eye.  The website is updated as soon as the message
	arrives.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<? freshports_PageBannerText("What does unknown mean for a revsion number?"); ?>

	<TR><TD>It means the data has been converted from an earlier
		version of the FreshPorts database that did not record this information.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<? freshports_PageBannerText("How can I link to your site?"); ?>

	<TR><TD>Yes, thank you, you can.  No need to ask us.  Just go ahead and do it.
		We prefer the name FreshPorts (one word, mixed case). The following 
		HTML is a good place to start:

		<BLOCKQUOTE>
		<CODE CLASS="code">&lt;A HREF="http://www.freshports.org/"&gt;FreshPorts&lt;/A&gt;</CODE>
		</BLOCKQUOTE>

		<P>Here is a banner which you are free to use to link to this site:</P>

		<P ALIGN="center">
		<img src="images/freshports-banner.gif" alt="FreshPorts - the place for ports" width="468" height="60">
		</P>

		Here is the HTML for that graphic.

		<BLOCKQUOTE>
		<CODE CLASS="code">&lt;img src="images/freshports-banner.gif" alt="FreshPorts - the place for ports" width="468" height="60"&gt;</CODE>
		</BLOCKQUOTE>


		<P>Please save this graphic on your website.</P>
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<? freshports_PageBannerText("Why do I need a different login for the Forums?"); ?>
	<TR><TD>
	You only need a login for the <A HREF="/phorum/">forums</A> if
	you want to use a login.  A login will ensure that only you can
	post under the name you enter.  It is a separate login because
	we didn't write the <A HREF="http://www.phorum.org/">Phorum software</A>
	used to implement for forums.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<? freshports_PageBannerText("What does these symbols mean??"); ?>
	<TR><TD>
	There are a few symbols you will see in this website:
	<BLOCKQUOTE>
	<P><img src="/images/new.gif" width=28 height=11 alt="new!" hspace=2>
		New: This port has been recently added.  A port is marked as new for 10 days.</P>

	<P><img src="images/forbidden.gif" alt="Forbidden" width="20" height="20" hspace="2">
		Forbidden: The port is marked as forbidden.  If you view the port details,
		you will see why.  Most often, it is because of a security exploit.</P>

	<P><img src="images/broken.gif" alt="Broken" width="17" height="16" hspace="2">
		Broken: The port is marked as broken.  Perhaps it won't compile.  Maybe
		it doesn't work under FreeBSD right now.  If you view the port details,
		you will see the reason why.</P>

	<P><IMG SRC="/images/logs.gif" ALT="files touched by this commit" BORDER="0" WIDTH="17" HEIGHT="20" HSPACE="2">
		Files: If you click on this graphic, you will be taken to the list of files
		touched by the commit in question.</P>

	<P>[Refresh] - The system is in the process of refreshing that port by inspecting
		the ports tree.  You should rarely see this.</P>
	</BLOCKQUOTE>
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<? freshports_PageBannerText("Why don't my old bookmarks work?"); ?>
	<TR><TD>
	<P>
	Many things changed between FP1 and FP2. The most major change
	was in the underlying database schema.  Not only did we move
	from <A HREF="http://www.mysql.org/">mySQL</A> to
	<A HREF="http://www.postgresql.org/">PortgreSQL</A>, we made major
	changes to the tables and the way in which the ports are stored
	in the database.  As a result of these changes, many internal IDs
	and values are no longer valid.  Therefore, URLs such as
	<CODE CLASS="code">/port-description.php3?port=1234</CODE> no longer
	work.
	</P>
	<P>
	If it is any consolation, the new URLs will be much more transparent
	and permanent.  They are of the form &lt;category&gt;/&lt;port&gt;.
	</P>
	</TD></TR><TR><TD>&nbsp;</TD></TR>

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
