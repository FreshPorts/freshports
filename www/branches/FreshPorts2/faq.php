<?
	# $Id: faq.php,v 1.1.2.27 2002-12-11 04:44:35 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	freshports_Start('FAQ',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');

	$ServerName = str_replace('freshports', 'FreshPorts', $_SERVER['SERVER_NAME']);

	GLOBAL $FreshPortsName;
	GLOBAL $FreshPortsSlogan;

?>
<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<tr><td valign="top" width="100%">
<table width="100%" border="0">

<tr>
	<? freshports_PageBannerText("FAQ"); ?>
</tr>
<TR><TD>
<P>This page contains the FAQ for FreshPorts. Hopefully the questions
are arranged from general to specific.  The more you know, the further
down you must read to find something you didn't already know.</P>
</TD></TR>
<TR><TD>&nbsp;</TD></TR>

<TR>

<? freshports_PageBannerText("What is this website about?"); ?>

	<TR><TD>
	This website will help you keep up with the latest releases of your
	favorite software.  When a new version of the software is available,
	FreshPorts will send you an email telling you about the change.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<? freshports_PageBannerText("How do I use this?"); ?>
</TR>

	<TR><TD>
	Your primary FreshPorts tool is your watch list.  This is the
	collection of ports which you have selected for FreshPorts to
	keep track of.  You will be emailed when a change is found
	for one of your watched ports.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<? freshports_PageBannerText("How do I modify my watch list?"); ?>
</TR>

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

<TR>
<? freshports_PageBannerText("How do I empty my watch list?"); ?>
</TR>

	<TR><TD>
	There is no way to do this in one click.  Sorry.  But a solution is planned (I wrote this on 5 Nov 2002).
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<? freshports_PageBannerText("How do delete my account?"); ?>
</TR>

	<TR><TD>
	You can't.  But you can unsubscribe from all of the reports
	and you'll never hear from us again.
	</TD></TR><TR><TD>&nbsp;</TD></TR>



<TR>
<? freshports_PageBannerText("What is a port"); ?>
</TR>

	<TR><TD>
	A port is a simple easy way to install an application.
	A port is a collection of files.  These files contain the location
	of the source file, any patches which must be applied,
	instructions for building the application, and the installation
	procedure.  Removing an installed port is also easy.  For full
	details on how to use ports, please refer to the official port
	documents in the <A HREF="http://www.FreeBSD.org/handbook/">FreeBSD
	Handbook</A>.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<? freshports_PageBannerText("Where do ports come from?"); ?>
</TR>

	<TR><TD>Ports are created by other FreeBSD volunteers, just like you
	and just like the creators of FreshPorts.  The FreshPorts team does
	not create ports; we just tell you about the latest changes.  The
	FreeBSD Ports team creates, maintains, and upgrades the ports.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<? freshports_PageBannerText("Who do I talk to about a port?"); ?>
</TR>

	<TR><TD>The official mailing list is freebsd-ports&#64;freebsd.org.
		More information all FreeBSD mailing lists can be obtained
		from <A HREF="http://www.FreeBSD.org/handbook/eresources.html#ERESOURCES-MAIL">FreeBSD Mailing Lists</A>.
		You can ask for help there and in our <A HREF="/phorum/">Support
		Forum</A>.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<? freshports_PageBannerText("How do I get these ports?"); ?>
</TR>

	<TR><TD>For full information on how to obtain the ports which appear on
	this website, please see <A HREF="http://www.FreeBSD.org/ports/">FreeBSD Ports</A>.
	The easiest way to get a port is via cvsup.  An abbreviated example is

	<BLOCKQUOTE>
	<CODE CLASS="code">cvsup -h cvsup.your.fav.server /usr/share/examples/cvsup/ports-supfile</CODE>
	</BLOCKQUOTE>
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<? freshports_PageBannerText("How is the website updated?"); ?>
</TR>

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

<TR>
<? freshports_PageBannerText("What does unknown mean for a revision number?"); ?>
</TR>

	<TR><TD>It means the data has been converted from an earlier
		version of the FreshPorts database that did not record this information.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<? freshports_PageBannerText("How can I link to your site?"); ?>
