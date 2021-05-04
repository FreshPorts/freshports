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

<TABLE class="fullwidth borderless" ALIGN="center">

<TR><td class="content">
<TABLE class="fullwidth borderless">
<TR>
	<? echo freshports_PageBannerText($Title); ?>
</TR>
<TR><TD>
<h2>System status</h2>
</TD></TR>
<tr><td>
<?

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../configuration/status-config.php');

echo '<table class="bordered">' . "\n";
echo '<tr><td></td><td align="center" colspan="' . count($sites) . '">sites - yeah, we can\'t do this yet from the front end</td></tr>' . "\n";
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

$sql = "select * from GetPackageStatus()";
$result = pg_exec($db, $sql);
if ($result) {
	$numrows = pg_numrows($result);
	if ($numrows) {
		echo '<table class="bordered" cellpadding="5" cellspacing="3">' . "\n";
		echo "<caption>The package imports</caption><tr>
		<td><b>ABI</b>
		<td><b>package set</b></td>
		<td><b>repo build date</b></td>
		<td><b>processed date</b></td>
		</tr>\n";
	
		$i=0;
		$GlobalHideLastChange = "N";
		for ($i = 0; $i < $numrows; $i++) {
			$myrow = pg_fetch_array ($result, $i);
			echo '<tr>
			<td align="right">' . $myrow['abi_name'] . '</td>
			<td align="right">' . $myrow['package_set'] . '</td>
			<td align="right">' . $myrow['repo_date'] . '</td>
			<td align="right">' . $myrow['processed_date'] . '</td>
			</tr>' . "\n";
		}

		echo "</table>\n";


	}
}

?>
<ul>
<li><b>repo build date</b> - date repo was last build</li>
<li><b>processed date</b> - when this information was imported into FreshPorts</li>
</ul>
</table>

<p><sup>*</sup>The processed queue is cleared out daily.

<h2>Last login count</h2>

<?php
$sql = "select * from LoginCounts(10)";
$result = pg_exec($db, $sql);
if ($result) {
	$numrows = pg_numrows($result);
	if ($numrows) {
		echo '<table class="bordered">' . "\n";
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

  <td class="sidebar">
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
