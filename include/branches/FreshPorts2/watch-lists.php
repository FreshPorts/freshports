<?
	# $Id: watch-lists.php,v 1.1.2.2 2002-12-06 16:49:23 dan Exp $
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

function freshports_WatchListSelectGoButton($name = 'watch_list_select') {
	return '	<input type="image" border="0" name="' . $name . '" value="GO" src="/images/go.gif" alt="Go" width="30" height="20" align="middle" title="Display the selected watch list">';
}

function freshports_WatchListDDLBForm($db, $UserID, $WatchListID) {
	
	$HTML = '
<form action="' . $_SERVER["PHP_SELF"] . '" method="POST" NAME=f>

<small>
';

	$HTML .= freshports_WatchListDDLB($db, $UserID, $WatchListID);

$HTML .=  '
</small>
</td><td valign="top" nowrap align="left">
'  . freshports_WatchListSelectGoButton() . '
</td>


</form>
';

	return $HTML;

}


?>