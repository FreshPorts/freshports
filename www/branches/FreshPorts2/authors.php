<?
	# $Id: authors.php,v 1.1.2.4 2002-02-21 23:13:52 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");
	require("./include/getvalues.php");

	freshports_Start("The Authors",
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

?>
<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<tr><td valign="top">
<table width="100%" border="0">
  <tr>
	<? freshports_PageBannerText("About the authors"); ?>
  </tr>
<TR><TD>
<p>Dan Langille thought up the idea, found the data sources, bugged people to 
write scripts, and did the html and database work. But he certainly didn't 
do it alone.</p>

<UL>

<LI>Olaf wrote did the perl script for the log catcher.</LI>

<LI>icmpecho wrote the awk code for the log catcher and the log munger.</LI>

<LI>Adriel helped me with perl syntax.</LI>

<LI>Acme talked over data sources with me.</LI>

<LI>John Polstra and Satoshi Asami provided insight into cvs and ports as well
as encouragement.</LI>

<LI>Laz hung around, criticized, and suggested security improvments.</LI>

<LI>halflife did some prototype coding for me.</LI>

<LI>David Bushong did a FreshBSD site which is a freshmeat-look site.</LI>

<LI>lzh on undernet #perl helped me with my perl knowledge.  Some of his examples 
form the basis for some of the most important parts of the system.  Aquitaine
also showed me the PERL dbi->quote() function.</LI>

<LI>John Beige did the logo you see at the top of the page.</LI>

<LI>Wolfram Schneider's <a href="http://www.freebsd.org/cgi/ports.cgi">FreeBSD Ports Changes</a>
page provided much of the basis for this site.</LI>

<LI>Jay gave me the box on which FreshPorts runs.  Thanks.</LI>

<LI>And various people on undernet's #nz.general and #freebsd helped me with 
scripts and ideas.  That's not to mention that channel on efnet which I won't 
name just so it stays a secret.</LI>

</UL>

<P>And I haven't updated the list of people who helped with FreshPorts2...</P>

</TD>
</TR>
</TABLE>
</td>
  <td valign="top" width="*">
    <? include("./include/side-bars.php") ?>
 </td>
</tr>
</table>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include("./include/footer.php") ?>
</TD></TR>
</TABLE>

</body>
</html>