</TR>

	<TR><TD>Yes, thank you, you can.  No need to ask us.  Just go ahead and do it.
		We prefer the name FreshPorts (one word, mixed case). The following 
		HTML is a good place to start:

		<BLOCKQUOTE>
		<CODE CLASS="code">&lt;A HREF="http://www.freshports.org/"&gt;FreshPorts&lt;/A&gt;</CODE>
		</BLOCKQUOTE>

		<P>Here is a banner which you are free to use to link to this site:</P>

		<P ALIGN="center">
		<img src="images/freshports-banner.gif" alt="<?php echo "$FreshPortsName -- $FreshPortsSlogan"; ?>" title="<?php echo "$FreshPortsName -- $FreshPortsSlogan"; ?>" width="468" height="60">
		</P>

		Here is the HTML for that graphic.

		<BLOCKQUOTE>
		<CODE CLASS="code">&lt;img src="images/freshports-banner.gif" alt="<?php echo "$FreshPortsName -- $FreshPortsSlogan"; ?>" title="<?php echo "$FreshPortsName -- $FreshPortsSlogan"; ?> width="468" height="60"&gt;</CODE>
		</BLOCKQUOTE>


		<P>Please save this graphic on your website.</P>
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<? freshports_PageBannerText("Why do I need a different login for the Forums?"); ?>
</TR>

	<TR><TD>
	You only need a login for the <A HREF="/phorum/">forums</A> if
	you want to use a login.  A login will ensure that only you can
	post under the name you enter.  It is a separate login because
	we didn't write the <A HREF="http://www.phorum.org/">Phorum software</A>
	used to implement for forums.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<? freshports_PageBannerText("What do these symbols mean?"); ?>
</TR>

	<TR><TD>
	There are a few symbols you will see in this website.
	<BLOCKQUOTE>
	<P><? echo freshports_New_Icon() ?>
		New: This port has been recently added.  A port is marked as new for 10 days.</P>

	<P><? echo freshports_Forbidden_Icon() ?>
		Forbidden: The port is marked as forbidden.  If you view the port details,
		you will see why.  Most often, it is because of a security exploit.</P>

	<P><? echo freshports_Broken_Icon() ?>
		Broken: The port is marked as broken.  Perhaps it won't compile.  Maybe
		it doesn't work under FreeBSD right now.  If you view the port details,
		you will see the reason why.</P>

	<P><? echo freshports_Files_Icon(); ?>
		Files: If you click on this graphic, you will be taken to the list of files
		touched by the commit in question.</P>

	<P><? echo freshports_Refresh_Icon(); ?> Refresh: 
		The system is in the process of refreshing that port by inspecting
		the ports tree.  You should rarely see this.</P>

	<P><? echo freshports_Deleted_Icon(); ?> Deleted:  This port has been removed from the ports tree.</P>

	<P><? echo freshports_Mail_Icon(); ?>
		Commit message: This link will take you to the original cvs-all message in the FreeBSD mailing list archives.
		Note that it can take a view minutes for the message to appear in the archives.  This link will not appear
		for commit messages before 3 March 2002 (which is the date FreshPorts started to store the message-id).</P>

	<P><? echo freshports_Commit_Icon(); ?> FreshPorts commit message: This will take you to the FreshPorts commit
		message and allow you to see all other ports which were affected by this commit.   This link will not appear
        for commit messages before 3 March 2002 (which is the date FreshPorts started to store the message-id).</P>

	<P><? echo freshports_Watch_Icon(); ?> Item is on your watch list: This port is on your watch list.  Click
		this icon to remove the port from your watch list.  This icon appears only if you are logged in.</P>

	<P><? echo freshports_Watch_Icon_Add(); ?> Add item to your watch list: This port is not on your watch list.  Click
		this icon to add the port to your watch list.  This icon appears only if you are logged in.</P>

	<P><? echo freshports_Encoding_Errors(); ?> Encoding Errors (not all of the commit message was ASCII): Some of the
		commit message may be altered because of character conversion problems.  We display only UTF-8 and remove
		the offending characters.  These errors may occur in the log message or elsewhere in the commit email.</P>

	</BLOCKQUOTE>
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<? freshports_PageBannerText("Why don't my old bookmarks work?"); ?>
</TR>

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
	If it is any consolation, the new URLs are transparent
	and permanent.  They are of the form &lt;category&gt;/&lt;port&gt;.
	</P>
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<? freshports_PageBannerText("Do you have any news feeds?"); ?>
</TR>

	<TR><TD>
	<P>
	Of course.  We have two:
	</P>

	<?
	$URL  = "http://$ServerName/news.php";
	$HREF = "<A HREF=\"$URL\">$URL</A>";
	?>

	<OL>
	<LI>An RSS feed : <? echo $HREF; ?></LI>

	<?
	$URL  = "http://$ServerName/sidebar.php";
	$HREF = "<A HREF=\"$URL\">$URL</A>";
	?>

	<LI>A Netscape 6, SideBar type feed : <? echo $HREF; ?>.  This can be added
		to your browser using the button in the right hand column of this page.</LI>

	</OL>

	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<? freshports_PageBannerText("Can the main page load any faster?"); ?>
