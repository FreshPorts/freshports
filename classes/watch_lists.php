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

	var $Debug;

	function __construct($dbh) {
		$this->dbh	= $dbh;
		$this->Debug	= 0;
	}

	function DeleteAllLists($UserID) {
		#
		# Delete a watch list
		#
		unset($return);

		$query  = '
DELETE FROM watch_list 
 WHERE user_id = $1';

		if ($this->Debug) echo $query;
		$result = pg_query_params($this->dbh, $query, array($UserID));

		# that worked and we updated exactly one row
		if ($result) {
			$return = 1;
		}

		return $return;
	}

	function Fetch($UserID, $element_id = 0) {
		$this->Debug = 0;

		if ($element_id) {
			$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . '
			SELECT id,
			       user_id,
			       name,
			       in_service,
			       count(element_id) as watch_list_count,
			       token,
                   NULL as watch_list_count
			  FROM watch_list LEFT OUTER JOIN watch_list_element
			    ON watch_list_element.watch_list_id = watch_list.id
			   AND watch_list_element.element_id  = $1
			 WHERE user_id = $2
		 GROUP BY id, user_id, name, in_service, element_id, token
		 ORDER BY name';
		 $params = array($element_id, $UserID);
		} else {
			$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . '
			SELECT id,
			       user_id,
			       name,
			       in_service,
			       token,
                   NULL as watch_list_count
			  FROM watch_list
			 WHERE user_id = $1
		 ORDER BY name';
		 	$params = array($UserID);
		}

		if ($this->Debug) {
			echo 'WatchLists::Fetch sql = <pre>' . $sql . '</pre>';
		}

		$this->LocalResult = pg_query_params($this->dbh, $sql, $params);
		if ($this->LocalResult) {
			$numrows = pg_num_rows($this->LocalResult);
#			echo "That would give us $numrows rows";
		} else {
			$numrows = -1;
			echo 'pg_query_params failed: ' . $sql;
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


		# first, set them all false
		$max = count($WatchListIDs);
		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ .  '
		        UPDATE watch_list
		           SET in_service = FALSE
		         WHERE user_id = $1';

		# then set the supplied watch lists to true
		if ($this->Debug) echo "<pre>$sql</pre>";
		$result = pg_query_params($this->dbh, $sql, array($UserID));
		if ($result && $max) {
			$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . '
			UPDATE watch_list
		           SET in_service = TRUE
		         WHERE user_id = $1
		           AND id IN (';
		        
			$params = array($UserID);
			for ($i = 0; $i < $max; $i++) {
				$sql .= '$' . ($i + 2) . ', ';
				$params[] = $WatchListIDs[$i];
			}

			# now get rid of the trailing ,
			$sql = substr($sql, 0, strlen($sql) - 2);

			$sql .= ')';
			if ($this->Debug) echo "<pre>$sql</pre>";
			$result = pg_query_params($this->dbh, $sql, $params);
		}
		if ($result) {
			$numrows = pg_affected_rows($result);
		} else {
			$numrows = -1;
			die(pg_last_error($this->dbh) . '<pre>' . $sql . '</pre>');
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

		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . "
   SELECT id,
          in_service
     FROM watch_list
    WHERE user_id = $1
 ORDER BY name";

		if ($this->Debug) echo "<pre>$sql</pre>";

		$WatchListID = '';
		$result = pg_query_params($this->dbh, $sql, array($UserID));
		if ($result) {
			$numrows = pg_num_rows($result);
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
			die(pg_last_error($this->dbh) . '<pre>' . $sql . '</pre>');
		}

		return $WatchListID;
	}
	
	function IsOnWatchList($UserID, $ElementID) {
		# return the number of watch lists owned by the user that
		# contain the indicated element

		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . "
   SELECT count(WLE.watch_list_id) AS listcount
     FROM watch_list WL, watch_list_element WLE
    WHERE WL.user_id     = $1
      AND WL.id          = WLE.watch_list_id
      AND WLE.element_id = $2";

		$ListCount = 0;
		$result = pg_query_params($this->dbh, $sql, array($UserID, $ElementID));
		if ($result) {
			$numrows = pg_num_rows($result);
			if ($numrows == 1) {
				$myrow = pg_fetch_array($result, 0);
				$ListCount = $myrow['listcount'];
			}
		} else {
			die(pg_last_error($result) . "<pre>$sql</pre>");
		}
				
		return $ListCount;
	}
}
