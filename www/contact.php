<?php
	#
	# $Id: contact.php,v 1.1 2007-10-21 16:59:05 dan Exp $
	#
	# Copyright (c) 2007-2026 Dan Langille
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_ConditionalGet(freshports_LastModified());

	$Title = _('Contact');
	freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(); ?>


<tr>
	<?php echo freshports_PageBannerText(_('Contact')); ?>
</tr>
<tr><td>

<P>
<?php echo _('This is a pretty big website.  Roughly 600,000 pages as of Oct 2007. And 1.8 million as of June 2020.'); ?>

<p>
<?php echo _('If you need help with a particular port, please go through the FreeBSD mailing lists.'); ?>

<p>
<?php echo _('If you see a problem with the website (incorrect information, errors, etc), please let us know.  The best place for that is via a <a href="https://github.com/FreshPorts/freshports/issues" rel="noopener noreferrer">GitHub Issue</a>.'); ?>

<p>
<?php echo sprintf(_('If your needs do not fall into the above categories, you can try email: %s.'), 'dan (at) langille.org'); ?>
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
