<?php
	#
	# $Id: now-in-maintenance-mode.php
	#
	# Copyright (c) 1998-2006 DVL Software Limited
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
	$Title = 'Maintenance Mode';
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
	<?php echo freshports_PageBannerText("Maintenance Mode"); ?>
</tr>
<TR><TD>

<p>
The website is now in maintenance mode. No updates are allowed during this process.
</p>

<p>
This page will reload every <?php echo MAINTENANCE_MODE_RERESH_TIME_SECONDS; ?> seconds. When maintence mode finishes, this page will be redirect to the home page.
</p>

<p class="maintenance">
<img src="images/work-in-progress.jpg" width="640" height="480" alt="work in progress">
</p>

</TD></TR>

</TABLE>
</TD>

  <td class="sidebar">
	<?
	echo freshports_SideBar();
	?>
  </td>

</TR>
</TABLE>

<?php
echo freshports_ShowFooter();
?>

</body>
</html>
