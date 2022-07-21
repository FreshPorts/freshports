<?php
	#
	# $Id: watch-lists.php,v 1.4 2012-09-18 20:51:39 dan Exp $
	#
	# Copyright (c) 1998-2007 DVL Software Limited
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
				$HTML .= '<option value="' . htmlspecialchars(pg_escape_string($dbh, $WatchList->id)) . '"';
				if ($selected == '') {
					if ($element_id && $WatchList->watch_list_count > 0) {
						$HTML .= ' selected';
					}
				} else {
					if ($WatchList->id == $selected) {
						$HTML .= ' selected';
					}
				}
				$HTML .= '>' . htmlspecialchars(pg_escape_string($dbh, $WatchList->name));
				if ($show_active && $WatchList->in_service == 't') {
					$HTML .= '*';
				}
				if ($element_id && $WatchList->watch_list_count) {
					$HTML .= " +";
				}
				$HTML .= "</option>\n";
			}
		}
	
		$HTML .= '</select>';

		if (!$NumRows) {
			$HTML .= '<br><h2> You have no watch lists.  You must <a href="watch-list-maintenance.php">create one</a>.</h2>';
		}
	
		return $HTML;
	}

function freshports_WatchListSelectGoButton($name = 'watch_list_select') {
	return '	<input type="image" name="' . $name . '" src="/images/go.gif" alt="Go" title="Display the selected watch list">';
}

function freshports_WatchListDDLBForm($db, $UserID, $WatchListID, $Extra = '') {
	
	$HTML = '
<form class="watchlist-selector" action="' . $_SERVER["PHP_SELF"] . '" method="POST" NAME=f>
';

	$HTML .= freshports_WatchListDDLB($db, $UserID, $WatchListID);

$HTML .=  '
'  . freshports_WatchListSelectGoButton() . $Extra .  '</form>
';

	return $HTML;

}

function freshports_WatchListCountDefault($dbh, $UserID) {
	$sql = "select WatchListCountDefault(" . pg_escape_string($dbh, $UserID) . ") as count";

#	echo $sql;

	$result = pg_exec($dbh, $sql);
	if (!$result) {
		echo "error " . pg_last_error($dbh);
		exit;
	}

	$myrow = pg_fetch_array($result, 0);

#	echo $myrow["count"];

	return $myrow["count"];
}

function freshports_WatchListVerifyToken($dbh, $token) {
	$id = '';

	$sql = "SELECT id from watch_list where token = '" . pg_escape_string($dbh, $token) . "'";

#	echo $sql;

	$result = pg_exec($dbh, $sql);
	if ($result) {
		$numrows = pg_num_rows($result);
		switch ($numrows) {
			case 0:
				// nothing found, do nothing
				break;

			case 1:
				$row = pg_fetch_array($result, 0);
				$id = $row['id'];
				break;

			default:
				syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . ' more than one watch list with this token ' . $token);
				header('HTTP/1.1 500 OK******');
				exit;
		}
	}

	return $id;
}

