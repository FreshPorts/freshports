<?php
	# $Id: watch_lists.php,v 1.1.2.4 2002-12-10 03:56:26 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited
	#


	require_once($_SERVER['DOCUMENT_ROOT'] . "/../classes/watch_list.php");

// base class for fetching watch lists
class WatchLists {

	var $dbh;
	var $LocalResult;

	function WatchLists($dbh) {
		$this->dbh	= $dbh;
	}

	function Fetch($UserID, $element_id = 0) {
		if ($element_id) {
			$sql = "
			SELECT id,
			       user_id,
			       name,
			       in_service,
			       count(element_id) as watch_list_count
			  FROM watch_list LEFT OUTER JOIN watch_list_element
			    ON watch_list_element.watch_list_id = watch_list.id
			   AND watch_list_element.element_id    = $element_id
			 WHERE user_id = $UserID
		 GROUP BY id, user_id, name, in_service, element_id
		 ORDER BY name";
		} else {
			$sql = "
			SELECT id,
			       user_id,
			       name,
			       in_service
			  FROM watch_list
			 WHERE user_id = $UserID
		 ORDER BY name";
		}

		if ($Debug) {
			echo '<pre>' . $sql . '</pre>';
		}

		if ($Debug)	echo "WatchLists::Fetch sql = '$sql'<BR>";

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

		$WatchList = new WatchList($db);

		$myrow = pg_fetch_array($this->LocalResult, $N);
		$WatchList->PopulateValues($myrow);

		return $WatchList;
	}

	function	In_Service_Set($UserID, $WatchListIDs) {
		#
		# for each ID in $WatchListIDs, set in_service = true
		# returns the number of rows set to true
		#

		$max = count($WatchListIDs);
		$sql = 'UPDATE watch_list
		           SET in_service = FALSE
		         WHERE user_id = ' . AddSlashes($UserID);

		if ($Debug) echo "<pre>$sql</pre>";
		$result = pg_exec($this->dbh, $sql);
		if ($result && $max) {
			$sql = 'UPDATE watch_list
		           SET in_service = TRUE
		         WHERE user_id = ' . AddSlashes($UserID) . '
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
			die(pg_lasterror . '<pre>' . $sql . '</pre>');
		}

		return $numrows;
	}

}

?>
