<?
	# $Id: about.php,v 1.1.2.5 2002-04-19 19:46:43 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");
	require("./include/getvalues.php");

	freshports_Start("About this site",
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

?>
<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<tr><td VALIGN=TOP>
<TABLE>
<TR>
	<? freshports_PageBannerText("About this site"); ?>
</TR>

<TR><TD>
<P>
We have here a few notes about this website.
</P>
</TD></TR>

<tr>
<td valign="top" width="100%">

<tr>
	<? freshports_PageBannerText("What is a port?"); ?>
</tr>

<tr><td>

<p>A port is the term used to describe a collection of files which makes it extremely
easy to install an application.  As it says in the <a href="http://www.freebsd.org/ports/">
FreeBSD Ports description</a>: <em>Installing an application is as simple as downloading 
the port, unpacking it and typing <b>make</b> in the port directory</em>. If you want an application, 
the port is the Way To Go(TM)</p>

<p>So off you go to the ports tree to install your favourite port.  It's quite easy. It's simple.
And you love that new application.  And you want to know when the port is updated.  That's where
we come in.</p>

<p>For more information about the Ports tree, see <a href="http://www.freebsd.org/ports/">http://www.freebsd.org/ports/</a>.</p>

</td></tr>
<tr>
	<? freshports_PageBannerText("What is $FreshPortsTitle"); ?>
</tr>

<tr><td>

<p><? echo $FreshPortsTitle; ?> lists the change made to the ports tree. If you wish, <? echo $FreshPortsTitle; ?> can email you 
when your favourite port has been updated.
</p>

<P>
<? echo $FreshPortsTitle; ?> is not the place to report errors or request changes.  You should do that on the 
<A HREF="mailto:freebsd-ports&#64;freebsd.org">FreeBSD Ports mailing list</A>.  We do not maintain ports.  We do not
create ports.  We do not fix ports.  We just tell you what others have been doing to the Ports tree.
</P>

<td></tr>
<tr>
	<? freshports_PageBannerText("OK, whose bright idea was this?"); ?>
</tr>

<tr><td>
<p>This site was created by Dan Langille.  His other web feats include 
<a href="http://www.freebsddiary.org/">The FreeBSD Diary</a>, <a 
href="http://www.racingsystem.com">The Racing System</a>, and an ability
to avoid reading the inane comments on <a href="http://slashdot.org">slashdot</a>.
But Dan didn't create the site all by himself.  Have a look at <a href="authors.php">
About the Authors</a> for details of who else helped.</p>
</table>
</td>
  <td valign="top">
    <?
       $ShowPoweredBy = 1;
       include("./include/side-bars.php");
    ?>
 </td>
</table>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include("./include/footer.php") ?>
</TD></TR>
</TABLE>

</body>
</html>
