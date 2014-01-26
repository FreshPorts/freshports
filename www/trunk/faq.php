<?php
	#
	# $Id: faq.php,v 1.7 2012-07-21 23:23:57 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_ConditionalGet(freshports_LastModified());

	freshports_Start('FAQ',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');

	$ServerName = str_replace('freshports', 'FreshPorts', $_SERVER['SERVER_NAME']);

	GLOBAL $FreshPortsName;
	GLOBAL $FreshPortsSlogan;

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(NOBORDER); ?>


<tr>
	<?php echo freshports_PageBannerText("FAQ"); ?>
</tr>
<TR><TD>

<CENTER>
<?php
    if ($ShowAds) echo Ad_728x90();
?>
</CENTER>

<P>This page contains the FAQ for FreshPorts. Hopefully the questions
are arranged from general to specific.  The more you know, the further
down you must read to find something you didn't already know.</P>
</TD></TR>
<TR><TD>&nbsp;</TD></TR>

<TR>

<?php echo freshports_PageBannerText("What is this website about?"); ?>

	<TR><TD>
	This website will help you keep up with the latest releases of your
	favorite software.  When a new version of the software is available,
	FreshPorts will send you an email telling you about the change.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText("How do I use this?"); ?>
</TR>

	<TR><TD>
	<p>
	Your primary FreshPorts tool is your <b>watch list</b>.  This is the
	collection of ports which you have selected for FreshPorts to
	keep track of.  You will be emailed when a change is found
	for one of your watched ports.

	<p>
	You can have more than one watch list.  Most people would have
	one watch list for each machine they administer.  I suggest giving
	the watch list the same name as the machine.  Email notifications
	will contain headers with the list name.  You can use that for any
	filtering you may want to do (e.g. procmail).
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText("Some definitions"); ?>
</TR>

	<TR><TD>
	<p>
	You should be familiar with the <a href="http://www.freebsd.org/doc/en_US.ISO8859-1/books/handbook/ports.html">Ports</a>
	section of <a href="http://www.freebsd.org/doc/en_US.ISO8859-1/books/handbook/index.html">The FreeBSD Handbook</a>.
	Pay careful attention to the difference between a port and a package.

	<h2>Definitions for the hopelessly lazy</h2>

	<h3><a name="port">What is a port?</a></h3>
	<p>
	If you install the port, the source will be downloaded, patched if necessary,
	compiled and installed.  You will have a chance to set any optional configuration
	items particular to that port. If the port is dependent upon another port,
	that port will also be installed.  After installing, ports are identical to
	packages.

	<p>
	It is because of these compile time options that I prefer ports over packages.

	<h3><a name="package">What is a package?</a></h3>
	<p>
	A package is a compiled port.  It is a binary.  When you install the package, 
	you will download a binary and it will be installed.

	<p>
	Packages often lag behind ports.  That is usually because it takes time to compile
	the packages, etc.  If you want the latest version, and the package is not
	available, then you should install the port.

	<p>
	Not all ports can have packages, often because of license restrictions that
	prevent binary distribution.

	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText("How do I modify my watch list?"); ?>
</TR>

	<TR><TD>
	There are three easy ways to modify your watch list:
	<OL>
	<LI>Wherever you see a port, you can click on the Add 
		(<?php echo freshports_Watch_Icon_Add(); ?>) /
		Remove (<?php echo freshports_Watch_Icon(); ?>)
		link as necessary (i.e. one-click watch list maintenance).</LI>
	<LI>The 'watch list categories' link provides you with a list
		of categories.  Select the category, and then the ports within
		that category.</LI>
	<LI>Use the 'upload' link to upload your pkg_info data into your
		watch list staging area and then into your watch list.</LI>
	</OL>

	<p>
	One-click watch list maintenance operates only upon your default
	watch lists.  You can set one or more watch lists as being default.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText("How do I empty my watch list?"); ?>
</TR>

	<TR><TD>
	Via <a href="/watch-list-maintenance.php">Watch List Maintenance</a>.
	Select the watch lists you wish to empty, and follow the instructions
	provided.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText("How do I delete my account?"); ?>
</TR>

	<TR><TD>
	You can't.  But you can unsubscribe from all of the reports
	and you'll never hear from us again.
	</TD></TR><TR><TD>&nbsp;</TD></TR>



<TR>
<?php echo freshports_PageBannerText("What is a port?"); ?>
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
<?php echo freshports_PageBannerText("Where do ports come from?"); ?>
</TR>

	<TR><TD>Ports are created by other FreeBSD volunteers, just like you
	and just like the creators of FreshPorts.  The FreshPorts team does
	not create ports; we just tell you about the latest changes.  The
	FreeBSD Ports team creates, maintains, and upgrades the ports.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText("Who do I talk to about a port?"); ?>
</TR>

	<TR><TD>The official mailing list is freebsd-ports&#64;freebsd.org.
		More information all FreeBSD mailing lists can be obtained
		from <A HREF="http://www.FreeBSD.org/handbook/eresources.html#ERESOURCES-MAIL">FreeBSD Mailing Lists</A>.
		You can ask for help there and in our <A HREF="/phorum/">Support
		Forum</A>.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText("How do I get these ports?"); ?>
</TR>

	<TR><TD>For full information on how to obtain the ports which appear on
	this website, please see <A HREF="http://www.FreeBSD.org/ports/">FreeBSD Ports</A>.
	The easiest way to get a port is via cvsup.  An abbreviated example is

	<BLOCKQUOTE>
	<CODE CLASS="code">cvsup -h cvsup.your.fav.server /usr/share/examples/cvsup/ports-supfile</CODE>
	</BLOCKQUOTE>
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText("How is the website updated?"); ?>
</TR>

	<TR><TD>
	The source code for the entire FreeBSD operating system and the Ports tree
	are stored in the official <A HREF="<?echo FRESHPORTS_FREEBSD_CVS_URL; ?>">FreeBSD 
	repository</A>.  Each time a change is committed to this <A HREF="http://cvshome.org/">CVS</A>
	repository, a mail message is sent out to the cvs-all mailing list.  FreshPorts
	takes these mail messages, parses them, and then loads them into a database.
	In theory, it's fairly straight forward.  In practice, there's much more to
	it than first meets the eye.  The website is updated as soon as the message
	arrives.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText("What does unknown mean for a revision number?"); ?>
</TR>

	<TR><TD>It means the data has been converted from an earlier
		version of the FreshPorts database that did not record this information.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText("How can I link to your site?"); ?>
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
		<CODE CLASS="code">&lt;img src="images/freshports-banner.gif" alt="<?php echo "$FreshPortsName -- $FreshPortsSlogan"; ?>" title="<?php echo "$FreshPortsName -- $FreshPortsSlogan"; ?>" width="468" height="60"&gt;</CODE>
		</BLOCKQUOTE>


		<P>Please save this graphic on your website.</P>
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText("Why do I need a different login for the Forums?"); ?>
</TR>

	<TR><TD>
	You only need a login for the <A HREF="/phorum/">forums</A> if
	you want to use a login.  A login will ensure that only you can
	post under the name you enter.  It is a separate login because
	we didn't write the <A HREF="http://www.phorum.org/">Phorum software</A>
	used to implement for forums.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText("What do these symbols mean?"); ?>
</TR>

	<TR><TD>
	There are a few symbols you will see in this website.
	<BLOCKQUOTE>
	<P><?php echo freshports_New_Icon() ?>
		New: This port has been recently added.  A port is marked as new for 10 days.</P>

	<P><a name="forbidden"><?php echo freshports_Forbidden_Icon() ?></a>
		Forbidden: The port is marked as forbidden.  If you view the port details,
		you will see why.  Most often, it is because of a security exploit. Packages for a forbidden port are not built
        by the package cluster.  Therefore, <code>pkg install</code> will not work.</P>

	<P><a name="broken"><?php echo freshports_Broken_Icon() ?></a>
		Broken: The port is marked as broken.  Perhaps it won't compile.  Maybe
		it doesn't work under FreeBSD right now.  If you view the port details,
		you will see the reason why. Packages for a broken port are not built
        by the package cluster.  Therefore, <code>pkg install</code> will not work.</P>

	<P><a name="deprecated"><?php echo freshports_Deprecated_Icon() ?></a>
		Deprecated: The port is marked as deprecated.  Perhaps it has exceeded
		its lifetime or is obselete. </P>

	<P><a name="expiration"><?php echo freshports_Expiration_Icon() ?></a>
		Expiration Date: The port has an expiration date. A port may be removed from the
        tree after this date. Often added in conjunction with Deprecated. </P>

	<P><a name="expired"><?php echo freshports_Expired_Icon() ?></a>
		Expired: The port has passed the expiration date. A port may be removed from the
        tree after this date. Often added in conjunction with Deprecated. </P>

	<P><a name="ignore"><?php echo freshports_Ignore_Icon() ?></a>
		Ignore: The port is marked as ignore.  It probably does not build. Packages for an ignored port are not built
        by the package cluster.  Therefore, <code>pkg install</code> will not work.</P>

	<P><?php echo freshports_Files_Icon(); ?>
		Files: If you click on this graphic, you will be taken to the list of files
		touched by the commit in question.</P>

	<P><a name="refresh"><?php echo freshports_Refresh_Icon(); ?></a> Refresh: 
		The system is in the process of refreshing that port by inspecting
		the ports tree.  You should rarely see this.</P>
		<p>If you do see one, chances are that the port contains an error
		that prevents make(1) from running.  For example:

<blockquote><code class="code">
$ make -V PORTVERSION<br>
"Makefile", line 271: 1 open conditional<br>
make: fatal errors encountered -- cannot continue
</code></blockquote>

		<p>
		In such circumstances, the port committer is notified (if they have
		opted in to the FreshPorts Sanity Check Report) and they should
		fix the problem as soon as possible.  Once you see a more recent
		commit without a refresh icon,
		then the problem has been fixed. The refresh icons will go away after
		FreshPorts has dealt with the old commits.  This may take a few hours.

	<P><a name="deleted"><?php echo freshports_Deleted_Icon(); ?></a> Deleted:  This port has been removed from the ports tree.</P>

	<P><?php echo freshports_Mail_Icon(); ?>
		Commit message: This link will take you to the original cvs-all message in the FreeBSD mailing list archives.
		Note that it can take a few minutes for the message to appear in the archives.  This link will not appear
		for commit messages before 3 March 2002 (which is the date FreshPorts started to store the message-id).</P>

	<P><?php echo freshports_Commit_Icon(); ?> FreshPorts commit message: This will take you to the FreshPorts commit
		message and allow you to see all other ports which were affected by this commit.   This link will not appear
        for commit messages before 3 March 2002 (which is the date FreshPorts started to store the message-id).
        <br>
        <br>
        NOTE: This link has been made redundant by recent advances in the Files link.  See next icon.
        </P>

	<P><?php echo freshports_Files_Icon(); ?>
		Files: If you click on this graphic, you will be taken to the list of files
		touched by the commit in question.</P>

	<P><?php echo freshports_Watch_Icon(); ?> Item is on one of your default watch lists: This port is on one of your default watch lists.  Click
		this icon to remove the port from your default watch lists.  This icon appears only if you are logged in.</P>

	<P><?php echo freshports_Watch_Icon_Add(); ?> Add item to your default watch lists: This port is not on any of your  default watch lists.  Click
		this icon to add the port to your default watch lists.  This icon appears only if you are logged in.</P>

	<P><a name="encodingerrors"><?php echo freshports_Encoding_Errors(); ?></a> Encoding Errors (not all of the commit message was ASCII): Some of the
		commit message may be altered because of character conversion problems.  We display only UTF-8 and remove
		the offending characters.  These errors may occur in the log message or elsewhere in the commit email.</P>

	<P><a name="watchlistcount"><?php echo freshports_WatchListCount_Icon(); ?></a> Watch List Count (WLC): This is the number of watch lists which are watching 
	this port.  This might give you an idea of the popularity of the port.
	</P>

	<P><?php echo freshports_CVS_Icon(); ?> CVS Repository: This link will take you to the CVS Repository entry
	for this version of the file.
	</P>

	<P><?php echo freshports_VuXML_Icon(); ?> <a href="http://www.vuxml.org/freebsd/">VuXML</a> vulnerability.  Click icon for details.
	</P>

	<P><?php echo freshports_VuXML_Icon_Faded(); ?> A past <a href="http://www.vuxml.org/freebsd/">VuXML</a> vulnerability.  Click icon for details.
	</P>

	<P><a name="restricted"><?php echo freshports_Restricted_Icon(); ?></a> This port has some restrictions on it.
	</P>

	<P><a name="no_cdrom"><?php echo freshports_No_CDROM_Icon(); ?></a> This port has some restrictions with respect to being included on a CD-ROM.
	</P>

	<P><a name="is_interactive"><?php echo freshports_Is_Interactive_Icon(); ?></a> This port will require interaction during installation.
	</P>

	<P><?php echo freshports_Revision_Icon(); ?> Revision details.  Click on the Files icon in the commit history and
	you'll see what files in this port were touched by this commit.  Click on the Revision details to view the
	revision of the file associated this commit.
	</P>

	<P><?php echo freshports_Diff_Icon(); ?> Diff.  Click on the Files icon in the commit history and
	you'll see what files in this port were touched by this commit.  Click on the Diff icon to view the
	diff between this revision of the file and the previous revision..
	</P>

	<P><?php echo freshports_SanityTestFailure_Icon(); ?> Sanity Test Failures.  The maintainers and committers are
	good.   But sometimes a mistake slips through.  This records the mistake to make it easier for others to correct
	it if it goes unnoticed.  If you see this icon next to a commit, it failed a Sanity Test.
	</P>

	<P><?php echo freshports_Commit_Flagged_Icon(); ?> Flagged Commit.  This commit is on your list of flagged
	commits.Why would you flag a commit?  Perhaps you want to review that commit.  Perhaps you want to MFC it later.
	Click on this icon to remove the commit from your flag list.
	</P>
	
    <P><?php echo freshports_Commit_Flagged_Not_Icon(); ?> Click on this icon to add the commit to your flagged
	list.
	</P>

	</BLOCKQUOTE>
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText("Why don't my old bookmarks work?"); ?>
</TR>

	<TR><TD>
	<P>
	Many things changed between FP1 and FP2. The most major change
	was in the underlying database schema.  Not only did we move
	from <A HREF="http://www.mysql.org/">mySQL</A> to
	<A HREF="http://www.postgresql.org/">PostgreSQL</A>, we made major
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
<?php echo freshports_PageBannerText("Do you have any news feeds?"); ?>
</TR>

	<TR><TD>
	<P>
	Yes.  Read <a href="/newsfeeds.php">all about it</a>!

	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText("Can the main page load any faster?"); ?>
</TR>

	<TR><TD>
	<P>
<a href="http://<?php echo $ServerName ?>/">http://<?php echo $ServerName ?>/</a> is the main page of 
this website.  It contains a lot of information.  You can trim this information by using parameters.

<p>
Try this URL: <a href="http://<?php echo $ServerName ?>/index.php?num=30&amp;days=0">http://<?php echo $ServerName ?>/index.php?num=30&amp;days=0</a>

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
<td nowrap valign="top"><b>URL</b></td>
</tr>

<tr>
<td>The last ten ports</td>
<td nowrap valign="top"><a href="http://<?php echo $ServerName ?>/index.php?num=10">http://<?php echo $ServerName ?>/index.php?<b>num=10</b></a><br></td>
</tr>

<tr>
<td>Same as above, but show only two days of previous commits</td>
<td nowrap valign="top"><a href="http://<?php echo $ServerName ?>/index.php?num=10&amp;days=2">http://<?php echo $ServerName ?>/index.php?num=10&amp;<b>days=2</b></a><br></td>
</tr>

<tr>
<td>Same as above, but show summaries instead of a link to another page</td>
<td nowrap valign="top"><a href="http://<?php echo $ServerName ?>/index.php?num=10&amp;dailysummary=2">http://<?php echo $ServerName ?>/index.php?num=10&amp;<b>dailysummary=2</b></a></td>
</tr>

</table>

</blockquote>

<P>
<b>NOTE:</b> Effective 13 November 2003, these parameters are no longer available.
</P>

</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText("How can I view the commits for a particular day?"); ?>
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

<TR>
<?php echo freshports_PageBannerText("Why can't I add a port to my watch list?"); ?>
</TR>

   <TR><TD>
   <P>
	You have clicked on the <?php echo freshports_Watch_Icon_Add(); ?> icon and 
	it doesn't change to a <?php echo freshports_Watch_Icon(); ?>.  Yes, I've
	had that happen too.  What you need to do is check your 
	<a href="/watch-list-maintenance.php">watch list settings</a>.  You have 
	probably selected "default watch list[s]" when you don't have any default watch
	list[s] set.  To mark a watch list as a default, select it in the list, then click on
	the Set Default button.
	
	<p>
	NOTE: The <?php echo freshports_Watch_Icon(); ?> will only appear beside a port
	that is one on of your default watch lists.  If the port is on one of your non-default
	watch list, the <?php echo freshports_Watch_Icon_Add(); ?> icon will appear instead.
	If you do not see what you expect, try setting the default watch lists in your
	 <a href="/watch-list-maintenance.php">watch list settings</a>.

	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText("Why doesn't this port appear on my watch list?"); ?>
</TR>

   <TR><TD>
   <P>
   Please refer to the above question.
   </TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText("What are Port Moves?"); ?>
</TR>

   <TR><TD>
   <P>
	Some ports (for example <a href="/net/">net</a>/<a href="/net/gift/">gift</a>) will have a section titled "Port Moves".
	FreshPorts obtains information about ports from the commits to the 
	<a href="<?php echo FRESHPORTS_FREEBSD_CVS_URL; ?>">CVS Repository</a>.  However, not all
	changes to ports occur because of commits.  A manual change to the repository,
	often referred to as a repo-copy, can move a port from one category to another.
	Such a change is done to ensure the port history is retained.

	<p>
	Repo-copies are documented in <a href="/MOVED">/usr/ports/MOVED</a>.  FreshPorts parses this file and records
	these changes in its database.

	<p>
	This new feature was added on 31 December 2003.


	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText("What is /usr/ports/UPDATING?"); ?>
</TR>

   <TR><TD>
   <P>
<code class="code">/usr/ports/UPDATING</code> is similar to
<code class="code">/usr/src/UPDATING</code>, but for ports,
not for the source tree.

<p>
FreshPorts parses this file and attempts to relate the entries to any ports
it can find.  Such relations are not always possible.  We do the best we can.
The <a href="/net/openldap22-client/">net/openldap22-client</a> port is a good
example of what to expect.

	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText("What are Master/Slave ports?"); ?>
</TR>

   <TR><TD>
   <P>
	Some ports are so similar to another port that it makes sense to maintain just one port
	and specify the differences in the other port.  This is slightly similar to the way 
	<code class="code">/etc/defaults/rc.conf</code> is related to 
	<code class="code">/etc/rc.conf</code>.

	<p>
	A good example is <a href="/www/">www</a>/<a href="/www/mod_php4">mod_php4</a>. You 
	can see that the <b>master port</b> for that is <a href="/lang/">lang</a>/<a href="/lang/php4/">php4</a>.
	Conversely, <a href="/lang/">lang</a>/<a href="/lang/php4/">php4</a> lists several
	<b>slave ports</b>.

	<p>
	The ability to add this feature is because of this patch:

<blockquote><pre class="code">
--- bsd.port.mk	10 Jun 2004 07:30:19 -0000	1.491
+++ bsd.port.mk	22 Jun 2004 13:48:33 -0000
@@ -913,6 +913,16 @@
 
 MASTERDIR?=	${.CURDIR}
 
+# Try to determine if we are a slave port.  These variables are used by
+# FreshPorts and portsmon, but not yet by the ports framework itself.
+.if ${MASTERDIR} != ${.CURDIR}
+IS_SLAVE_PORT?=	yes
+MASTERPORT?=	${MASTERDIR:C/[^\/]+\/\.\.\///:C/[^\/]+\/\.\.\///:C/^.*\/([^\/]+\/[^\/]+)$/\\1/}
+.else
+IS_SLAVE_PORT?=	no
+MASTERPORT?=
+.endif
+
 # If they exist, include Makefile.inc, then architecture/operating
 # system specific Makefiles, then local Makefile.local.
 
</pre></blockquote>

	<p>
	The FreeBSD tree has no defined method for handling master/slave ports.
	It is because of this that FreshPorts never attempted to refresh slave ports
	when a master port was updated.  Now that we have a mostly-reliable method,
	all slave ports are refreshed when the master port is updated.

	<p>
	This method works for all but 40 ports which are involved in a master/slave relationship.
	It is hoped that those 40 are fixed soon.  It is also hoped that the above patch
	is comitted to the tree.

	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText('What is this "to add the package" stuff?'); ?>
</TR>

	<TR><TD>
	<P>
	Included within the port description is the instruction for adding the package.
	This information can be important when the package name does not match the
	port name.  A good example is <a href="/x11/">x11</a>/<a href="/x11/XFree86-4-clients/">XFree86-4-clients</a>.
	The command to add this package is:

<blockquote><code class="code">
pkg install XFree86-clients
</code></blockquote>

	<p>
	This normally isn't a problem, but for the 1900 or so ports which are different,
	this information is very useful.

	<p>
	If the <code class="code">pkg install</code> information does not appear,
	you'll be told why there is no package.  This is controlled by the
	<code class="code">NO_PACKAGE</code> variable in the port's Makefile.

	<p>
	Broken, ignored, and forbidden ports are not built by the package
	cluster.  Therefore, there is no package for <code>pkg install</code> to use.
	
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText('Why does the search page use GET and not POST?'); ?>
</TR>

   <TR><TD>
   <P>
If you visit the <a href="/search.php">search</a> page, and you run a search,
you'll find that the URL becomes very long.  For example, 
<a href="/search.php?query=bacula&amp;search=go&amp;num=10&amp;stype=name&amp;method=match&amp;deleted=excludedeleted&amp;start=1&amp;casesensitivity=caseinsensitive">this really long link</a>.
<p>
Long URLs occur like that because the search form uses a GET.  A long URL
would not occur if it was using a POST.  The long URLs are useful because
they allow you to bookmark your favorite search.  That is why a GET is used
instead of a POST.

<p>
It also makes it easier to <a href="http://validator.w3.org/">validate the HTML</a>
if you can provide a URL that exercises all the options that require testing.

	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText('What are all those fields I can search on?'); ?>
</TR>

   <TR><TD>
   <P>
	For those familiar with the FreeBSD ports structure, the following fields indicate their origin:

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

	For all of the above origins, you can obtain the value using 
	<a href="http://www.freebsd.org/cgi/man.cgi?query=make&amp;apropos=0&amp;sektion=0&amp;manpath=FreeBSD+5.3-RELEASE+and+Ports&amp;format=html"><code class="code">make</code></a>.
	For example:

<blockquote><code class="code">
$ cd /usr/ports/sysutils/bacula/<br>
$ make -V PORTNAME<br>
bacula<br>
$
</code></blockquote>

<sup>1</sup> This value is obtained from a file in the port directory.  For
example <code class="code">/usr/ports/sysutils/bacula/pkg-descr</code>.

	</TD></TR><TR><TD>&nbsp;</TD></TR>


<TR>
<?php echo freshports_PageBannerText('Where did this "People watching this port, also watch" feature come from?'); ?>
</TR>

   <TR><TD>
   <P>
Like many FreshPorts features, this idea came from someone else.  Florent 
Thoumie mentioned something about extending the ports system to include 
recommendations from maintainers/committers.  Such a feature would allow
a committer/maintainer to suggest, for example, that if you install Firefox,
that you also install linuxflashplugin.

<p>
It was from this concept that I came up with "People watching this port...".
This information is obtained by:
<ol>
<li>FreshPorts takes the port you are looking at
<li>It finds all the watch lists that this port appears on
<li>It finds the top 5 most popular ports on those lists
<li>FreshPorts shows you the results
</ol>

<p>
All of this takes about 55ms.

	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText('What do you mean, the master port has been updated?'); ?>
</TR>

   <TR><TD>
   <P>
For some slave ports, you may see a message like this, just above the <b>Commit History</b>:

<blockquote>
NOTE: This slave port may no longer be vulnerable to issues shown below because the master port has been updated.
</blockquote>

<p>
Slave ports can be updated with a commit against the master port.  A commit
against the master port will affect any slave ports.  If a
<a href="http://www.vuxml.org/freebsd/">VuXML</a> vulnerability has been recorded
against a slave port, any fix would be applied to the master port.
However, the commit to the master port would not appear under the slave port,
thereby giving a false impression
that the slave port was still vulnerable.

<p>
The above notice serves as a reminder that the slave port may no longer be vulnerable.

	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText('How does FreshPorts determine the master sites?'); ?>
</TR>

   <TR><TD>
   <P>
Each port displays the master sites from which its distfiles can be downloaded.  This
information is obtained from "make master-sites-all".  However, this is not the only
list of master sites that a port knows about.  Edwin Groothuis explains it in this
<a href="http://docs.freebsd.org/cgi/mid.cgi?20041219204057.GE63708">email</a>.

<p>
In short, FreshPorts displays the list of master sites that should contain all
the distfiles.  That is why we use that value, and not one of the other options.

	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText('Why don\'t you obscure email addresses?'); ?>
</TR>

   <TR><TD>
   <P>
FreshPorts used to obscure email addresses, but we don't any more.  We realised that
every email address on FreshPorts is already somewhere else first. For example:

<ul>
<li>www/ports pages
<li>portsmon
<li>fenner's output
<li>GNATS
<li>cvsweb
</ul>

<p>
In short, it doesn't make sense to obscure that which is freely available elsewhere.
	</TD></TR><TR><TD>&nbsp;</TD></TR>

<TR>
<?php echo freshports_PageBannerText('Why does the PORTVERSION at the top of page differ from that of the first commit?'); ?>
</TR>

   <TR><TD>
   <P>
   This question refers to the a port page.  
   
<p>
This situation usually occurs with MASTER/SLAVE ports.  The Master port is updated with a new
REVISION.  No commit is done against the Slave port.  FreshPorts knows to refresh the Slave port
when its Master port is updated.    This refresh updates the PORTVERSION at the top of the 
page.  This update reflects the REVISION you would get if you were to install the Slave port
now that the Master has been upgraded.

	</TD></TR><TR><TD>&nbsp;</TD></TR>

</table>
</td>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
	<?php
	echo freshports_SideBar();
	?>
  </td>

</tr>
</table>

<?php
echo freshports_ShowFooter();
?>

</body>
</html>
