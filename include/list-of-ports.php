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
	$numrows = pg_num_rows($result);
	if ($PortCount == -1) {
		$PortCount = $numrows;
	}

	$PortCountText = "<tr><td>$PortCount ports found.";
	if ($numrows != $PortCount) {
		$PortCountText .= " (showing only $numrows ports on this page)";
	}
	$PortCountText .= "</td></tr>\n";

	$HTML  = $PortCountText;
	$HTML .= "<tr><td>\n";

	if (IsSet($ShowAds)) {
		$HTML .= "<br><center>\n" . Ad_728x90() . "\n</center>\n";
	}

	if ($numrows > 0 && $ShowCategoryHeaders) {
		$HTML .= '<dl>';
	}

	for ($i = 0; $i < $numrows; $i++) {
		$port->FetchNth($i);

		if ($ShowCategoryHeaders) {
			$Category = $port->category;

			if ($LastCategory != $Category) {
				if ($i > 0) {
					$HTML .= "\n</dd>\n";
				}

				$LastCategory = $Category;
				if ($ShowCategoryHeaders) {
						$HTML .= '<dt>';
				}

				$HTML .= '<span class="element-details"><span><a href="/' . $Category . '/">' . $Category . '</a></span></span>';
				if ($ShowCategoryHeaders) {
					$HTML .= "</dt>\n<dd>";
				}
			}
		}
		$port_display->SetPort($port);
		$port_display->ShowPackageLink  = false;

		$Port_HTML = $port_display->Display();
		
		$HTML .= $port_display->ReplaceWatchListToken($port->{'onwatchlist'}, $Port_HTML, $port->{'element_id'});

		$HTML .= '<br>';
	}

	if ($numrows && $ShowCategoryHeaders) {
		$HTML .= "\n</dd>\n</dl>\n";
	}

	$HTML .= "</td></tr>\n";

	$HTML .= $PortCountText;

	return $HTML;

}

