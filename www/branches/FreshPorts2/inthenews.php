<?
	# $Id: inthenews.php,v 1.1.2.10 2002-12-11 04:44:36 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	freshports_Start('In The News',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');

?>
<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<tr><td valign="top" width="100%">
<table width="100%" border="0">
  <tr>
	<? freshports_PageBannerText("In the news"); ?>
  </tr>

<TR>
<TD VALIGN="top">
<p>This page is just a place for me to record the <? echo $FreshPortsTitle; ?> articles which appear
on other sites.  Links are recorded in reverse chronological order (i.e. newest first).
</p>
<p>
BSD Today - <a href="http://www.bsdtoday.com/2000/May/News146.html">Keeping track of your favorite ports</a>
</p>

<p>
slashdot - <a href="http://slashdot.org/article.pl?sid=00/05/10/1014226">BSD: FreshPorts</a>
</p>

Daily Daemon News - <a href="http://daily.daemonnews.org/view_story.php3?story_id=889"><? echo $FreshPortsTitle; ?> site announncement</a>
</TD>
</TR>
</TABLE>
</td>
  <TD VALIGN="top" WIDTH="*" ALIGN="center">
    <? require_once($_SERVER['DOCUMENT_ROOT'] . '/include/side-bars.php') ?>
 </td>
</tr>
</table>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? require_once($_SERVER['DOCUMENT_ROOT'] . '/include/footer.php') ?>
</TD></TR>
</TABLE>

</body>
</html>
