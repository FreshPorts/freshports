<?php
	#
	# $Id: list-of-ports.php,v 1.1.2.10 2006-06-12 17:55:10 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

function freshports_ListOfPorts($result, $db, $ShowDateAdded, $ShowCategoryHeaders, $User) {

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/port-display.php');

	$port_display = new port_display($db, $User);
	$port_display->SetDetailsReports();
	$port = new Port($db);
	$port->LocalResult = $result;

	$LastCategory         = '';
	$GlobalHideLastChange = 'N';
	$numrows = pg_numrows($result);

	$HTML = "<TR><TD>$numrows ports found</TD></TR>\n";

	$ShowDescriptionLink = 0;
	$DaysMarkedAsNew= $DaysMarkedAsNew= $GlobalHideLastChange = $ShowMaintainedBy= $ShowPortCreationDate= $ShowPackageLink= $ShowShortDescription =1;
	$ShowChangesLink = $ShowDownloadPortLink = $ShowHomepageLink = "Y";
	$ShowPortCreationDate = 0;
#	$HideCategory = 1;
	$ShowCategories		= 1;
	GLOBAL	$ShowDepends;
	$ShowDepends		= 1;
	$HideDescription = 1;
	$ShowEverything  = 0;
	$ShowShortDescription = "Y";
	$ShowMaintainedBy     = "Y";
	#$GlobalHideLastChange = "Y";
	$ShowDescriptionLink  = "N";
	$ShowLastChange       = "Y";

	if ($ShowDateAdded == "Y") {
		$ShowLastChange = "N";
	}

	$HTML .= "<TR><TD>\n";

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
#		$HTML .= 'START ..';
		$HTML .= $port_display->Display();

#		$HTML .= freshports_PortDetails($port, $db, $DaysMarkedAsNew, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription, 1, '', 1, $ShowDateAdded);
#		$HTML .= '... END';
		$HTML .= '<BR>';
	}

	if ($numrows && $ShowCategoryHeaders) {
		$HTML .= "\n</DD>\n</DL>\n";
	}

	$HTML .= "</TD></TR>\n";

	$HTML .= "<TR><TD>$numrows ports found</TD></TR>\n";

	return $HTML;

}

?>
