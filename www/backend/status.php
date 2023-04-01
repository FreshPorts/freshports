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

<table class="fullwidth borderless" ALIGN="center">

<tr><td class="content">
<table class="fullwidth borderless">
<tr>
	<?php echo freshports_PageBannerText($Title); ?>
</tr>
<tr><td>
<h2>System status</h2>
</td></tr>
<tr><td>
<?php


$sql = "select * from GetPackageStatus()";
$result = pg_query_params($db, $sql, array());
if ($result) {
	$numrows = pg_num_rows($result);
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
$result = pg_query_params($db, $sql, array());
if ($result) {
	$numrows = pg_num_rows($result);
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
