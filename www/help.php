<?php
	# $Id: help.php,v 1.2 2006-12-17 12:06:11 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_ConditionalGet(freshports_LastModified());

	$Title = 'pkg_info >> watch list';
	freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(NOBORDER); ?>
<tr>
	<?php echo freshports_PageBannerText("pkg_info and your watch list "); ?>
</tr>

<tr><td>
<P>
One of the most powerful FreshPorts features is the ability to tap
the existing information on your FreeBSD system and use it to 
modify your watch list.  This is achieved using the 
<a href="/pkg_upload.php">pkg_info upload facility</a>.  This is a two 
stage process:
<ol>
<li>upload the pkg_info output from your system into your watch list
	staging area</li>
<li>choose the elements from your staging area and save them to your
	watch list</li>
</ol>

The rest of this page should answer everything.

</td></tr><tr><td>&nbsp;</td></tr>

<tr>
	<?php echo freshports_PageBannerText("How does pkg_upload work?"); ?>
</tr>
<tr><td>
<P>
Mostly with smoke and mirrors, a small group of lowly trained but
highly motivated cats, and a pair of keas.  Actually, those
team members are behind the scenes and we do not like to let
them share the limelight.
In brief, FreshPorts uses the basic information
available on your system.  It's pretty basic.  And easy.
</P>

</td></tr><tr><td>&nbsp;</td></tr>

<tr>
	<?php echo freshports_PageBannerText("How does pkg_upload really work?"); ?>
</tr>

<tr><td>
<P>
For each installed port on your FreeBSD system, there is an entry in the
ports database.  This database is at <code class="code">/var/db/pkg/</code>.
The <code class="code">pkg_info</code> command interrogates this database
and can produce a list of the installed ports.  The basic command outputs
the name of the installed package and a brief description. Using the 
<code class="code">-qoa</code> options, FreshPorts obtains the 
&lt;category&gt;/&lt;port&gt; format of the installed packages.  This 
format is useful for three reasons:

<ol>
<li>It mirrors the directory structures of the ports tree</li>
<li>It is easily parsed and understood by FreshPorts</li>
<li>It is the minimal information necessary to obtain the objective</li>
</ol>

Here is a short example of the output from that command:

<blockquote><pre class="code">
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
</pre></blockquote>

<P>
The 
The output from this command is uploaded and saved into the FreshPorts 
database in your personal staging area.

</td></tr><tr><td>&nbsp;</td></tr>

<tr>
	<?php echo freshports_PageBannerText("What is a staging area?"); ?>
</tr>
<tr><td>
The watch list staging area is a temporary storage area
for your upload pkg_info output.  It serves as an area in which you
can compare the new and old watch lists before saving any changes.
</td></tr><tr><td>&nbsp;</td></tr>

<tr>
	<?php echo freshports_PageBannerText("What are the different sections in my staging area?"); ?>
</tr>
<tr><td>
There are four sections to your staging area.  These will be listed from
left to right.

<ol>
<li>pkg_info - The first section lists the ports found in your pkg_info 
	upload.  Those which also appear in your watch list will be marked
	by a 'W'.
</li>
<li>Not found - It is possible for ports from your pkg_info output to
	not be found in FreshPorts.  Over time, ports are deleted, renamed,
	or moved.  This section contains the ports from your pkg_info 
	output that could not be found within FreshPorts.
</li>
<li>Duplicates - Sometimes you install the same port twice, and not always
	by design.  If that happens, these ports will be listed in this section.
	Unless you need both versions of these ports for a particular reason, you
	should consolidate this situation.</li>
<li>Watch List - This section contains the ports which are on your watch list
	which did not appear in your pkg_info upload.</li>
</ol>

You should inspect that information carefully before saving it to your watch list.

</td></tr><tr><td>&nbsp;</td></tr>

<tr>
	<?php echo freshports_PageBannerText("What are these check boxes in my staging area?"); ?>
</tr>
<tr><td>
Your staging area is just a temporary situation.  You must save this information
to your watch list.  You can choose the ports you wish to watch using the
check boxes.  Only selected ports will be saved to your watch list.
</td></tr><tr><td>&nbsp;</td></tr>

<tr>
	<?php echo freshports_PageBannerText("Why would I want to clear my staging area?"); ?>
</tr>
<tr><td>
You cannot upload anything to your staging area unless it is empty.  If you 
don't wish to save the data to your watch list, you must first clear
your staging area before uploading again.
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

</body>
</html>
