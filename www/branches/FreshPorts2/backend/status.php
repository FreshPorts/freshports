<?php
	#
	# $Id: status.php,v 1.1.2.1 2003-09-28 15:03:04 dan Exp $
	#
	# Copyright (c) 2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	$Debug = 0;

	$Title    = "Status";

	freshports_Start($Title,
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

?>

<TABLE width="<? echo $TableWidth ?>" border="0" ALIGN="center">

<TR><TD valign="top" width="100%">
<TABLE width="100%" border="0">
<TR>
	<? echo freshports_PageBannerText($Title); ?>
</TR>
<TR><TD>
System status
</TD></TR>
<tr><td>
<?

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../configuration/status-config.php');

echo '<table border="1">' . "\n";
echo '<tr><td></td><td align="center" colspan="' . count($sites) . '">sites</td></tr>' . "\n";
echo '<tr><td>queues</td>';
foreach ($sites as $site) {
	echo '<td><b>' . $site . '</b></td>';
}
echo "</tr>\n";

foreach ($queues as $queue => $pattern) {
	echo '<tr><td><b>' . $queue_names[$queue] . '</b></td>';
	foreach ($sites as $site) {
		$count = exec("ls $base/$site/msgs/FreeBSD/$queue/$pattern | wc -l");
		echo '<td align="right">' . $count . '</td>';
	}
	echo "</tr>\n";
}

echo "</table>\n";
?>
</td></tr>
</TABLE>

<p>The processed queue is cleared out daily.

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
	<?
	freshports_SideBar();
	?>
  </td>

</TR>
</TABLE>

<?
freshports_ShowFooter();
?>

</body>
</html>

