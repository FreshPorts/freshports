<?php
	#
	# $Id: list-of-ports.php,v 1.2 2006-12-17 11:55:53 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

function freshports_ListOfPorts($result, $db, $ShowDateAdded, $ShowCategoryHeaders, $User, $PortCount = -1) {

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/port-display.php');

	$port_display = new port_display($db, $User);
	$port_display->SetDetailsReports();
	$port = new Port($db);
	$port->LocalResult = $result;

	$LastCategory         = '';
	$GlobalHideLastChange = 'N';
	$numrows = pg_numrows($result);
	if ($PortCount == -1) {
		$PortCount = $numrows;
	}

	$PortCountText = "<TR><TD>$PortCount ports found.";
	if ($numrows != $PortCount) {
		$PortCountText .= " (showing only $numrows ports on this page)";
	}
	$PortCountText .= "</TD></TR>\n";

	$HTML  = $PortCountText;
	$HTML .= "<TR><TD>\n";
	
	$HTML .= "<br><center>\n" . Ad_728x90() . "\n</center>\n";
	

	if ($numrows > 0 && $ShowCategoryHeaders) {
		$HTML .= '<DL>';
	}

	for ($i = 0; $i < $numrows; $i++) {
		$port->FetchNth($i);

		if ($ShowCategoryHeaders) {
			$Category = $port->category;

			if ($LastCategory != $Category) {
				if ($i > 0) {
					$HTML .= "\n</DD>\n";
				}

				$LastCategory = $Category;
				if ($ShowCategoryHeaders) {
						$HTML .= '<DT>';
				}

				$HTML .= '<BIG><BIG><B><a href="/' . $Category . '/">' . $Category . '</a></B></BIG></BIG>';
				if ($ShowCategoryHeaders) {
					$HTML .= "</DT>\n<DD>";
				}
			}
		}
		$port_display->port = $port;

		$Port_HTML = $port_display->Display();
		
		$HTML .= $port_display->ReplaceWatchListToken($port->{'onwatchlist'}, $Port_HTML, $port->{'element_id'});

		$HTML .= '<BR>';
	}

	if ($numrows && $ShowCategoryHeaders) {
		$HTML .= "\n</DD>\n</DL>\n";
	}

	$HTML .= "</TD></TR>\n";

	$HTML .= $PortCountText;

	return $HTML;

}

