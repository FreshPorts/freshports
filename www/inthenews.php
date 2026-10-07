<?php
	#
	# $Id: inthenews.php,v 1.2 2006-12-17 12:06:11 dan Exp $
	#
	# Copyright (c) 1998-2026 Dan Langille
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_ConditionalGet(freshports_LastModified());

	$Title = _('In The News');
	freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');

?>
	<?php echo freshports_MainTable(); ?>
<tr><td class="content">
<table class="fullwidth borderless">
  <tr>
	<?php echo freshports_PageBannerText(_('In the news')); ?>
  </tr>

<tr>
<td>
<p><?php echo sprintf(_('This page is just a place for me to record the %s articles which appear on other sites.  Links are recorded in reverse chronological order (i.e. newest first).'), $FreshPortsTitle); ?>
</p>
<p>
BSD Today - <a href="http://www.bsdtoday.com/2000/May/News146.html">Keeping track of your favorite ports</a>
</p>

<p>
slashdot - <a href="https://slashdot.org/article.pl?sid=00/05/10/1014226">BSD: FreshPorts</a>
</p>

Daily Daemon News - <a href="https://daily.daemonnews.org/view_story.php3?story_id=889"><?php echo sprintf(_('%s site announcement'), $FreshPortsTitle); ?></a> -
(<?php echo _('That link no longer works, try this copy on the Internet Archive instead:'); ?> <a href="https://web.archive.org/web/20020424042757/https://daily.daemonnews.org/view_story.php3?story_id=889"><?php echo sprintf(_('%s site announcement'), $FreshPortsTitle); ?></a>)
</td>
</tr>
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
