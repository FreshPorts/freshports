<?php
	#
	# $Id: watch_list_also_watched.php,v 1.2 2006-12-17 11:37:22 dan Exp $
	#
	# Copyright (c) 1998-2005 DVL Software Limited
	#

	$Debug = 0;

// base class for a single item on a watch list
class WatchListAlsoWatched {

	var $dbh;

	var $element_id;
	var $URL;

	var $LocalResult;

	function WatchListAlsoWatched($dbh) {
		$this->dbh	= $dbh;
	}
	
	function WatchersAlsoWatch($ElementID) {
		#
		# What items do people who watch this also watch?
		#

		$Debug = 0;

		#
		# The subselect ensures the user can only add things to their
		# own watch list and avoid duplicate key problems.
		#
		$sql = "select * from WatchersAlsoWatched(" . pg_escape_string($ElementID) . ")";

		if ($Debug) echo "<pre>$sql</pre>";
		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
		} else {
			$numrows = 0;
			echo 'pg_exec failed: <pre>' . $sql . '</pre> : ' . pg_errormessage();
		}

		return $numrows;
	}

	function FetchNth($N) {
		#
		# call FetchByCategoryInitialise first.
		# then call this function N times, where N is the number
		# returned by FetchByCategoryInitialise
		#

		$myrow = pg_fetch_array($this->LocalResult, $N);
		$this->PopulateValues($myrow);
	}

	function PopulateValues($myrow) {
		#
		# call Fetch first.
		# then call this function N times, where N is the number
		# returned by Fetch.
		#

		$this->element_id	= $myrow['element_id'];
		$this->URL			= $myrow['url'];
	}
}

