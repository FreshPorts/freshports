<?
	# $Id: watch_lists.php,v 1.1.2.1 2002-12-04 21:27:22 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited
	#


	require($_SERVER['DOCUMENT_ROOT'] . "/../classes/watch_list.php");

// base class for fetching watch lists
class WatchLists {

	var $dbh;
	var $LocalResult;

	function WatchLists($dbh) {
		$this->dbh	= $dbh;
	}

	function Fetch($UserID) {
		$sql = "
		SELECT id,
		       user_id,
		       name
		  FROM watch_list
		 WHERE user_id = $UserID
	 ORDER BY name";

#		echo '<pre>' . $sql . '</pre>';

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

		$commit = new WatchList($db);

		$myrow = pg_fetch_array($this->LocalResult, $N);
		$commit->PopulateValues($myrow);

		return $commit;
	}
}
