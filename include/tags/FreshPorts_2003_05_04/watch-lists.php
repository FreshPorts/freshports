<?php
	#
	# $Id: watch-lists.php,v 1.1.2.11 2003-04-28 00:05:56 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/watch_lists.php');

function freshports_WatchListDDLB($dbh, $UserID, $selected = '', $size = 0, $multiple = 0, $show_active = 1, $element_id = 0) {
	# return the HTML which forms a dropdown list box.
	# optionally, select the item identified by $selected.

	$Debug = 0;

	$HTML = '<select name="wlid';
	if ($multiple) {
		$HTML .= '[]';
	}
	
	$HTML .= '" title="Select a watch list"';

	if ($size) {
		$HTML .= ' size="' . $size . '"';
	}
	if ($multiple) {
		$HTML .= ' multiple';
	}
	$HTML .= ">\n";

	$WatchLists = new WatchLists($dbh);
	$NumRows = $WatchLists->Fetch($UserID, $element_id);

	if ($Debug) {
		echo "$NumRows rows found!<br>";
		echo "selected = '$selected'<br>";
	}

	if ($NumRows) {
		for ($i = 0; $i < $NumRows; $i++) {
			$WatchList = $WatchLists->FetchNth($i);
			$HTML .= '<option value="' . htmlspecialchars(AddSlashes($WatchList->id)) . '"';
			if ($selected == '') {
				if ($element_id && $WatchList->watch_list_count > 0) {
					$HTML .= ' selected';
				}
			} else {
				if ($WatchList->id == $selected) {
					$HTML .= ' selected';
				}
			}
			$HTML .= '>' . htmlspecialchars(AddSlashes($WatchList->name));
			if ($show_active && $WatchList->in_service == 't') {
				$HTML .= '*';
			}
			if ($element_id && $WatchList->watch_list_count) {
				$HTML .= " +";
			}
			$HRML .= "</option>\n";
		}
	}

	$HTML .= '</select>';

	return $HTML;
}

function freshports_WatchListSelectGoButton($name = 'watch_list_select') {
	return '	<input type="image" name="' . $name . '" value="GO" src="/images/go.gif" alt="Go" align="middle" title="Display the selected watch list">';
}

function freshports_WatchListDDLBForm($db, $UserID, $WatchListID, $Extra = '') {
	
	$HTML = '
<form action="' . $_SERVER["PHP_SELF"] . '" method="POST" NAME=f>
<table border="0">
<tr>
<td valign="top" nowrap align="right">
<small>
';

	$HTML .= freshports_WatchListDDLB($db, $UserID, $WatchListID);

$HTML .=  '
</small>
</td>
<td valign="top" nowrap align="left">
'  . freshports_WatchListSelectGoButton() . $Extra .
'</td></tr></table></form>
';

	return $HTML;

}


?>