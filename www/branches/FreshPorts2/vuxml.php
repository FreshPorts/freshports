<?php
	#
	# $Id: vuxml.php,v 1.1.2.6 2004-09-28 03:27:31 dan Exp $
	#
	# Copyright (c) 2004 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');
	define('VUXMLURL', 'http://www.vuxml.org/freebsd/');

	if (IsSet($_REQUEST['vid'])) {
		$vid = $_REQUEST['vid'];

		$vidArray = explode('|', $vid);

		if (count($vidArray) == 1) {
			header('Location: ' . VUXMLURL . $_REQUEST['vid'] . '.html');
		} 
	}
?>

<html>
<body>

<?php
	if (IsSet($_REQUEST['vid'])) {
?>

<p>
Hi.  Thanks for checking, but this part of the FreshPorts - VuXML system is still
being designed.
<p>

These are the vulnerabilities relating to the commit you have selected

<ul>
<?php

	while (list($key, $value) = each($vidArray)) {
		$URL = VUXMLURL . $value . '.html';
		echo '<li><a href="' . $URL . '">' . $URL . '</a>' . "\n";
	}

?>

</ul>
<?php
}
	if (IsSet($_REQUEST['list'])) {

	function vuxml_name_link($VID, $Name, $Count) {
		$HTML = '<tr><td>';

		$HTML .= '<a href="/vuxml.php?vid=' . $VID . '">' . $Name . '</a></td><td align="center">';
		if ($Count > 1) {
			$HTML .= ' (' . $Count . ')';
		} else {
			$HTML .= '&nbsp;';
		}

		$HTML .= '</td><td align="center">';
		$HTML .= '<a href="/?package=' . $Name . '">port</a>';
		$HTML .= '</td></tr>' . "\n";

		return $HTML;
	}


		$sql = "
SELECT V.vid,
       VN.name
  FROM vuxml_affected VA, vuxml_names VN, vuxml V
 WHERE VN.vuxml_affected_id = VA.id
   AND VA.vuxml_id          = V.id
ORDER BY lower(VN.name), V.vid
";

		$result = pg_exec($db, $sql);
		if ($result) {
			$numrows = pg_numrows($result);

			$LastName    = '';
			$LastVID     = '';
			$Count       = 0;
			$NumPackages = 0;
			$VIDs        = array();
			echo '<table border="1">' . "\n";
			echo '<tr><td><b>package</b></td><td align="center"><b>vuln count</b></td><td align="center"><b>Port(s)</b></td></tr>' . "\n";
			for ($i = 0; $i < $numrows; $i++) {
				$myrow = pg_fetch_array ($result, $i);

				if (IsSet($VIDs[$myrow['vid']])) {
					$VIDs[$myrow['vid']] += 1;
				} else {
					$VIDs[$myrow['vid']] = 1;
				}
				$NumPackages += 1;
				if ($LastName == '') {
					$LastName = $myrow['name'];
				}
				if ($LastName == $myrow['name']) {
					if ($LastVID != '') {
						$LastVID .= '|';
					}
					$LastVID .= $myrow['vid'];
					$Count += 1;
				} else {
					echo vuxml_name_link($LastVID, $LastName, $Count);
					$LastName = $myrow['name'];
					$LastVID  = $myrow['vid'];
					$Count    = 1;
				}
			}
			echo vuxml_name_link($LastVID, $LastName, $Count);
			echo "</table>\n";

			echo "<p>Number of packages: $NumPackages<br>\n";
			echo "<p>Number of vulns   : " . count($VIDs) . "<br>\n";
		}

	}

?>

</body>
</html>