<?php
	#
	# $Id: vuxml.php,v 1.1.2.13 2005-01-23 20:55:59 dan Exp $
	#
	# Copyright (c) 2004 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	define('VUXMLURL',     'http://www.vuxml.org/freebsd/');
	define('VUXMLREVISION', $_SERVER['DOCUMENT_ROOT'] . '/../dynamic/vuxml_revision');

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

<?php echo freshports_MainTable(); ?>

<h1>FreshPorts - VuXML</h1>

<p>
This page displays <a href="<?php echo VUXMLURL; ?>">vulnerability information</a> about FreeBSD Ports.
</p>

<?php
	if (file_exists(VUXMLREVISION) && is_readable(VUXMLREVISION)) {
		echo '<p>The last vuln.xml file processed by FreshPorts is:</p>';
		echo "<blockquote><pre>\n";
		require_once(VUXMLREVISION);
		echo "</pre></blockquote>\n";
	}

	if (!IsSet($_REQUEST['list'])) {
		echo '<p><a href="' . $_SERVER["PHP_SELF"] . '?list">List all Vulnerabilities</a></p>';
	}



	if (IsSet($_REQUEST['vuln']) || IsSet($_REQUEST['vid'])) {

		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/vuxml.php');

?>

<p>
These are the vulnerabilities relating to the commit you have selected:
</p>

<table cellpadding="5" border="1" cellspacing="0">
<tr><th align="left"><b>VuXML ID</b></th><th align="left"><b>Description</b></th></tr>
<?php
	if (!IsSet($vidArray)) {
		$vuln = $_REQUEST['vuln'];

        $vidArray = explode('|', $vuln);
	}

	$VuXML = new VuXML($db);

	while (list($key, $value) = each($vidArray)) {
		$VuXML->FetchByVID($value);

		$URL = VUXMLURL . $value . '.html';
		echo '<tr><td valign="top" nowrap><a href="' . $URL . '">' . $value . '</a></td><td>';
		$VuXML->display();
		
		echo "</td></tr>\n";
	}

?>

</table>
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
			echo '<th colspan="3">VuXML entries as process by FreshPorts</th>';
			echo '<tr><td><b>';
			echo 'package';
			echo '</b></td><td align="center"><b>';
			echo 'vuln count<br>[blank means (1)]';
			echo '</b></td><td align="center"><b>Port(s)</b></td></tr>' . "\n";
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