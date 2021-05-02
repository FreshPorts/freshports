<?php
	#
	# $Id: inthenews.php,v 1.2 2006-12-17 12:06:11 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_ConditionalGet(freshports_LastModified());

	$Title = 'In The News';
	freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');

?>
	<?php echo freshports_MainTable(); ?>
<tr><td valign="top" width="100%">
<table class="fullwidth borderless">
  <tr>
	<? echo freshports_PageBannerText("In the news"); ?>
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
slashdot - <a href="https://slashdot.org/article.pl?sid=00/05/10/1014226">BSD: FreshPorts</a>
</p>

Daily Daemon News - <a href="https://daily.daemonnews.org/view_story.php3?story_id=889"><? echo $FreshPortsTitle; ?> site announncement</a>
</TD>
</TR>
</TABLE>
</td>
  <TD class="sidebar" VALIGN="top" WIDTH="*" ALIGN="center">

	<?
	echo freshports_SideBar();
	?>
  </td>

</tr>
</table>

<?
echo freshports_ShowFooter();
?>

</body>
</html>
