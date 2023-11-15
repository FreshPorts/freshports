<?php
	#
	# $Id: ports-ignore.php,v 1.2 2006-12-17 12:06:15 dan Exp $
	#
	# Copyright (c) 1998-2004 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
?>

<h1>FreshPorts Conflict Matches</h1>

<p>This page is manually generated and was last updated at:</P>

<p>Please look for the ports you maintain, and verify the conflicts are what you expect.  The middle column is the port you maintain. The right hand column are the conflicts.<p>

<p>Please report any issues to dvl@FreeBSD.org</p>

<?php

$sql = "
SELECT distinct P.maintainer, C.name || '/' || E.name AS port_name, f.*
  FROM ports_conflicts_matches PCM 
  JOIN ports_conflicts PC ON PCM.ports_conflicts_id = PC.id
  JOIN GetPortFromPackageName(PackageName(PCM.port_id)) AS f ON true
  JOIN ports           P  ON PC.port_id    = P.id
  JOIN element         E  ON P.element_id  = E.id
  JOIN categories      C  ON P.category_id = C.id
 WHERE coalesce(P.conflicts, P.conflicts_build, P.conflicts_install) IS NOT NULL order by 1, 2";

echo "<pre>$sql</pre>";

$result = pg_query_params($db, $sql, array());
if (!$result) {
  echo pg_last_error($db);
  die('that did not work');
}
$numrows = pg_num_rows($result);
echo '<table><tr><th>Maintainer</th><th>port</th><th>conflicts with</th></tr>';
for ($i = 0; $i < $numrows; $i++) {
	 $myrow = pg_fetch_array($result, $i);
	 echo '<tr><td>' . $myrow['maintainer'] . '</td><td><a href="/' . $myrow['port_name'] . '">' .  $myrow['port_name'] . 
              '</a></td><td>conflicts with: <a href="/' . $myrow['category'] . '/' . $myrow['port'] . '">' . $myrow['category'] . '/' . $myrow['port'] . '</a></td></tr>';
}

echo '</table>';
