<?php
	#
	# $Id: faq.php,v 1.7 2012-07-21 23:23:57 dan Exp $
	#
	# Copyright (c) 1998-2026 Dan Langille
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_Start(_('FreshPorts News Feeds'),
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');

	GLOBAL $FreshPortsName;
	GLOBAL $FreshPortsSlogan;

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(NOBORDER); ?>


<tr>
	<?php echo freshports_PageBannerText(_('FreshPorts News Feeds')); ?>
</tr>
<tr><td class="content">
<h1><?php echo _('FreshPorts News Feeds'); ?></h1>

<p><?php echo sprintf(_('The feeds which were here have moved to <a href="%s">%s</a>, and their old URLs redirect there. Please update your subscriptions.'), '/--/feeds/', '/--/feeds/'); ?></p>

<p><?php echo _('The JavaScript, mbox and OPML feeds have been retired.'); ?></p>

<h2><?php echo _('HTML'); ?></h2>

<p><?php echo sprintf(_('<a href="%s">%s</a> gives the latest commits as HTML, for showing in another page. It takes the optional parameters <b>flavor</b> and <b>branch</b>, as described at <a href="%s">%s</a>.'), 'html.php', 'html.php', '/--/feeds/', '/--/feeds/'); ?></p>

<h2><?php echo _('Other feeds'); ?></h2>
<ol>
    <li><p><?php echo _('A Personal News feed for each of your watch lists. Look for the link under the <code>Watch Lists</code> box after you have logged in.'); ?></li>

    <li><p><?php echo _('The blog for this website, <a href="https://news.freshports.org/">FreshPorts News</a>.'); ?>
</ol>

</table>
</TD>
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
