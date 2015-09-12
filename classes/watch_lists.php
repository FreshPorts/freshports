<?php
	#
	# $Id: watch_lists.php,v 1.2 2006-12-17 11:37:22 dan Exp $
	#
	# Copyright (c) 1998-2005 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/watch_list.php');

// base class for fetching watch lists
class WatchLists {

	var $dbh;
	var $LocalResult;

	function WatchLists($dbh) {
		$this->dbh	= $dbh;
	}

	function DeleteAllLists($UserID) {
		#
		# Delete a watch list
		#
		unset($return);

		$query  = '
DELETE FROM watch_list 
 WHERE user_id = ' . $UserID;

		if ($Debug) echo $query;
		$result = pg_query($this->dbh, $query);

		# that worked and we updated exactly one row
		if ($result) {
			$return = 1;
		}

		return $return;
	}

	function Fetch($UserID, $element_id = 0) {
		$Debug = 0;

		if ($element_id) {
			$sql = "
			SELECT id,
			       user_id,
			       name,
			       in_service,
			       count(element_id) as watch_list_count,
			       token,
                   NULL as watch_list_count
			  FROM watch_list LEFT OUTER JOIN watch_list_element
			    ON watch_list_element.watch_list_id = watch_list.id
			   AND watch_list_element.element_id    = " . pg_escape_string($element_id) . "
			 WHERE user_id = " . pg_escape_string($UserID) . "
		 GROUP BY id, user_id, name, in_service, element_id, token
		 ORDER BY name";
		} else {
			$sql = "
			SELECT id,
			       user_id,
			       name,
			       in_service,
			       token,
                   NULL as watch_list_count
			  FROM watch_list
			 WHERE user_id = " . pg_escape_string($UserID) . "
		 ORDER BY name";
		}

		if ($Debug) {
			echo 'WatchLists::Fetch sql = <pre>' . $sql . '</pre>';
		}

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
#			echo "That would give us $numrows rows";
		} else {
			$numrows = -1;
			echo 'pg_exec failed: ' . $sql;
		}

		return $numrows;
	}

	function FetchNth($N) {
		#
		# call Fetch first.
		# then call this function N times, where N is the number
		# returned by Fetch
		#

#		echo "fetching row $N<br>";

		$WatchList = new WatchList($this->dbh);

		$myrow = pg_fetch_array($this->LocalResult, $N);
		$WatchList->PopulateValues($myrow);

		return $WatchList;
	}

	function In_Service_Set($UserID, $WatchListIDs) {
		#
		# for each ID in $WatchListIDs, set in_service = true
		# returns the number of rows set to true
		#

		$max = count($WatchListIDs);
		$sql = 'UPDATE watch_list
		           SET in_service = FALSE
		         WHERE user_id = ' . pg_escape_string($UserID);

		if ($Debug) echo "<pre>$sql</pre>";
		$result = pg_exec($this->dbh, $sql);
		if ($result && $max) {
			$sql = 'UPDATE watch_list
		           SET in_service = TRUE
		         WHERE user_id = ' . pg_escape_string($UserID) . '
		           AND id IN (';

			for ($i = 0; $i < $max; $i++) {
				$sql .= $WatchListIDs[$i] . ', ';
			}

			# now get rid of the trailing ,
			$sql = substr($sql, 0, strlen($sql) - 2);

			$sql .= ')';
			if ($Debug) echo "<pre>$sql</pre>";
			$result = pg_exec($this->dbh, $sql);
		}
		if ($result) {
			$numrows = pg_affected_rows($result);
		} else {
			$numrows = -1;
			die(pg_last_error() . '<pre>' . $sql . '</pre>');
		}

		return $numrows;
	}

	function GetDefaultWatchListID($UserID) {
		#
		# If the user has just one watch list, return that.
		# If the user has more than one watch list, take
		# the first one which is in_service.
		# if none are in service, return the first one.
		# otherwise, return an empty string.
		#

		$Debug = 0;

		$sql = "
   SELECT id,
          in_service
     FROM watch_list
    WHERE user_id = " . pg_escape_string($UserID) . "
 ORDER BY name";

		if ($Debug) echo "<pre>$sql</pre>";

		$WatchListID = '';
		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 1) {
				$myrow = pg_fetch_array($result, 0);
				$WatchListID = $myrow["id"];
			} else {
				if ($numrows > 0) {
					for ($i = 0; $i < $numrows; $i++) {
						$myrow = pg_fetch_array($result, $i);
						if ($myrow["in_service"] == 't') {
							$WatchListID = $myrow["id"];
							break;
						}
					}
					if ($WatchListID == '') {
						$myrow = pg_fetch_array($result, 0);
						$WatchListID = $myrow["id"];
					}
				}
			}
		} else {
			die(pg_last_error() . '<pre>' . $sql . '</pre>');
		}

		return $WatchListID;
	}
	
	function IsOnWatchList($UserID, $ElementID) {
		# return the number of watch lists owned by the user that
		# contain the indicated element

		$sql = "
   SELECT count(WLE.watch_list_id) AS listcount
     FROM watch_list WL, watch_list_element WLE
    WHERE WL.user_id     = " . pg_escape_string($UserID) . "
      AND WL.id          = WLE.watch_list_id
      AND WLE.element_id = " . pg_escape_string($ElementID);

      	$ListCount = 0;
		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 1) {
				$myrow = pg_fetch_array($result, 0);
				$ListCount = $myrow['listcount'];
			}
		} else {
			die(pg_result_error($result) . "<pre>$sql</pre>");
		}
				
		return $ListCount;
	}
}

