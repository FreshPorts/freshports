<?
	# $Id: watch-lists.php,v 1.1.2.1 2002-12-04 21:26:45 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . "/../classes/watch_lists.php");

function freshports_WatchListDDLB($dbh, $UserID, $selected = '', $size = 0, $multiple = 0) {
	# return the HTML which forms a dropdown list box.
	# optionally, select the item identified by $selected.

	$HTML = '<select name=watch_list_id';
	if ($multiple) {
		$HTML .= '[]';
	}
	
	$HTML .= ' title="Select a watch list"';

	if ($size) {
		$HTML .= ' size="' . $size . '"';
	}
	if ($multiple) {
		$HTML .= ' multiple';
	}
	$HTML .= ">\n";

	$WatchLists = new WatchLists($dbh);
	$NumRows = $WatchLists->Fetch($UserID);
	
#	echo "$NumRows rows found!<br>";

	if ($NumRows) {
		for ($i = 0; $i < $NumRows; $i++) {
			$WatchList = $WatchLists->FetchNth($i);
			$HTML .= '<option value="' . htmlspecialchars(AddSlashes($WatchList->id)) . '"';
			if ($WatchList->id == $selected) $HTML .= ' selected';
			$HTML .= '>' . htmlspecialchars(AddSlashes($WatchList->name)) . "</option>\n";
		}
	}

	$HTML .= '</select>';

	return $HTML;
}


?>