</TR>

	<TR><TD>
	<P>
<a href="http://<?php echo $ServerName ?>/">http://<?php echo $ServerName ?>/</a> is the main page of 
this website.  It contains a lot of information.  You can trim this information by using parameters.

<p>
Try this URL: <a href="http://<?php echo $ServerName ?>/index.php?num=30&days=0">http://<?php echo $ServerName ?>/index.php?num=30&days=0</a>

<ul>
<li>
<b>num</b> - number of ports to show (regardless of commits so the last 
commit may not list fully). The valid values are 10..100.

<li>
<b>days</b> - number of summary days (in the right hand column) to display.
The valid values are 0..9.

<li>
<b>dailysummary</b> - similar to days, but displays a summary of the days instead
of a link to a page of commits for that day.
</ul>

Here are a few examples:

<blockquote>

<table BORDER="1" CELLSPACING="0" CELLPADDING="5">
<tr>
<td><b>Description</b></td>
<td nowrap valign="top"><b>URL</td>
</tr>

<tr>
<td>The last ten ports</td>
<td nowrap valign="top"><a href="http://<?php echo $ServerName ?>/index.php?num=10">http://<?php echo $ServerName ?>/index.php?num=10</b></a><br></td>
</tr>

<tr>
<td>Same as above, but show only two days of previous commits</td>
<td nowrap valign="top"><a href="http://<?php echo $ServerName ?>/index.php?num=10&days=2">http://<?php echo $ServerName ?>/index.php?num=10&<b>days=2</b></a><br></td>
</tr>

<tr>
<td>Same as above, but show summaries instead of a link to another page</td>
<td nowrap valign="top"><a href="http://<?php echo $ServerName ?>/index.php?num=10&dailysummary=2">http://<?php echo $ServerName ?>/index.php?num=10&<b>dailysummary=2</b></a></td>
</tr>

</table>
</blockquote>

BTW: I've found a way to improve the outer joins I've been doing in 
SQL and I'll be implementing that gradually.
</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<? freshports_PageBannerText("How can I view the commits for a particular day?"); ?>
</TR>

   <TR><TD>
   <P>
	Yes, you can.  <a href="http://<?php echo $ServerName ?>/date.php">http://<?php echo $ServerName ?>/date.php</a>
	displays all the commits for today (relative to the current server time).  

	<p>
	You can also pass a parameter and view the commits for a given day.  For example, 
	<a href="http://<?php echo $ServerName ?>/date.php?date=2002/11/19">http://<?php echo $ServerName ?>/date.php?date=2002/11/19</a>
	will show all the commits for 19 November 2002

	<p>
	The date should be of the format YYYY/MM/DD but I'm sure different formats
	will work.  If the code has trouble figuring out what date you mean, it will guess and let you know it adjusted the date.

	</TD></TR><TR><TD>&nbsp;</TD></TR>


</table>
</td>
  <TD VALIGN="top" WIDTH="*" ALIGN="center">
    <? require_once($_SERVER['DOCUMENT_ROOT'] . '/include/side-bars.php') ?>
 </td>
</tr>
</table>
<? require_once($_SERVER['DOCUMENT_ROOT'] . '/include/footer.php') ?>

</body>
</html>
