<?php
	#
	# $Id: status.php,v 1.2 2006-12-17 12:06:22 dan Exp $
	#
	# Copyright (c) 2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

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
<h2>System status</h2>
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
		$command = "find $base/$site/msgs/FreeBSD/$queue/";
		if ($pattern) {
			$command .= " -name \"$pattern\"";
		}
		$command .= ' | wc -l';

		# the above command will return 1 for an empty directory.
		# so adjust appropriately

		$count = exec($command) - 1;
		echo '<td align="right">' . $count . '</td>';
	}
	echo "</tr>\n";
}

echo "</table>\n";
?>
</td></tr>
</TABLE>

<p><sup>*</sup>The processed queue is cleared out daily.

<h2>Last login count</h2>

<?php
$sql = "select * from LoginCounts(7)";
$result = pg_exec($db, $sql);
if ($result) {
	$numrows = pg_numrows($result);
	if ($numrows) {
		echo '<table border="1">' . "\n";
		echo "<tr><td><b>Days</b><td><b>Users</b></td></tr>\n";
	
		$i=0;
		$GlobalHideLastChange = "N";
		for ($i = 0; $i < $numrows; $i++) {
			$myrow = pg_fetch_array ($result, $i);
			echo '<tr><td align="right">' . $myrow[0] . '</td><td align="right">' . $myrow[1] . '</td></tr>' . "\n";
		}

		echo "</table>\n";
	}
}
?>

<sup>*</sup>The users column indicates the number of logged-in users who last accessed the system on that day.

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
	<?
	echo freshports_SideBar();
	?>
  </td>

</TR>
</TABLE>

<?
echo freshports_ShowFooter();
?>

</body>
</html>

