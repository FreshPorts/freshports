<?php
	#
	# $Id: vuxml.php,v 1.10 2012-07-18 18:33:27 dan Exp $
	#
	# Copyright (c) 2004 DVL Software Limited
	#

	if (IsSet($_REQUEST['all']) && strlen($_REQUEST['all'])) {
		echo "Just what do you think you're doing, Dave?";
		exit;
	}
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	define('VUXMLURL',     'https://www.vuxml.org/freebsd/');

	if (IsSet($_REQUEST['vid'])) {
		$vid = pg_escape_string($db, $_REQUEST['vid']);

		$vidArray = explode('|', $vid);
	}
	echo HTML_DOCTYPE;
?>
<html lang="en">
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
	if (file_exists(VUXML_LATEST) && is_readable(VUXML_LATEST)) {
		echo '<p>The VUXML data was last processed by FreshPorts on ' . date('Y-m-d H:i:s T', filemtime(VUXML_LATEST)) . '</p>';
	} else {
		echo '<p><b> * * * We have no information on when we last processed VUXML. This should never happen. * * * </b></p>';
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

<table class="cellpadding5" class="bordered">
<tr><th class="hleft"><b>VuXML ID</b></th><th class="vleft"><b>Description</b></th></tr>
<?php
	if (!IsSet($vidArray)) {
		$vuln = $_REQUEST['vuln'];

		$vidArray = explode('|', $vuln);
	}

	$VuXML = new VuXML($db);

	foreach($vidArray as $key => $value) {
		$VuXML->FetchByVID($value);

		$URL = VUXMLURL . $value . '.html';
		echo '<tr><td class="vtop" nowrap><a href="' . $URL . '">' . $value . '</a></td><td>';
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

			$HTML .= '<a href="/vuxml.php?vid=' . urlencode($VID) . '">' . $Name . '</a></td><td class="hcentered">';
			if ($Count > 1) {
				$HTML .= ' (' . $Count . ')';
			} else {
				$HTML .= '&nbsp;';
			}

			$HTML .= '</td><td class="hcentered">';
			$HTML .= '<a href="/?package=' . $Name . '">port</a>';
			$HTML .= '</td></tr>' . "\n";

			return $HTML;
		}

	

		$params = array();
		$sql = "
SELECT V.vid,
       VN.name
  FROM vuxml_affected VA, vuxml_names VN, vuxml V
 WHERE VN.vuxml_affected_id = VA.id
   AND VA.vuxml_id          = V.id";
   
   	if (IsSet($_REQUEST['package'])) {
		$sql .= "\n   AND lower(VN.name) = $1";
		$params = array(strtolower($_REQUEST['package']));
   	}

   	$sql .= "\nORDER BY lower(VN.name), V.vid\n";
   	
		$result = pg_query_params($db, $sql, $params);
		if ($result) {
			$numrows = pg_num_rows($result);

			$LastName    = '';
			$LastVID     = '';
			$Count       = 0;
			$NumPackages = 0;
			$VIDs        = array();
			echo '<table class="bordered">' . "\n";
			echo '<th colspan="3">VuXML entries as processed by FreshPorts</th>';
			echo '<tr><td><b>';
			echo 'package';
			echo '</b></td><td class="hcentered"><b>';
			echo 'vuln count<br>[blank means (1)]';
			echo '</b></td><td class="hcentered"><b>Port(s)</b></td></tr>' . "\n";
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
			$HTML = '<tr><td class="vtop nowrap">';
			
			$HTML .= $Date;
			if ($IsNew == 'f') {
				$HTML .= '<sup>*</sup>';
			}
			$HTML .= '</td><td class="vtop">';
			
			$Narrative = $Description;
			$HTML .= '<b>VuXML ID</b> <span class="code">' . $VID . '</span><br>' . $Narrative . ' <a href="' . VUXMLURL . $VID . '.html">more...</a>';
			$HTML .= '</td><td class="hleft vtop">';

			foreach ($PortArray as $package) {
				$HTML .= '<a href="/?package=' . $package . '">' . $package . '</a> ';
				$HTML .= '<br>';
			}
			

			$HTML .= '<br><a href="vuxml.php?vid=' . $VID . '">more detail</a></td></tr>' . "\n";

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

		$result = pg_query_params($db, "set client_encoding = 'ISO-8859-15'", array()) or die('query failed ' . pg_last_error($db));
		$result = pg_query_params($db, $sql, array());
		if ($result) {
			$numrows = pg_num_rows($result);
			if ($numrows == 0) {
				echo '<p>no vulnerabilities found.  it looks as if the data is missing.</p>';
			} else {

				$PortArray   = array();
				$LastVID     = '';
				$NumPackages = 0;
				$VIDs        = 0;
				echo '<table class="bordered" class="cellpadding5">' . "\n";
				echo '<th colspan="3">VuXML entries as processed by FreshPorts</th>';
				echo '<tr><td><b>Date</b></td><td><b>';
				echo 'Decscription';
				echo '</b></td><td class="hcentered"><b>Port(s)</b></td></tr>' . "\n";
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