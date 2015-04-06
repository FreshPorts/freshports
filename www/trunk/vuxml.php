<?php
	#
	# $Id: vuxml.php,v 1.10 2012-07-18 18:33:27 dan Exp $
	#
	# Copyright (c) 2004 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	define('VUXMLURL',     'http://www.vuxml.org/freebsd/');
	define('VUXMLREVISION', $_SERVER['DOCUMENT_ROOT'] . '/../dynamic/vuxml_revision');


	if (IsSet($_REQUEST['vid'])) {
		$vid = pg_escape_string($_REQUEST['vid']);

		$vidArray = explode('|', $vid);
	}
?>
<html>
<head>
<title>FreshPorts - VuXML</title>
<meta name="robots" content="nofollow">
</head>
<body>

<h1>FreshPorts - VuXML</h1>

<p>
This page displays <a href="<?php echo VUXMLURL; ?>">vulnerability information</a> about FreeBSD Ports.
</p>

<?php
	if (file_exists(VUXMLREVISION) && is_readable(VUXMLREVISION)) {
		echo '<p>The last <a href="/security/vuxml/vuln.xml">vuln.xml</a> file processed by FreshPorts is:</p>';
		echo "<blockquote><pre>\n";
		require_once(VUXMLREVISION);
		echo "</pre></blockquote>\n";
	}

	if (!IsSet($_REQUEST['list'])) {
		echo '<p><a href="' . $_SERVER["PHP_SELF"] . '?list">List all Vulnerabilities, by package</a></p>';
	}

	if (!IsSet($_REQUEST['all'])) {
		echo '<p><a href="' . $_SERVER["PHP_SELF"] . '?all">List all Vulnerabilities, by date</a></p>';
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
	if (IsSet($_REQUEST['list']) || IsSet($_REQUEST['package']) ) {

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
   AND VA.vuxml_id          = V.id";
   
   	if (IsSet($_REQUEST['package'])) {
   		$sql .= "\n   AND lower(VN.name) = '" . pg_escape_string($db, strtolower($_REQUEST['package'])) . "'";
   	}

   	$sql .= "\nORDER BY lower(VN.name), V.vid\n";
   	
		$result = pg_exec($db, $sql);
		if ($result) {
			$numrows = pg_numrows($result);

			$LastName    = '';
			$LastVID     = '';
			$Count       = 0;
			$NumPackages = 0;
			$VIDs        = array();
			echo '<table border="1">' . "\n";
			echo '<th colspan="3">VuXML entries as processed by FreshPorts</th>';
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
				if (strtolower($LastName) == strtolower($myrow['name'])) {
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


	if (IsSet($_REQUEST['all'])) {

		function vuxml_name_link($VID, $Date, $Description, $PortArray, $IsNew) {
			$HTML = '<tr><td nowrap valign="top">';
			
			$HTML .= $Date;
			if ($IsNew == 'f') {
				$HTML .= '<sup>*</sup>';
			}
			$HTML .= '</td><td valign="top">';
			
			$Narrative = trim(strip_tags($Description));
			$Narrative = utf8_decode($Description);
			$HTML .= $Narrative . ' <a href="' . VUXMLURL . $VID . '.html">more...</a>';
			$HTML .= '</td><td align="left" valign="top">';

			foreach ($PortArray as $package) {
				$HTML .= '<a href="/?package=' . $package . '">' . $package . '</a> ';
				$HTML .= '<br>';
			}
			

			$HTML .= '</td></tr>' . "\n";

			return $HTML;
		}

	


		$sql = "
SELECT V.vid,
       VN.name,
       V.description,
       coalesce(V.date_modified, V.date_entry, V.date_discovery)::date as date,
       V.date_modified IS NULL AS new
  FROM vuxml V left outer join vuxml_affected VA on VA.vuxml_id          = V.id
       left outer join vuxml_names VN on VN.vuxml_affected_id = VA.id
ORDER BY coalesce(V.date_modified, V.date_entry, V.date_discovery)::date desc, V.vid, lower(VN.name)
";

		$result = pg_exec($db, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 0) {
				echo '<p>no vulnerabilities found.  it looks as if the data is missing.</p>';
			} else {

				$PortArray   = array();
				$LastVID     = '';
				$NumPackages = 0;
				$VIDs        = 0;
				echo '<table border="1" cellpadding="5" cellspacing="0">' . "\n";
				echo '<th colspan="3">VuXML entries as processed by FreshPorts</th>';
				echo '<tr><td><b>Date</b></td><td><b>';
				echo 'Decscription';
				echo '</b></td><td align="center"><b>Port(s)</b></td></tr>' . "\n";
				for ($i = 0; $i < $numrows; $i++) {
					$myrow = pg_fetch_array($result, $i);

					$NumPackages += 1;
					if ($LastVID == '') {
						$LastVID = $myrow['vid'];
					}
					
					if ($LastVID != $myrow['vid']) {
						$VIDs++;
						echo vuxml_name_link($LastVID, $Date, $Description, $PortArray, $IsNew);
						$PortArray = array();
						$LastVID = $myrow['vid'];
					}

					$PortArray[$myrow['name']] = $myrow['name'];
					$Description = $myrow['description'];
					$IsNew       = $myrow['new'];
					$Date        = $myrow['date'];
				}
				$VIDs++;
				echo vuxml_name_link($LastVID, $Date, $Description, $PortArray, $IsNew);
				echo "</table>\n";

				echo "<p>Number of vulns/ports : " . $numrows . "<br>\n";
				echo "<p>Number of vulns   : " . $VIDs . "<br>\n";
				echo "<p>A date marked with <sup>*</sup> indicates an updated vuxml entry.<br>\n";
			}
		}

	}

?>

</body>
</html>