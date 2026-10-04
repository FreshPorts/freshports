<?php
	#
	# $Id: now-in-maintenance-mode.php
	#
	# Copyright (c) 1998-2026 Dan Langille
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_ConditionalGet(freshports_LastModified());

	if (IN_MAINTENANCE_MODE) {
		header('Refresh: ' . MAINTENANCE_MODE_RERESH_TIME_SECONDS);
	} else {
		header('Location: /', TRUE, 307);
	}
	$Title = _('Maintenance Mode');
	freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');

	$ServerName = str_replace('freshports', 'FreshPorts', $_SERVER['HTTP_HOST']);

	GLOBAL $FreshPortsName;
	GLOBAL $FreshPortsSlogan;


?>
	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(NOBORDER); ?>


<tr>
	<?php echo freshports_PageBannerText(_('Maintenance Mode')); ?>
</tr>
<TR><td>

<p>
<?php echo _('The website is now in maintenance mode. No updates are allowed during this process.'); ?>
</p>

<p>
<?php printf(ngettext('This page will reload every %d second. When maintenance mode finishes, this page will be redirected to the home page.', 'This page will reload every %d seconds. When maintenance mode finishes, this page will be redirected to the home page.', MAINTENANCE_MODE_RERESH_TIME_SECONDS), MAINTENANCE_MODE_RERESH_TIME_SECONDS); ?>
</p>

<p class="maintenance">
<img src="images/work-in-progress.jpg" width="640" height="480" alt="<?php echo htmlspecialchars(_('work in progress')); ?>">
</p>

</td></TR>

</table>
</td>

  <td class="sidebar">
	<?php
	echo freshports_SideBar();
	?>
  </td>

</TR>
</table>

<?php
echo freshports_ShowFooter();
?>

</body>
</html>
